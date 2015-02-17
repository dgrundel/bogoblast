<?php
    //check post type
    if($_POST['post_type'] != 'bogoblast_subscriber') return $post_id;

    // verify nonce
    if (!wp_verify_nonce($_POST['bogoblast_subscriber_meta_box_nonce'], 'bogoblast_subscriber_meta_box_nonce')) return $post_id;
    
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

    // check permissions
    if (!current_user_can('edit_post', $post_id)) {
        wp_die("You don't have permission to edit post $post_id");
    }
    
    if(is_array($_POST['bogoblast_businesses'])) {
        foreach($_POST['bogoblast_businesses'] as $business_id => $value) {
            
            //have to delete first to make sure we're not adding a duplicate
            delete_post_meta($post_id, '_bogoblast_business_id', $business_id);
            
            if(intval($value)) add_post_meta($post_id, '_bogoblast_business_id', $business_id, false);
        }
    }
?>