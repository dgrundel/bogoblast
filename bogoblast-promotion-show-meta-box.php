<?php
    global $post;
    
    $bogoblast_business_id = get_post_meta($post->ID, '_bogoblast_business_id', true);
    
    //$query_vars = array(
    //    'numberposts' => -1,
    //    'post_type' => 'bogoblast_business',
    //    'orderby' => 'title',
    //    'order' => 'ASC',
    //    'post_status' => 'any');
    //$bogoblast_businesses = get_posts($query_vars);
    $bogoblast_businesses = Bogoblast::get_businesses();
    
    $promotion_email_subject = get_post_meta($post->ID, '_promotion_email_subject', true);
    
    $promotion_email_template = get_post_meta($post->ID, '_promotion_email_template', true);
    
    $email_templates = array();
    $template_dir = BOGOBLAST_DIR.'mail-templates';
    $dir_items = scandir($template_dir);
    foreach($dir_items as $dir_item) {
        
        if($dir_item != '..' && $dir_item != '.' &&
            file_exists("{$template_dir}/{$dir_item}/mail.php")) {
            
            $email_templates[$dir_item] = "{$template_dir}/{$dir_item}/mail.php";
        }
    }
    
    $promotion_expiration = intval(get_post_meta($post->ID, '_promotion_expiration', true));
    if($promotion_expiration == 0) {
        //no expiration set. figure out a sensible default.
        $now = getdate();
        
        if($now['mday'] <= 15) {
            //it's before the 15th. Let's set the expiration as the last day of this month.
            //the 0th day of next month == last day of this month.
            $promotion_expiration = mktime(0, 0, 0, $now['mon']+1, 0, $now['year']);
        } else {
            //it's after the 15th. Let's set the expiration as the 15th of next month.
            $promotion_expiration = mktime(0, 0, 0, $now['mon']+1, 15, $now['year']);
        }
    }
    $promotion_expiration_info = getdate($promotion_expiration);
    
    $promotion_fine_print = get_post_meta($post->ID, '_promotion_fine_print', true);
    
    $old_scheduled_date = intval(get_post_meta($post->ID, '_send_on_schedule_date', true));
    $bogoblast_promotion_send_on_schedule_date = $old_scheduled_date;
    if($bogoblast_promotion_send_on_schedule_date == 0) {
        
        $now = getdate();
        
        if($now['mday'] <= 15) {
            //it's before the 15th. set send date to the 15th of this month.
            $bogoblast_promotion_send_on_schedule_date = mktime(0, 0, 0, $now['mon'], 15, $now['year']);
        } else {
            //it's after the 15th. set send date to the 1st of next month.
            $bogoblast_promotion_send_on_schedule_date = mktime(0, 0, 0, $now['mon']+1, 1, $now['year']);
        }
    }
    $promotion_send_on_schedule_date_info = getdate($bogoblast_promotion_send_on_schedule_date);
?>

<style type="text/css">
    table.form-table table th,
    table.form-table table td {
        padding: 0;
    }
    
    .send_queue_result { }
    
    #send_test_result,
    #send_on_schedule_result,
    #send_now_result {
        display: none;
    }
    
    .send_queue_result_stats {
        display: none;
        padding: 0 0 0 50px;
        background: transparent url('<?php echo BOGOBLAST_URL ?>img/ajax-loader_sm.gif') 4px 50% no-repeat;
    }
    .send_queue_result_stats.complete {
        background: transparent url('<?php echo BOGOBLAST_URL ?>img/complete.png') 4px 50% no-repeat;
    }
    .send_queue_result_stats.fatal_error {
        background: transparent url('<?php echo BOGOBLAST_URL ?>img/fatal-error.png') 4px 50% no-repeat;
    }
    
    .send_queue_error_messages li {
        margin: 2px 0;
        padding: 3px;
        background-color: #f9dede;
        border: 1px solid #ff8e8e;
    }
    
    .send_queue_status_messages li {
        margin: 2px 0;
        padding: 3px;
        background-color: #ecfdbe;
        border: 1px solid #a1dd00;
    }
    
    .send_queue_result_show_debug { display: none; }
    
    .send_queue_result_debug {
        display: none;
        font-family: monospace;
        color: #333;
        background-color: #f8f8f8;
        border: 1px solid #dedede;
        padding: 0 4px;
    }
