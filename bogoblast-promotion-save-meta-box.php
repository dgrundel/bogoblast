<?php
    //check post type
    if($_POST['post_type'] != 'bogoblast_promotion') return $post_id;

    // verify nonce
    if (!wp_verify_nonce($_POST['bogoblast_promotion_meta_box_nonce'], 'bogoblast_promotion_meta_box_nonce')) return $post_id;
    
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

    // check permissions
    if (!current_user_can('edit_post', $post_id)) {
        wp_die("You don't have permission to edit post $post_id");
    }
    
    if(isset($_POST['bogoblast_business_id'])) {
        $selected_business_id = intval($_POST['bogoblast_business_id']);
        
        if($selected_business_id) {
            add_post_meta($post_id, '_bogoblast_business_id', $selected_business_id, true) or
                update_post_meta($post_id, '_bogoblast_business_id', $selected_business_id);
        } else {
            delete_post_meta($post_id, '_bogoblast_business_id');
        }
    }
    
    if(isset($_POST['bogoblast_promotion_email_template'])) {
        $promotion_email_template = trim($_POST['bogoblast_promotion_email_template']);
        
        add_post_meta($post_id, '_promotion_email_template', $promotion_email_template, true) or
            update_post_meta($post_id, '_promotion_email_template', $promotion_email_template);
    }
    
    if(isset($_POST['bogoblast_promotion_email_subject'])) {
        $promotion_email_subject = trim($_POST['bogoblast_promotion_email_subject']);
        
        add_post_meta($post_id, '_promotion_email_subject', $promotion_email_subject, true) or
            update_post_meta($post_id, '_promotion_email_subject', $promotion_email_subject);
    }
    
    if(isset($_POST['bogoblast_promotion_expiration_month']) &&
       isset($_POST['bogoblast_promotion_expiration_day']) &&
       isset($_POST['bogoblast_promotion_expiration_year'])) {
        
        $month = $_POST['bogoblast_promotion_expiration_month'];
        $day = $_POST['bogoblast_promotion_expiration_day'];
        $year = $_POST['bogoblast_promotion_expiration_year'];
        
        if(checkdate($month, $day, $year)) {
            $expiration_timestamp = mktime(0, 0, 0, $month, $day, $year);
            
            add_post_meta($post_id, '_promotion_expiration', $expiration_timestamp, true) or
                update_post_meta($post_id, '_promotion_expiration', $expiration_timestamp);
        }
    }
    
    if(isset($_POST['bogoblast_promotion_fine_print'])) {
        $promotion_fine_print = $_POST['bogoblast_promotion_fine_print'];
        
        add_post_meta($post_id, '_promotion_fine_print', $promotion_fine_print, true) or
            update_post_meta($post_id, '_promotion_fine_print', $promotion_fine_print);
    }
?>