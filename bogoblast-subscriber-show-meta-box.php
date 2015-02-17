<?php
    global $post;
    
    $bogoblast_business_ids = get_post_meta($post->ID, '_bogoblast_business_id', false);
    
    if(sizeof($bogoblast_business_ids) > 0) {
        $selected_bogoblast_businesses_query_vars = array(
            'numberposts' => -1,
            'post_type' => 'bogoblast_business',
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'any',
            'post__in' => $bogoblast_business_ids);
        //$selected_bogoblast_businesses = get_posts($selected_bogoblast_businesses_query_vars);
        $selected_bogoblast_businesses = Bogoblast::get_businesses($selected_bogoblast_businesses_query_vars);
    } else {
        $selected_bogoblast_businesses = array();
    }
    
    $bogoblast_business_query_vars = array(
        'numberposts' => -1,
        'post_type' => 'bogoblast_business',
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'any',
        'post__not_in' => $bogoblast_business_ids);
    //$bogoblast_businesses = get_posts($bogoblast_business_query_vars);
    $bogoblast_businesses = Bogoblast::get_businesses($bogoblast_business_query_vars);
?>

<!-- nonce for verification -->
<input type="hidden" name="bogoblast_subscriber_meta_box_nonce" value="<?php echo wp_create_nonce('bogoblast_subscriber_meta_box_nonce'); ?>">

<table class="form-table">
    <tr>
        <th><label>Subscribed To</label></th>
        <td>
            <ul class="bogoblast_businesses">
                <?php if(is_array($selected_bogoblast_businesses) && sizeof($selected_bogoblast_businesses) > 0):
                    foreach($selected_bogoblast_businesses as $bogoblast_business): ?>
                        
                        <li>
                            <input type="hidden" name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]" value="0">
                            <input type="checkbox"
                                id="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"
                                name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]"
                                value="1" checked="checked" />
                            <label for="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?></label>
                        </li>
                        
                    <?php endforeach;
                endif; ?>
                
                <?php if(is_array($bogoblast_businesses) && sizeof($bogoblast_businesses) > 0):
                    foreach($bogoblast_businesses as $bogoblast_business): ?>
                        
                        <li>
                            <input type="hidden" name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]" value="0">
                            <input type="checkbox"
                                id="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"
                                name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]"
                                value="1" />
                            <label for="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?></label>
                        </li>
                        
                    <?php endforeach;
                endif; ?>
            </ul>
        </td>
    </tr>
    <?php
        $confirmation_code = get_post_meta($post->ID, '_confirmation_code', true);
        if(strlen($confirmation_code) > 0):
            $confirmation_timestamp = intval(get_post_meta($post->ID, '_confirmation_timestamp', true));
    ?>
    <tr>
        <th>E-Mail Confirmation Code</th>
        <td>
            <pre><?php echo $confirmation_code; ?></pre>
            <p class="description">Generated: <?php echo date('r', $confirmation_timestamp); ?></p>
        </td>
    </tr>
    <?php endif; ?>
</table>