</style>

<script type="text/javascript">
    var default_limit = 50;
    var last_limit = 0;
    var last_offset = 0;
    var last_args = {};

    jQuery(document).ready(function($){
        
        $(".send_queue_result_show_debug").click(function(){
            $(this).css('display', 'none');
            $(".send_queue_result_debug").css('display', 'block');
            
            return false;
        });
        
        $("#send_test").click(function(){
            var send_to = $("#bogoblast_promotion_send_test_to").val();
            
            if(typeof(send_to) == 'string' && send_to.length > 0) {
                var args = {
                    "send_to": send_to
                };
                
                clear_status_messages();
                do_ajax_promotion_queue(default_limit, 0, args);
            } else {
                alert('Send to field is empty!');
            }
            
            return false; //so the page doesn't scroll
        });
        
        $("#send_on_schedule").click(function(){
            if(confirm("Are you sure?")) {
                var args = {
                    "send_month": $("#bogoblast_promotion_send_on_schedule_month").val(),
                    "send_day": $("#bogoblast_promotion_send_on_schedule_day").val(),
                    "send_year": $("#bogoblast_promotion_send_on_schedule_year").val()
                };
                
                clear_status_messages();
                do_ajax_promotion_queue(default_limit, 0, args);
            }
            
            return false; //so the page doesn't scroll
        });
        
        $("#send_now").click(function(){
            if(confirm("Are you sure?")) {
                clear_status_messages();
                do_ajax_promotion_queue(default_limit, 0, {});
            }
            
            return false; //so the page doesn't scroll
        });

        $(".ajax_resume").live("click", function(){
            $(".ajax_resume").remove();

            do_ajax_promotion_queue(last_limit, last_offset, last_args);

            return false; //so the page doesn't scroll
        });
        
        function do_ajax_promotion_queue(limit, offset, args) {
            
            last_limit = limit;
            last_offset = offset;
            last_args = args;

            $(".send_queue_result").css("display", "block");
            
            send_to = ('send_to' in args) ? args.send_to : '';
            send_month = ('send_month' in args) ? args.send_month : 0;
            send_day = ('send_day' in args) ? args.send_day : 0;
            send_year = ('send_year' in args) ? args.send_year : 0;
            promotion_queue_id = ('promotion_queue_id' in args) ? args.promotion_queue_id : '';
            
            var post_data = {
                "post_id": <?php echo $post->ID; ?>,
                "action": "bogoblast_promotion_queue_ajax",
                "send_to": send_to,
                "send_month": send_month,
                "send_day": send_day,
                "send_year": send_year,
                "promotion_queue_id": promotion_queue_id,
                "limit": limit,
                "offset": offset
            };
            
            //ajaxurl is defined by WordPress
            // jQuery.post(ajaxurl, post_data, ajax_promotion_queue_callback);
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: post_data,
                dataType: 'text',
                success: ajax_promotion_queue_callback,
                error: ajax_promotion_queue_error
            });
        }
        
        function ajax_promotion_queue_callback(response_text, status_text, jqXHR) {
            
            //data we get back:
            //'post_id' => $post_id,
            //'promotion_queue_id' => $promotion_queue_id,
            //'send_to' => $send_to,
            //'send_month' =>  $send_month,
            //'send_day' => $send_day,
            //'send_year' => $send_year,    
            //'limit' => $limit,
            //'new_offset' => ($limit + $offset),
            //'subscriber_count' => $subscriber_count,
            //'insert_count' => ($limit + $offset),
            //'insert_percent' => number_format((( ($limit + $offset) / $subscriber_count ) * 100), 1),
            //'has_errors' => (size($error_messages > 0)),
            //'error_messages' => $error_messages,
            //'fatal_error' => $fatal_error
            
            var p = $(document.createElement('p'));
            p.text(response_text);
            $('.send_queue_result_debug').append(p);
            
            $('.send_queue_result_show_debug').css('display', 'inline');
            
            var response = jQuery.parseJSON(response_text);
            
            if(response.has_errors) {
                for(var i in response.error_messages) {
                    var li = $(document.createElement('li'));
                    li.text(response.error_messages[i]);
                    $('.send_queue_error_messages').append(li);
                }
            }
            
            if(response.fatal_error) {
                var li = $(document.createElement('li'));
                li.text('Fatal error encountered. Promotion not sent.');
                $('.send_queue_error_messages').append(li);
                
                $('.send_queue_result_stats').addClass('fatal_error');
            } else {
                
                $('.send_queue_result_stats').css('display', 'block');
                
                $('.send_queue_subscriber_count').text(response.subscriber_count);
                $('.send_queue_insert_count').text(response.insert_count);
                $('.send_queue_insert_percent').text(response.insert_percent);
                
                if(parseInt(response.insert_remaining) > 0) {
                    var args = {
                        "send_month": response.send_month,
                        "send_day": response.send_day,
                        "send_year": response.send_year,
                        "promotion_queue_id": response.promotion_queue_id
                    };
                    
                    do_ajax_promotion_queue(response.limit, response.new_offset, args);
                } else {
                    $('.send_queue_result_stats').addClass('complete');
                }
            }
        }
        
        function ajax_promotion_queue_error(jqXHR, status_text, error) {

            var li = $(document.createElement('li'));
            li.text("AJAX Error: " + status_text + ", " + error + " ");

            var a = $(document.createElement('a'));
            a.text("Attempt to Resume");
            a.attr("href", "#");
            a.attr("class", "ajax_resume");
            li.append(a);

            $('.send_queue_error_messages').append(li);
        }

        $("#tweet_now").click(function(){
            if(confirm("Are you sure?")) {
                clear_status_messages();
                $("#tweet_result").css('display', 'block');
                do_ajax_tweet();
            }
            
            
            
            return false; //so the page doesn't scroll
        });
        
        function do_ajax_tweet() {
            
            var post_data = {
                "post_id": <?php echo $post->ID; ?>,
                "action": "bogoblast_twitter_ajax",
            };
            
            //ajaxurl is defined by WordPress
            //jQuery.post(ajaxurl, post_data, ajax_tweet_callback);
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: post_data,
                dataType: 'text',
                success: ajax_tweet_callback,
                error: ajax_tweet_error
            });
        }
        
        function ajax_tweet_callback(response_text) {
            
            //data we get back:
            //'post_id' => $post_id,
            //'has_errors' => (size($error_messages > 0)),
            //'error_messages' => $error_messages,
            //'fatal_error' => $fatal_error
            
            var p = $(document.createElement('p'));
            p.text(response_text);
            $('.send_queue_result_debug').append(p);
            
            $('.send_queue_result_show_debug').css('display', 'inline');
            
            var response = jQuery.parseJSON(response_text);
            
            if(response.has_errors) {
                for(var i in response.error_messages) {
                    var li = $(document.createElement('li'));
                    li.text(response.error_messages[i]);
                    $('.send_queue_error_messages').append(li);
                }
            }
            
            if(response.has_status) {
                for(var i in response.status_messages) {
                    var li = $(document.createElement('li'));
                    li.text(response.status_messages[i]);
                    $('.send_queue_status_messages').append(li);
                }
            }
            
            if(response.fatal_error) {
                var li = $(document.createElement('li'));
                li.text('Fatal error encountered. Tweet not sent.');
                $('.send_queue_error_messages').append(li);
            }
            
            //$('#tweet_result').css('display', 'block');
        }
        
        function ajax_tweet_error(jqXHR, status_text, error) {

            var li = $(document.createElement('li'));
            li.text("AJAX Error: " + status_text + ", " + error + " ");

            $('.send_queue_error_messages').append(li);
        }
        
        function clear_status_messages() {
            $('.send_queue_result_stats').removeClass('complete');
            $('.send_queue_result_stats').removeClass('fatal_error');
            $('.send_queue_result_stats').css('display', 'none');
            
            $('.send_queue_result_debug p').remove();
            $('.send_queue_error_messages li').remove();
            $('.send_queue_status_messages li').remove();
            
            $('.send_queue_subscriber_count').text('');
            $('.send_queue_insert_count').text('');
            $('.send_queue_insert_percent').text('');
            
            $(".send_queue_result").css("display", "none");
        }
    });
