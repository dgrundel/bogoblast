<?php
    //check post type
    if($_POST['post_type'] != 'bogoblast_business') return $post_id;

    // verify nonce
    if (!wp_verify_nonce($_POST['bogoblast_business_meta_box_nonce'], 'bogoblast_business_meta_box_nonce')) return $post_id;
    
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

    // check permissions
    if (!current_user_can('edit_post', $post_id)) {
        wp_die("You don't have permission to edit post $post_id");
    }
    
    Bogoblast::clear_business_cache();
    
    if(is_array($_POST['bogoblast_business'])) {
        
        $business_status = $_POST['bogoblast_business']['status'];
        add_post_meta($post_id, '_bogoblast_business_status', $business_status, true) or
            update_post_meta($post_id, '_bogoblast_business_status', $business_status);
        
        //set post status based business status so inactive and private businesses won't show on site.
        $current_post = array('ID' => $post_id);
        
        switch($business_status) {
            case 'active_public':
            case 'listing_only':
                $current_post['post_status'] = 'publish';
                break;
            case 'active_private':
                $current_post['post_status'] = 'private';
                break;
            case 'inactive':
            default:
                $current_post['post_status'] = 'draft';
                break;
        }
        
        //unhook this function so it doesn't loop infinitely
        remove_action('save_post', array(&$this, 'business_save_meta_box'));
        
        wp_update_post($current_post);
        
        //rehook this function
        add_action('save_post', array(&$this, 'business_save_meta_box'));
        
        add_post_meta($post_id, '_bogoblast_business_owner_name', $_POST['bogoblast_business']['owner_name'], true) or
            update_post_meta($post_id, '_bogoblast_business_owner_name', $_POST['bogoblast_business']['owner_name']);
        
        $owner_cell = BogoblastUtil::strip_nonnumeric_chars($_POST['bogoblast_business']['owner_cell_phone'], true, true);
        add_post_meta($post_id, '_bogoblast_business_owner_cell_phone', $owner_cell, true) or
            update_post_meta($post_id, '_bogoblast_business_owner_cell_phone', $owner_cell);
        
        add_post_meta($post_id, '_bogoblast_business_owner_email', $_POST['bogoblast_business']['owner_email'], true) or
            update_post_meta($post_id, '_bogoblast_business_owner_email', $_POST['bogoblast_business']['owner_email']);
        
        $address_1 = $_POST['bogoblast_business']['address_1'];
        add_post_meta($post_id, '_bogoblast_business_address_1', $_POST['bogoblast_business']['address_1'], true) or
            update_post_meta($post_id, '_bogoblast_business_address_1', $_POST['bogoblast_business']['address_1']);
        
        $address_2 = $_POST['bogoblast_business']['address_2'];
        add_post_meta($post_id, '_bogoblast_business_address_2', $_POST['bogoblast_business']['address_2'], true) or
            update_post_meta($post_id, '_bogoblast_business_address_2', $_POST['bogoblast_business']['address_2']);
        
        $city = $_POST['bogoblast_business']['city'];
        add_post_meta($post_id, '_bogoblast_business_city', $_POST['bogoblast_business']['city'], true) or
            update_post_meta($post_id, '_bogoblast_business_city', $_POST['bogoblast_business']['city']);
        
        $state = $_POST['bogoblast_business']['state'];
        add_post_meta($post_id, '_bogoblast_business_state', $_POST['bogoblast_business']['state'], true) or
            update_post_meta($post_id, '_bogoblast_business_state', $_POST['bogoblast_business']['state']);
        
        $zip = $_POST['bogoblast_business']['zip'];
        if(BogoblastUtil::validate_as('zip', $zip)) {
            add_post_meta($post_id, '_bogoblast_business_zip', $zip, true) or
                update_post_meta($post_id, '_bogoblast_business_zip', $zip);
        } else {
            Bogoblast::queue_flash_message("'$zip' is not a valid zip code.", 'error');
        }
        
        add_post_meta($post_id, '_bogoblast_business_directions', $_POST['bogoblast_business']['directions'], true) or
            update_post_meta($post_id, '_bogoblast_business_directions', $_POST['bogoblast_business']['directions']);
        
        $latitude = BogoblastUtil::strip_nonnumeric_chars($_POST['bogoblast_business']['latitude']);
        $longitude = BogoblastUtil::strip_nonnumeric_chars($_POST['bogoblast_business']['longitude']);
        
        if(floatval($latitude) == 0 || floatval($longitude) == 0) {
            $geocode_result = BogoblastUtil::geocode_address("$address_1 $address_2, $city, $state $zip");
            if($geocode_result !== null) {
                $latitude = $geocode_result['lat'];
                $longitude = $geocode_result['lng'];
            }
        }
        
        add_post_meta($post_id, '_bogoblast_business_latitude', $latitude, true) or
            update_post_meta($post_id, '_bogoblast_business_latitude', $latitude);
        add_post_meta($post_id, '_bogoblast_business_longitude', $longitude, true) or
            update_post_meta($post_id, '_bogoblast_business_longitude', $longitude);
        
        
        
        
        $phone = BogoblastUtil::strip_nonnumeric_chars($_POST['bogoblast_business']['phone'], true, true);
        add_post_meta($post_id, '_bogoblast_business_phone', $phone, true) or
            update_post_meta($post_id, '_bogoblast_business_phone', $phone);
        
        $fax = BogoblastUtil::strip_nonnumeric_chars($_POST['bogoblast_business']['fax'], true, true);
        add_post_meta($post_id, '_bogoblast_business_fax', $fax, true) or
            update_post_meta($post_id, '_bogoblast_business_fax', $fax);
        
        $email = $_POST['bogoblast_business']['email'];
        if(BogoblastUtil::validate_as('email', $email)) {
            add_post_meta($post_id, '_bogoblast_business_email', $email, true) or
                update_post_meta($post_id, '_bogoblast_business_email', $email);
        } else {
            Bogoblast::queue_flash_message("'$email' is not a valid e-mail address.", 'error');
        }
        
        add_post_meta($post_id, '_bogoblast_business_url', $_POST['bogoblast_business']['url'], true) or
            update_post_meta($post_id, '_bogoblast_business_url', $_POST['bogoblast_business']['url']);
        
        add_post_meta($post_id, '_bogoblast_business_facebook', $_POST['bogoblast_business']['facebook'], true) or
            update_post_meta($post_id, '_bogoblast_business_facebook', $_POST['bogoblast_business']['facebook']);
        
        add_post_meta($post_id, '_bogoblast_business_twitter', $_POST['bogoblast_business']['twitter'], true) or
            update_post_meta($post_id, '_bogoblast_business_twitter', $_POST['bogoblast_business']['twitter']);
        
        add_post_meta($post_id, '_bogoblast_business_google_plus', $_POST['bogoblast_business']['google_plus'], true) or
            update_post_meta($post_id, '_bogoblast_business_google_plus', $_POST['bogoblast_business']['google_plus']);
        
        add_post_meta($post_id, '_bogoblast_business_linkedin', $_POST['bogoblast_business']['linkedin'], true) or
            update_post_meta($post_id, '_bogoblast_business_linkedin', $_POST['bogoblast_business']['linkedin']);
        
        add_post_meta($post_id, '_bogoblast_business_youtube', $_POST['bogoblast_business']['youtube'], true) or
            update_post_meta($post_id, '_bogoblast_business_youtube', $_POST['bogoblast_business']['youtube']);
        
        add_post_meta($post_id, '_bogoblast_business_pinterest', $_POST['bogoblast_business']['pinterest'], true) or
            update_post_meta($post_id, '_bogoblast_business_pinterest', $_POST['bogoblast_business']['pinterest']);
    }
?>