<?php
    
    //send an e-mail, async-ish
    
    ignore_user_abort(true);
    
    if ( !defined('ABSPATH') ) {
        /* Set up WordPress environment */
        require_once('../../../wp-load.php');
    }
    
    $pause_sending = intval(get_option('bogoblast__pause_sending', 0));
    if($pause_sending) {
        echo 'Sending Paused.';
        exit();
    }

    $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
    
    if($post_id > 0) {
        
        //sleep a random, even-numbered duration between 2 and 20. Should help alleviate multiple sends per e-mail
        var_dump($sleepy_time = (mt_rand(1, 10)) * 2);
        sleep($sleepy_time);
        
        if(get_transient("sending_bogoblast_mail_{$post_id}") === false) {
            echo "Send $post_id \n";
            set_transient("sending_bogoblast_mail_{$post_id}", true, 60);
        } else {
            echo "Skip $post_id \n";
            exit();
        }
        
        if(get_post_type($post_id) == 'bogoblast_mail' && get_post_status($post_id) == 'draft') {
            var_dump($bogoblast_email_message = new BogoblastEMailMessage($post_id));
            var_dump($bogoblast_email_message->send());
            var_dump($bogoblast_email_message->save());
            var_dump($bogoblast_email_message);
        } else {
            echo "Post $post_id is not a bogoblast_message or it has already been sent.";
        }
    }