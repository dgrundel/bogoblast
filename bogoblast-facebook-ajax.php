<?php

    $post_id = $_POST['post_id'];
    
    $status_messages = array();
    $error_messages = array();
    $fatal_error = false;
    
    //get promotion
    $bogoblast_promotion = get_post($post_id);
    if($bogoblast_promotion->post_type != 'bogoblast_promotion') {
        //error
        
        //only log an event if it's not a revision. We don't care about those.
        if($bogoblast_promotion->post_type != 'revision') {
            Bogoblast::log_event("Post '{$bogoblast_promotion->post_title}' (ID: $post_id) is not a bogoblast_promotion.");
            $error_messages[] = "Post '{$bogoblast_promotion->post_title}' (ID: $post_id) is not a bogoblast_promotion.";
            $fatal_error = true;
        }
    }
    
    //get business
    if(!$fatal_error) {
        $bogoblast_business_id = intval(get_post_meta($post_id, '_bogoblast_business_id', true));
        if($bogoblast_business_id <= 0) {
            //error
            //Bogoblast::queue_flash_message("<a href=\"{$admin_url}post.php?post={$post_id}&action=edit\">{$bogoblast_promotion->post_title}</a> has no associated Business.", 'error');
            Bogoblast::log_event("bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no associated bogoblast_business.");
            $error_messages[] = "bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id) has no associated bogoblast_business.";
            $fatal_error = true;
        }
    }
    
    if(!$fatal_error) {
        $bogoblast_business = get_post($bogoblast_business_id);
        if($bogoblast_business === null) {
            //error
            //Bogoblast::queue_flash_message("Couldn't find a business with ID {$bogoblast_business_id} for <a href=\"{$admin_url}post.php?post={$post_id}&action=edit\">{$bogoblast_promotion->post_title}</a>.", 'error');
            Bogoblast::log_event("bogoblast_business $bogoblast_business_id doesn't exist. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
            $error_messages[] = "bogoblast_business $bogoblast_business_id doesn't exist. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
            $fatal_error = true;
        }
    }
    
    if(!$fatal_error) {
        $business_status = get_post_meta($bogoblast_business->ID, '_bogoblast_business_status', true);
        switch($business_status) {
            case 'active_public':
            case 'active_private':
                break;
            default:
                //Bogoblast::queue_flash_message("<a href=\"{$admin_url}post.php?post={$bogoblast_business_id}&action=edit\">{$bogoblast_business->post_title}</a> is not an active Business.", 'error');
                Bogoblast::log_event("bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) is not an active business. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).");
                $error_messages[] = "bogoblast_business '{$bogoblast_business->post_title}' (ID: $bogoblast_business_id) is not an active business. Attempted to use it for bogoblast_promotion '{$bogoblast_promotion->post_title}' (ID: $post_id).";
                $fatal_error = true;
        }
    }
    
    if(!$fatal_error) {
        
        require(BOGOBLAST_DIR.'lib/tmhOAuth/tmhOAuth.php');
        require(BOGOBLAST_DIR.'lib/tmhOAuth/tmhUtilities.php');
        $tmhOAuth = new tmhOAuth(array(
            'consumer_key'    => get_option('bogoblast__twitter_consumer_key'),
            'consumer_secret' => get_option('bogoblast__twitter_consumer_secret'),
            'user_token'      => get_option('bogoblast__twitter_user_token'),
            'user_secret'     => get_option('bogoblast__twitter_user_secret'),
        ));
        
        //get t.co URL length
        $code = $tmhOAuth->request('GET', $tmhOAuth->url('1/help/configuration.json'));
        if ($code == 200) {
            $help_config = json_decode($tmhOAuth->response['response']);
            $short_url_length = intval($help_config->short_url_length);
            //$status_messages[] = "Short URL Length: {$short_url_length}";
        } else {
            $short_url_length = 24;
        }
        
        $max_tweet_length = 140;
        
        $promotion_content = apply_filters('the_content', $bogoblast_promotion->post_content);
        $promotion_content = str_replace(']]>', ']]&gt;', $promotion_content);
        $promotion_content = strip_tags($promotion_content);
        
        $the_tweet = "{$bogoblast_business->post_title}:{$promotion_content}";
        
        $business_lat = floatval(get_post_meta($bogoblast_business->ID, '_bogoblast_business_latitude', true));
        $business_lng = floatval(get_post_meta($bogoblast_business->ID, '_bogoblast_business_longitude', true));
        
        $permalink = get_permalink($bogoblast_business->ID);
        $permalink .= "?utm_source=bogoblast&utm_medium=twitter&utm_campaign=bogoblast_promotion_{$bogoblast_promotion->ID}";
        
        if(strlen($the_tweet) > ($max_tweet_length - $short_url_length)) {
            $the_tweet = substr($the_tweet, 0, ($max_tweet_length - $short_url_length - 4)) . '... ';
        }
        
        $the_tweet .= $permalink;
        
        $tweet_params = array();
        $tweet_params['status'] = $the_tweet;
        
        if($business_lat > 0 && $business_lng > 0) {
            $tweet_params['lat'] = $business_lat;
            $tweet_params['long'] = $business_lng;
            $tweet_params['display_coordinates'] = true;
        }
        
        $code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), $tweet_params);
        //$code = 200;
        
        if ($code == 200) {
            //tmhUtilities::pr(json_decode($tmhOAuth->response['response']));
            $status_messages[] = "Successfully Tweeted: '{$the_tweet}'";
        } else {
            $error_messages[] = $tmhOAuth->response['response'];
            $fatal_error = true;
        }
    }
    
    echo json_encode(array(
        'post_id' => $post_id,
        'has_errors' => (sizeof($error_messages > 0)),
        'error_messages' => $error_messages,
        'has_status' => (sizeof($status_messages > 0)),
        'status_messages' => $status_messages,
        'fatal_error' => $fatal_error
    ));