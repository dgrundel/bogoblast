<div class="bogoblast_subscription_preferences">
    <form method="post">
        <?php
        
            $request_email_address = isset($_REQUEST['subscriber_email_address']) ? $_REQUEST['subscriber_email_address'] : null;
            $request_confirmation_code = isset($_REQUEST['confirmation_code']) ? $_REQUEST['confirmation_code'] : null;
            $bogoblast_subscriber = ($request_email_address !== null) ? get_page_by_title($request_email_address, 'OBJECT', 'bogoblast_subscriber') : null;
            $confirmation_timeout_interval = 60 * 60 * 24; //1 day
            $bypass_confirmation = false;
            
            if($request_email_address !== null && $bogoblast_subscriber === null) {
                
                $bogoblast_subscriber = Bogoblast::get_or_create_subscriber($request_email_address);
                
                if($bogoblast_subscriber === null) {
                    ?><div class="error">Uh oh. Looks like we're having a problem right now.
                        E-mail us at <a href="mailto:hello@bogoblast.com">hello@bogoblast.com</a>
                        and we'll take care of you!</div><?php
                } else {
                    $bypass_confirmation = true;
                }
            }
        
            //Get E-Mail Address
            if($bogoblast_subscriber === null):
                ?>
                <div class="notice">Please enter your e-mail address to update your preferences.</div>
                <div class="form_field">
                    <label for="subscriber_email_address">E-Mail Address</label>
                    <input type="email" class="input_text" name="subscriber_email_address" id="subscriber_email_address">
                </div>
                <div class="form_footer">
                    <button type="submit">Submit</button>
                </div><?php
            
            else:
                
                if($request_confirmation_code === null && !$bypass_confirmation):
                
                    //Send Confirmation
                    $confirmation_timestamp = time();
                    $confirmation_code = BogoblastUtil::random_string(12, 'alphanumeric');
                    
                    add_post_meta($bogoblast_subscriber->ID, '_confirmation_timestamp', $confirmation_timestamp, true) or
                        update_post_meta($bogoblast_subscriber->ID, '_confirmation_timestamp', $confirmation_timestamp);
                    add_post_meta($bogoblast_subscriber->ID, '_confirmation_code', $confirmation_code, true) or
                        update_post_meta($bogoblast_subscriber->ID, '_confirmation_code', $confirmation_code);
                    
                    $link_url = BogoblastUtil::current_url();
                    $link_url = BogoblastUtil::set_url_query_value($link_url, 'subscriber_email_address', $request_email_address);
                    $link_url = BogoblastUtil::set_url_query_value($link_url, 'confirmation_code', $confirmation_code);
                    
                    wp_mail(
                        $request_email_address,
                        "Please Confirm Your E-Mail Address",
                        "Hello,
    
    A request was made to update your e-mail preferences with Bogoblast.
    
    Your confirmation code is: {$confirmation_code}
    You can confirm your address by visiting this link:
    {$link_url}
    
    Your confirmation code will expire in one day.
    
    If you do not wish to update your subscription preferences, simply ignore this message.
    
    Thanks!
    Your Friends at Bogoblast");
                    
                    ?><div class="notice">Please check your e-mail and click the confirmation link that was just sent to you.</div>
                    <div class="form_field">
                        <label for="confirmation_code">Confirmation Code</label>
                        <input type="text" class="input_text" name="confirmation_code" id="confirmation_code">
                    </div>
                    <div class="form_footer">
                        <input type="hidden" name="subscriber_email_address" value="<?php echo htmlspecialchars($request_email_address); ?>" />
                        <button type="submit">Submit</button>
                    </div><?php
                
                else:
                
                    //Get Confirmed and Actually Set Prefs
                    $bogoblast_subscriber_confirmation_code = get_post_meta($bogoblast_subscriber->ID, '_confirmation_code', true);
                    $bogoblast_subscriber_confirmation_timestamp = intval(get_post_meta($bogoblast_subscriber->ID, '_confirmation_timestamp', true));
                    
                    if($request_confirmation_code != $bogoblast_subscriber_confirmation_code && !$bypass_confirmation):
                        ?><div class="error">Invalid confirmation code for that e-mail address.</div><?php
                    else:
                        if( (time() - $bogoblast_subscriber_confirmation_timestamp) > $confirmation_timeout_interval && !$bypass_confirmation):
                            ?><div class="error">Your confirmation code has expired. Please try again.</div><?php
                        else:
                            
                            if(isset($_REQUEST['bogoblast_businesses']) && is_array($_REQUEST['bogoblast_businesses'])):
                            
                                foreach($_REQUEST['bogoblast_businesses'] as $business_id => $value) {
                                    
                                    //have to delete first to make sure we're not adding a duplicate
                                    delete_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', $business_id);
                                    
                                    if(intval($value)) add_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', $business_id, false);
                                }
                                ?><div class="notice">Your preferences have been updated.</div><?php
                            
                            else:
                                
                                $bogoblast_business_ids = get_post_meta($bogoblast_subscriber->ID, '_bogoblast_business_id', false);
                                
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
                                    
                                    //$bogoblast_business_query_vars = array(
                                    //    'numberposts' => -1,
                                    //    'post_type' => 'bogoblast_business',
                                    //    'orderby' => 'title',
                                    //    'order' => 'ASC',
                                    //    'post__not_in' => $bogoblast_business_ids);
                                    //$bogoblast_businesses = get_posts($bogoblast_business_query_vars);
                                    $bogoblast_businesses = Bogoblast::get_businesses();
                                    
                                } else {
                                    
                                    $selected_bogoblast_businesses = array();
                                    
                                    $bogoblast_business_query_vars = array(
                                        'numberposts' => -1,
                                        'post_type' => 'bogoblast_business',
                                        'orderby' => 'title',
                                        'order' => 'ASC');
                                    //$bogoblast_businesses = get_posts($bogoblast_business_query_vars);
                                    $bogoblast_businesses = Bogoblast::get_businesses($bogoblast_business_query_vars);
                                }
                                
                                ?><ul class="bogoblast_businesses">
                                    <?php if(is_array($selected_bogoblast_businesses) && sizeof($selected_bogoblast_businesses) > 0):
                                        foreach($selected_bogoblast_businesses as $bogoblast_business): ?>
                                            <li>
                                                <input type="hidden" name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]" value="0">
                                                <input type="checkbox"
                                                    id="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"
                                                    name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]"
                                                    value="1" checked="checked">
                                                <label for="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?></label>
                                            </li>
                                        <?php endforeach;
                                    endif;
                                    
                                    if(is_array($bogoblast_businesses) && sizeof($bogoblast_businesses) > 0):
                                        foreach($bogoblast_businesses as $bogoblast_business): ?>
                                            <li>
                                                <input type="hidden" name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]" value="0">
                                                <input type="checkbox"
                                                    id="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"
                                                    name="bogoblast_businesses[<?php echo $bogoblast_business->ID ?>]"
                                                    value="1">
                                                <label for="bogoblast_businesses_<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?></label>
                                            </li>
                                        <?php endforeach;
                                    endif; ?>
                                </ul>
                                <div class="form_footer">
                                    <input type="hidden" name="subscriber_email_address" value="<?php echo htmlspecialchars($request_email_address); ?>" />
                                    <input type="hidden" name="confirmation_code" value="<?php echo htmlspecialchars($request_confirmation_code); ?>" />
                                    <button type="submit">Submit</button>
                                </div><?php
                                
                            endif;
                            
                        endif;
                    endif;
    
                endif;
                
            endif;
    
        ?>
    </form>
</div>