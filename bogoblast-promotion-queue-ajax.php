<?php
    $post_id = $_POST['post_id'];
    
    $send_to = ( isset($_POST['send_to']) && strlen($_POST['send_to']) > 0 ) ? $_POST['send_to'] : null;
    
    $send_month = isset($_POST['send_month']) ? intval($_POST['send_month']) : 0;
    $send_day = isset($_POST['send_day']) ? intval($_POST['send_day']) : 0;
    $send_year = isset($_POST['send_year']) ? intval($_POST['send_year']) : 0;
    
    $limit = intval($_POST['limit']);
    $offset = intval($_POST['offset']);
    
    $fatal_error = false;
    $error_messages = array();
    
    $promotion_queue_id = null;

    // if(isset($_POST['promotion_queue_id']) && strlen($_POST['promotion_queue_id']) > 0) {
    //     $promotion_queue_id = $_POST['promotion_queue_id'];
        
    //     $promotion_vars = get_transient($promotion_queue_id);
    //     if($promotion_vars === false) {
    //         // $fatal_error = true;
    //         $promotion_queue_id = null; //reset so we re-validate
    //         $error_messages[] = "Transient key '{$promotion_queue_id}' not found!";
    //     }
    // }

    if($promotion_queue_id === null) {
        $promotion_vars = Bogoblast::get_and_validate_promotion($post_id);
        $fatal_error = $promotion_vars['fatal_error'];
        $error_messages = $promotion_vars['error_messages'];
        //$error_messages[] = serialize($_POST);
        
        if(!$fatal_error) {
            $promotion_queue_id = $post_id.'_'.BogoblastUtil::random_string(24, 'alphanumeric');
            set_transient($promotion_queue_id, $promotion_vars, 60 * 60 * 12); //save for 12 hours
        }
    }
    
    //scheduling options
    if(!$fatal_error) {
        if($send_month > 0 && $send_day > 0 && $send_year > 0) {
            if(checkdate($send_month, $send_day, $send_year)) {
                $send_date = mktime(0, 0, 0, $send_month, $send_day, $send_year);
                Bogoblast::log_event("bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) will be sent on ".date_i18n(get_option('date_format'), $send_date).".");
            } else {
                Bogoblast::log_event("bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) has an invalid scheduled send date.");
                $error_messages[] = "bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) has an invalid scheduled send date.";
                $fatal_error = true;
            }
        } else {
            $send_date = 0;
        }
    }
    
    //get subscribers, unless we don't need to
    if(!$fatal_error) {
        if($send_to === null) {
            $subscriber_query = array(
                'numberposts' => $limit,
                'offset' => $offset,
                'orderby' => 'ID',
                'order' => 'ASC',
                'meta_key' => '_bogoblast_business_id',
                'meta_query' => array(
                    array(
                        'key'=>'_bogoblast_business_id',
                        'value'=> $promotion_vars['bogoblast_business_id'],
                        'compare' => '='
                    )
                ),
                'post_type' => 'bogoblast_subscriber');
            $bogoblast_subscribers = get_posts($subscriber_query);
            if(!is_array($bogoblast_subscribers) || sizeof($bogoblast_subscribers) <= 0) {
                //error
                Bogoblast::log_event("bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) has no bogoblast_subscribers.");
                $error_messages[] = "bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) has no bogoblast_subscribers.";
                $fatal_error = true;
            }
            
            if(!$fatal_error) {
                $subscriber_count = $promotion_vars['subscriber_count'];
            } else {
                $subscriber_count = 0;
            }
            
        } else {
            $recipients = explode(',', $send_to);
            
            $subscriber_count = sizeof($recipients);
        }
    }
    
    if(!$fatal_error) {
        
        $message_headers = array();
        $message_headers[] = "From: ".$promotion_vars['message_from'];
        //$message_headers[] = "Reply-To: {$this->reply_to}";
        $message_headers[] = "MIME-Version: 1.0";
        $message_headers[] = "Content-Type: text/html";
        
        $message_header_string = implode("\r\n", $message_headers)."\r\n";
        
        $message_content = $promotion_vars['message_content'];
        
        //Bogoblast::queue_flash_message("<a href=\"{$admin_url}post.php?post={$post_id}&action=edit\">".$promotion_vars['post_title']."</a> is queueing now!");
        Bogoblast::log_event("bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) is queueing now!");
        
        if(is_array($bogoblast_subscribers) && sizeof($bogoblast_subscribers) > 0) {
            foreach($bogoblast_subscribers as $subscriber) {
                
                //find and replace in message content
                $message_content = str_replace('[[subscriber_email]]', $subscriber->post_title, $message_content);
                
                $bogoblast_email_message = new BogoblastEMailMessage();
                
                //load message object via wp_mail call, but don't send right now
                $bogoblast_email_message->wp_mail($subscriber->post_title, $promotion_vars['message_subject'], $message_content, $message_header_string, array(), false);
                $bogoblast_email_message->set_scheduled_send_date = $send_date;
                
                //set some tracking vars
                $bogoblast_email_message->set_trackable('bogoblast_business_id', $promotion_vars['bogoblast_business_id']);
                $bogoblast_email_message->set_trackable('bogoblast_promotion_id', $post_id);
                $bogoblast_email_message->set_trackable('bogoblast_subscriber_id', $subscriber->ID);
                
                if($bogoblast_email_message->save() == false) {
                    Bogoblast::log_event("Could not queue message to bogoblast_subscriber '{$subscriber->post_title}' (ID: {$subscriber->ID}) for bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id).");
                    $error_messages[] = "Could not queue message to bogoblast_subscriber '{$subscriber->post_title}' (ID: {$subscriber->ID}) for bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id).";
                }
            }
        } elseif(is_array($recipients) && sizeof($recipients) > 0) {
            foreach($recipients as $recipient) {
                
                //find and replace in message content
                $message_content = str_replace('[[subscriber_email]]', $recipient, $message_content);
                
                $bogoblast_email_message = new BogoblastEMailMessage();
                
                //load message object via wp_mail call, but don't send right now
                $bogoblast_email_message->wp_mail(trim($recipient), $promotion_vars['message_subject'], $message_content, $message_header_string, array(), false);
                $bogoblast_email_message->set_scheduled_send_date = $send_date;
                
                if($bogoblast_email_message->save() == false) {
                    Bogoblast::log_event("Could not queue message to bogoblast_subscriber '{$subscriber->post_title}' (ID: {$subscriber->ID}) for bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id).");
                    $error_messages[] = "Could not queue message to bogoblast_subscriber '{$subscriber->post_title}' (ID: {$subscriber->ID}) for bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id).";
                }
            }
        }
        
        Bogoblast::log_event("bogoblast_promotion '".$promotion_vars['post_title']."' (ID: $post_id) was queued successfully!");
    }
    
    if(!$fatal_error) {
        $insert_count = ($limit + $offset) > $subscriber_count ? $subscriber_count : ($limit + $offset);
        
        echo json_encode(array(
            'promotion_queue_id' => $promotion_queue_id,
            'post_id' => $post_id,
            'send_to' => $send_to,
            'send_month' =>  $send_month,
            'send_day' => $send_day,
            'send_year' => $send_year,    
            'limit' => $limit,
            'new_offset' => ($limit + $offset),
            'subscriber_count' => $subscriber_count,
            'insert_count' => $insert_count,
            'insert_remaining' => $subscriber_count - $insert_count,
            'insert_percent' => number_format((( $insert_count / $subscriber_count ) * 100), 1),
            'has_errors' => (sizeof($error_messages > 0)),
            'error_messages' => $error_messages,
            'fatal_error' => $fatal_error
        ));
    } else {
        echo json_encode(array(
            'post_id' => $post_id,
            'send_to' => $send_to,
            'send_month' =>  $send_month,
            'send_day' => $send_day,
            'send_year' => $send_year,    
            'limit' => $limit,
            'has_errors' => (sizeof($error_messages > 0)),
            'error_messages' => $error_messages,
            'fatal_error' => $fatal_error
        ));
    }