<div class="bogoblast_business_subscribe">
    <h2>Get Updates from <?php the_title(); ?></h2>
    
    <?php
    
    $new_subscriber_added = false;
    if(isset($_REQUEST['bogoblast_new_subscriber_email']) && strlen($_REQUEST['bogoblast_new_subscriber_email']) > 0) {
        $new_subscriber_email = $_REQUEST['bogoblast_new_subscriber_email'];
        $new_subscriber_added = Bogoblast::add_subscriber_to(get_the_ID(), $new_subscriber_email);
    }
    
    $method = isset($_REQUEST['subscribe_method']) ? $_REQUEST['subscribe_method'] : 'unknown';
    
    if($new_subscriber_added):
        
        $message_to = get_option('admin_email');
        $message_subject = "New Subscriber to ".get_the_title()." from Website";
        $message_content = "Hello,

{$new_subscriber_email} just subscribed to ".get_the_title()." via {$method} !
Pretty sweet, eh?

Your Friends at Bogoblast";
        
        $bogoblast_email_message = new BogoblastEMailMessage();
        $bogoblast_email_message->wp_mail($message_to, $message_subject, $message_content);
        $bogoblast_email_message->save();
    ?>
        <p>Thanks for subscribing!<p>
        <p>You'll start to receive offers from <?php the_title(); ?>
            in the next couple of weeks.</p>
    <?php else: ?>
        <form method="post">
            <div class="form_field">
                <label for="bogoblast_new_subscriber_email">Your E-Mail Address</label>
                <input type="text" class="input_text" name="bogoblast_new_subscriber_email" id="bogoblast_new_subscriber_email">
            </div>
            <div class="form_footer">
                <input type="hidden" name="subscribe_method" value="shortcode">
                <button name="submit" type="submit" id="submit">Subscribe</button>
            </div>
        </form>
    <?php endif; ?>
</div>