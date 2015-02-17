<?php
    if ( !function_exists( 'wp_mail' ) ) :
        /**
         * Send mail, similar to PHP's mail
         *
         * A true return value does not automatically mean that the user received the
         * email successfully. It just only means that the method used was able to
         * process the request without any errors.
         *
         * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
         * creating a from address like 'Name <email@address.com>' when both are set. If
         * just 'wp_mail_from' is set, then just the email address will be used with no
         * name.
         *
         * The default content type is 'text/plain' which does not allow using HTML.
         * However, you can set the content type of the email by using the
         * 'wp_mail_content_type' filter.
         *
         * The default charset is based on the charset used on the blog. The charset can
         * be set using the 'wp_mail_charset' filter.
         *
         * @since 1.2.1
         * @uses apply_filters() Calls 'wp_mail' hook on an array of all of the parameters.
         * @uses apply_filters() Calls 'wp_mail_from' hook to get the from email address.
         * @uses apply_filters() Calls 'wp_mail_from_name' hook to get the from address name.
         * @uses apply_filters() Calls 'wp_mail_content_type' hook to get the email content type.
         * @uses apply_filters() Calls 'wp_mail_charset' hook to get the email charset
         * @uses do_action_ref_array() Calls 'phpmailer_init' hook on the reference to
         *		phpmailer object.
         * @uses PHPMailer
         *
         * @param string|array $to Array or comma-separated list of email addresses to send message.
         * @param string $subject Email subject
         * @param string $message Message contents
         * @param string|array $headers Optional. Additional headers.
         * @param string|array $attachments Optional. Files to attach.
         * @return bool Whether the email contents were sent successfully.
         */
        function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
            
            $bogoblast_email_message = new BogoblastEMailMessage();
            return $bogoblast_email_message->wp_mail($to, $subject, $message, $headers, $attachments);
        }
    endif;
?>