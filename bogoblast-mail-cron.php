<?php

ignore_user_abort(true);

/*
 * Bogoblast Cron file to send bogoblast_mail in the mail queue (status of draft)
 *
 * We query WordPress for posts of type bogoblast_mail with a status of draft
 * and a _scheduled_send_date in the past (or right now), which means they're not
 * sent and they should have been.
 *
 * We orderby random so that if the cron file gets executed twice in rapid
 * succession, we don't send the exact same message at the exact same time.
 * This makes it easier to use transient values and other methods keep track of
 * what's sending right this moment and avoiding duplicates.
 *
 * In the async mailer script, we also sleep() a random, even number of seconds
 * between 2 and 20. This also helps ensure that we never send the exact same
 * thing at the exact same time, thereby avoiding duplicate message deliveries.
 *
 * The async mailer script also re-checks the message's draft status and post type.
 */

class BogoblastMailCron {
    
    private $transient_key;
    private $start_time;
    
    public function __construct() {
        $this->transient_key = 'doing_bogoblast_mail_cron';
        $this->start_time = time();
        
        if ( !defined('ABSPATH') ) {
            /** Set up WordPress environment */
            require_once('../../../wp-load.php');
        }
        
        $pause_sending = intval(get_option('bogoblast__pause_sending', 0));
        if($pause_sending) {
            echo 'Sending Paused.';
            exit();
        }

        if(isset($_REQUEST['reset'])) {
            $this->give_up_the_ghost('Resetting.', true, false);
        }
        
        if(get_transient($this->transient_key) === false) {
            set_transient($this->transient_key, true, 30);
        } else {
            $this->give_up_the_ghost('Transient key exists.');
        }
        
        $sendable_mail_query = array(
            'numberposts' => 50,
            'orderby' => 'rand',
            'post_status' => 'draft',
            'meta_key' => '_scheduled_send_date',
            'meta_query' => array(
                array(
                    'key'=>'_scheduled_send_date',
                    'compare' => '<=',
                    'value'=> time(),
                    'type' => 'NUMERIC'
                )
            ),
            'post_type' => 'bogoblast_mail');
        $sendable_mail = get_posts($sendable_mail_query);
        
        $timer = time();
        $sent_this_second = 0;
        $send_count = 0;
        
        //sleep a random, even-numbered duration between 2 and 8.
        //These young whipper-snappers move too fast.
        //sleep( (mt_rand(1, 4)) * 2 );
        
        foreach($sendable_mail as $post) {
            
            //don't let the script run too long.
            if((time() - $this->start_time) > 28) $this->give_up_the_ghost('Killing and Restarting. Sent '.$send_count, true, true);
            //if((time() - $this->start_time) > 28) $this->give_up_the_ghost('Killing. Sent '.$send_count);
            
            //make sure we aren't sending more than 4 per second
            if($timer == time()) {
                if($sent_this_second >= 4) {
                    @time_sleep_until($timer + 1);
                } else {
                    $sent_this_second++;
                }
            } else {
                $timer = time();
                $sent_this_second = 1;
            }
            
            var_dump($post);
            
            $url = BOGOBLAST_URL.'bogoblast-mail-send-item-async.php';
            var_dump($url = BogoblastUtil::set_url_query_value($url, 'post_id', $post->ID));
            BogoblastUtil::async_request($url);
            
            $send_count++;
        }
        
        $this->give_up_the_ghost('Finished successfully. Sent '.$send_count);
    }
    
    public function give_up_the_ghost($message = '', $delete_transient = false, $restart = false) {
        
        echo $message;
        
        if($delete_transient) {
            delete_transient($this->transient_key);            
        }
        
        if($restart) {
            BogoblastUtil::async_request(BogoblastUtil::current_url());
            //BogoblastMailCron::curl_request_async(BogoblastUtil::current_url(), array());
        }
        exit();
    }
}

$bogoblast_mail_cron = new BogoblastMailCron();