<?php
    //check post type
    if($_POST['post_type'] != 'bogoblast_business') return $post_id;

    // check permissions
    if (!current_user_can('manage_options')) return $post_id;
    
    //kill all associations with subscribers
    $subscriber_query = array(
        'numberposts' => -1,
        'meta_key' => '_bogoblast_business_id',
        'meta_query' => array(
            array(
                'key'=>'_bogoblast_business_id',
                'value'=> $post_id,
                'compare' => '='
            )
        ),
        'post_type' => 'bogoblast_subscriber');
    $bogoblast_subscribers = get_posts($subscriber_query);
    if(is_array($bogoblast_subscribers) && sizeof($bogoblast_subscribers) > 0) {
        foreach($bogoblast_subscribers as $bogoblast_subscriber) {
            delete_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', $post_id);
        }
    }
    
    
?>