</script>

<!-- nonce for verification -->
<input type="hidden" name="bogoblast_promotion_meta_box_nonce" value="<?php echo wp_create_nonce('bogoblast_promotion_meta_box_nonce'); ?>">

<table class="form-table">
    <tr>
        <th>Promotion Stats</th>
        <td>
            <?php
                //$promotion_sent_query = array(
                //    'numberposts' => -1,
                //    'post_status' => 'publish',
                //    'meta_query' => array(
                //        array(
                //            'key'=>'_trackable_bogoblast_promotion_id',
                //            'value'=> $post->ID,
                //            'compare' => '='
                //        )
                //    ),
                //    'post_type' => 'bogoblast_mail');
                //$sent_promotions = get_posts($promotion_sent_query);
                //
                //$promotion_opened_query = array(
                //    'numberposts' => -1,
                //    'post_status' => 'any',
                //    'meta_query' => array(
                //        array(
                //            'key'=>'_trackable_bogoblast_promotion_id',
                //            'value'=> $post->ID,
                //            'compare' => '='
                //        ),
                //        array(
                //            'key'=>'_trackable_opened',
                //            'value'=> 1,
                //            'compare' => '='
                //        )
                //    ),
                //    'post_type' => 'bogoblast_mail');
                //$opened_promotions = get_posts($promotion_opened_query);
                
                $sent_count = Bogoblast::count_sent_mail_for($post->ID);
                $open_count = Bogoblast::count_opened_mail_for($post->ID);
            ?>
            <table>
                <tbody>
                    <tr>
                        <th>Sent</th>
                        <td><?php echo $sent_count; ?></td>
                    </tr>
                    <tr>
                        <th>Opened</th>
                        <td><?php echo $open_count;?> (<?php echo round($open_count / $sent_count * 100.0, 2);?>%)</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    
    <tr>
        <th><label for="bogoblast_business_id">Business</label></th>
        <td>
            <select name="bogoblast_business_id" id="bogoblast_business_id">
                <option value="0"></option>
                <?php if(is_array($bogoblast_businesses) && sizeof($bogoblast_businesses) > 0):
                    foreach($bogoblast_businesses as $bogoblast_business): ?>
                        <option value="<?php echo $bogoblast_business->ID ?>"<?php
                            if($bogoblast_business_id == $bogoblast_business->ID) echo ' selected="selected"';
                        ?>><?php echo $bogoblast_business->post_title; ?></option>
                    <?php endforeach;
                endif; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_promotion_email_template">E-Mail Template</label></th>
        <td><select name="bogoblast_promotion_email_template" id="bogoblast_promotion_email_template">
            <option value="0"></option>
            <?php foreach($email_templates as $label => $value): ?>
                <option value="<?php echo $value ?>"<?php
                    if($promotion_email_template == $value) echo ' selected="selected"';
                ?>><?php echo $label; ?></option>
            <?php endforeach;  ?>
        </select></td>
    </tr>
    <tr>
        <th><label for="bogoblast_promotion_email_subject">Email Subject</label></th>
        <td>
            <input type="text" name="bogoblast_promotion_email_subject" id="bogoblast_promotion_email_subject" value="<?php echo $promotion_email_subject; ?>" class="regular-text" />
        </td>
    </tr>
    <tr>
        <th><label>Expiration Date</label></th>
        <td>
            <select name="bogoblast_promotion_expiration_month" id="bogoblast_promotion_expiration_month">
                <?php for($month_int = 1; $month_int <= 12; $month_int++) :
                    $month_label = date("F", mktime(0, 0, 0, $month_int, 1, 2012));
                    ?><option value="<?php echo $month_int; ?>"<?php
                        if($month_int == $promotion_expiration_info['mon']) echo ' selected="selected"';
                    ?>><?php echo $month_int; ?> - <?php echo $month_label; ?></option>
                <?php endfor; ?>
            </select>
            <input type="text" name="bogoblast_promotion_expiration_day" class="small-text"
                id="bogoblast_promotion_expiration_day" value="<?php echo $promotion_expiration_info['mday']; ?>"/>
            <input type="text" name="bogoblast_promotion_expiration_year" class="small-text"
                id="bogoblast_promotion_expiration_year" value="<?php echo $promotion_expiration_info['year']; ?>"/>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_promotion_fine_print">Fine Print</label></th>
        <td><textarea name="bogoblast_promotion_fine_print" id="bogoblast_promotion_fine_print"
            class="widefat" style="width: 99%; height: 100px;"><?php
            echo $promotion_fine_print;
        ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="bogoblast_promotion_send_test_to">Send Test</label></th>
        <td>
            <?php
                global $current_user;
                get_currentuserinfo();
            ?>
            <input type="text" name="bogoblast_promotion_send_test_to" id="bogoblast_promotion_send_test_to" value="<?php echo $current_user->user_email; ?>" class="regular-text" />
            
            <a href="#" id="send_test" class="button">Send Test</a>
        </td>
    </tr>
    <tr>
        <th><label>Send on Schedule</label></th>
        <td>
            <select name="bogoblast_promotion_send_on_schedule_month" id="bogoblast_promotion_send_on_schedule_month">
                <?php for($month_int = 1; $month_int <= 12; $month_int++) :
                    $month_label = date("F", mktime(0, 0, 0, $month_int, 1, 2012));
                    ?><option value="<?php echo $month_int; ?>"<?php
                        if($month_int == $promotion_send_on_schedule_date_info['mon']) echo ' selected="selected"';
                    ?>><?php echo $month_int; ?> - <?php echo $month_label; ?></option>
                <?php endfor; ?>
            </select>
            <input type="text" name="bogoblast_promotion_send_on_schedule_day" class="small-text"
                id="bogoblast_promotion_send_on_schedule_day" value="<?php echo $promotion_send_on_schedule_date_info['mday']; ?>"/>
            <input type="text" name="bogoblast_promotion_send_on_schedule_year" class="small-text"
                id="bogoblast_promotion_send_on_schedule_year" value="<?php echo $promotion_send_on_schedule_date_info['year']; ?>"/>
            <a href="#" id="send_on_schedule" class="button">Schedule Send</a>
        </td>
    </tr>
    <tr>
        <th><label>Send Now</label></th>
        <td>
            <a href="#" id="send_now" class="button">Send Now</a>
        </td>
    </tr>
    <tr>
        <th></th>
        <td>
            <div class="send_queue_result">
                <div class="send_queue_result_stats">
                    <table>
                        <tbody>
                            <tr>
                                <th>Message Count</th>
                                <td class="send_queue_subscriber_count"></td>
                            </tr>
                            <tr>
                                <th>Messages Queued</th>
                                <td class="send_queue_insert_count"></td>
                            </tr>
                            <tr>
                                <th>Percent Complete</th>
                                <td class="send_queue_insert_percent"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <ul class="send_queue_error_messages"></ul>
                <a href="#" class="button send_queue_result_show_debug">Show Debug Messages</a>
                <div class="send_queue_result_debug"></div>
            </div>
        </td>
    </tr>
    <tr>
        <th><label>Tweet This Promotion</label></th>
        <td>
            <a href="#" id="tweet_now" class="button">Tweet Now</a>
            <div id="tweet_result" class="send_queue_result">
                <ul class="send_queue_status_messages"></ul>
                <ul class="send_queue_error_messages"></ul>
                <a href="#" class="button send_queue_result_show_debug">Show Debug Messages</a>
                <div class="send_queue_result_debug"></div>
            </div>
        </td>
    </tr>
</table>