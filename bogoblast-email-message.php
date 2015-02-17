<?php
    class BogoblastEMailMessage {
        private $from;
        private $recipients;
        private $headers;
        private $attachments;
        private $charset;
        private $content_type;
        private $subject;
        private $body;
        private $response_body;
        private $send_status;
        private $undeliverable;
        private $post_id;
        
        private $scheduled_send_date;
        private $sent_date;
        
        private $trackables;
        
        //these are now trackables
        //public $opened;
        //public $clicked;
        //public $bounced;
        //public $spammed;
        //public $unsubscribed;
        
        public function __construct($post_id = null) {
            
            //set from name and address
            $from_address = get_option('bogoblast__send_mail_from_address', '');
            $from_name = get_option('bogoblast__send_mail_from_name', '');
            if(strlen($from_address) == 0) {
                // Get the site domain and get rid of www.
                $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                    $sitename = substr( $sitename, 4 );
                }
                $from_address = "wordpress@{$sitename}";
            }
            if(strlen($from_name) == 0) $from_name = 'WordPress';
            $this->set_from($from_address, $from_name);
            
            //set charset
            $this->set_charset(apply_filters( 'wp_mail_charset', get_bloginfo('charset') ));
            
            //set content type
            $content_type = 'text/plain';
            $content_type = apply_filters( 'wp_mail_content_type', $content_type );
            $this->set_content_type($content_type);
            
            //set send_status
            $this->send_status = false;
            
            //set undeliverable status
            $this->undeliverable = false;
            
            //set scheduled send date to now
            $this->set_scheduled_send_date(time()-1);
            
            if($post_id !== null) $this->load($post_id);
        }
        
        public static function validate_address($address) {
            //validate email address
            $email_regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
            return preg_match($email_regex, $sanitized_address) == 0 ? false : true;
        }
        
        public function set_from($address = null, $name = null) {
            
            if(!is_array($this->from)) $this->from = array();
            
            if($address !== null) {
                //not really sanitized. TODO later.
                $sanitized_address = trim($address);
                $this->from['address'] = $sanitized_address;
            }
            
            if($name !== null) {
                //not really sanitized. TODO later.
                $sanitized_name = trim($name);
                $this->from['name'] = $sanitized_name;
            }
        }
        
        public function get_from($part = null) {
            
            if(!is_array($this->from)) return null;
            
            if($part === null) {
                if(strlen($this->from['name']))
                    return $this->from['name'].' <'.$this->from['address'].'>';
                else
                    return $this->from['address'];
            } else {
                switch($part) {
                    case 'address':
                    case 'name':
                        return $this->from[$part];
                        break;
                    default:
                        throw new Exception("'{$part}' is not a valid from part.");
                }
            }
            
        }
        
        public function add_recipient($type = 'to', $address, $name = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    break;
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
            
            //initialize the arrays if need be.
            if(!is_array($this->recipients)) $this->recipients = array();
            if(!is_array($this->recipients[$type])) $this->recipients[$type] = array();
            
            //not really sanitized. TODO later.
            $sanitized_name = trim($name);
            $sanitized_address = trim($address);
            
            $this->recipients[$type][] = array(
                'address' => $sanitized_address,
                'name' => $sanitized_name);
        }
        
        public function add_to($address, $name = null) {
            $this->add_recipient('to', $address, $name);
        }
        public function add_cc($address, $name = null) {
            $this->add_recipient('cc', $address, $name);
        }
        public function add_bcc($address, $name = null) {
            $this->add_recipient('bcc', $address, $name);
        }
        
        public function get_recipients($type = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    return is_array($this->recipients[$type]) ? $this->recipients[$type] : array();
                    break;
                
                case null:
                    return is_array($this->recipients) ? $this->recipients : array();
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
        }
        public function get_to() {
            return $this->get_recipients('to');
        }
        public function get_cc() {
            return $this->get_recipients('cc');
        }
        public function get_bcc() {
            return $this->get_recipients('bcc');
        }
        
        public function get_recipients_as_strings($type) {
            
            $to_return = array();
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    if(is_array($this->recipients[$type])) {
                        foreach($this->recipients[$type] as $recipient) {
                            if(strlen($recipient['name']) > 0)
                                $to_return[] = $recipient['name'].' <'.$recipient['address'].'>';
                            else
                                $to_return[] = $recipient['address'];
                        }
                        return $to_return;
                    } else {
                        return array();
                    }
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
        }
        public function get_to_as_strings() {
            return $this->get_recipients_as_strings('to');
        }
        public function get_cc_as_strings() {
            return $this->get_recipients_as_strings('cc');
        }
        public function get_bcc_as_strings() {
            return $this->get_recipients_as_strings('bcc');
        }
        
        public function recipient_exists($address, $type = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    foreach($this->recipients[$type] as $key => $value) {
                        if($key == 'address' && $value == $address) return true;
                    }
                    break;
                
                case null:
                    foreach($this->recipients as $type => $recipients) {
                        foreach($recipients as $key => $value) {
                            if($key == 'address' && $value == $address) return true;
                        }
                    }
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
            
            return false;
        }
        
        public function add_header($line) {
            //initialize the array if need be.
            if(!is_array($this->headers)) $this->headers = array();
            
            $this->headers[] = $line;
        }
        
        public function get_headers() {
            return is_array($this->headers) ? $this->headers : array();
        }
        
        public function add_attachment($attachment) {
            //initialize the array if need be.
            if(!is_array($this->attachments)) $this->attachments = array();
            
            $this->attachments[] = $attachment;
        }
        
        public function get_attachments() {
            return is_array($this->attachments) ? $this->attachments : array();
        }
        
        public function set_charset($charset) {
            $this->charset = $charset;
        }
        
        public function get_charset() {
            return $this->charset;
        }
        
        public function set_content_type($content_type) {
            $this->content_type = $content_type;
        }
        
        public function get_content_type() {
            return $this->content_type;
        }
        
        public function set_subject($subject) {
            $this->subject = $subject;
        }
        
        public function get_subject() {
            return $this->subject;
        }
        
        public function set_body($content) {
            $this->body = $content;
            //if($html) $this->add_header("Content-type: text/html; charset={$this->charset}");
        }
        
        public function get_body() {
            return $this->body;
        }
        
        public function set_scheduled_send_date($send_date) {
            $date = intval($send_date);
            if($date > 0) {
                $this->scheduled_send_date = intval($send_date);
                return true;
            } else {
                return false;
            }
        }
        
        public function get_scheduled_send_date() {
            return $this->scheduled_send_date;
        }
        
        public function get_sent_date() {
            return $this->sent_date;
        }
        
        public function get_response_body() {
            return $this->response_body;
        }
        
        public function set_trackable($key, $value = 1) {
            //initialize the array if need be.
            if(!is_array($this->trackables)) $this->trackables = array();
            
            $this->trackables[$key] = $value;
        }
        
        public function get_trackable($key) {
            if(is_array($this->trackables) && isset($this->trackables[$key])) return $this->trackables[$key];
            return null;
        }
        
        public function get_all_trackables() {
            return is_array($this->trackables) ? $this->trackables : array();
        }
        
        public function send_via_ses() {
            
            $this->pre_send_actions();
            
            require_once(BOGOBLAST_DIR.'lib/ses.php');
            
            $access_key = get_option('bogoblast__ses_access_key', '');
            $secret_key = get_option('bogoblast__ses_secret_key', '');
            
            if(strlen($access_key) == 0)
                throw new Exception("SES Access Key not set.");
            if(strlen($secret_key) == 0)
                throw new Exception("SES Secret Key not set.");
            
            $ses = new SimpleEmailService($access_key, $secret_key);
            
            $message = new SimpleEmailServiceMessage();
            
            $message->setFrom($this->get_from());
            
            foreach($this->get_to_as_strings() as $recipient) {
                $message->addTo($recipient);
            }
            foreach($this->get_cc_as_strings() as $recipient) {
                $message->addCC($recipient);
            }
            foreach($this->get_bcc_as_strings() as $recipient) {
                $message->addBCC($recipient);
            }
            
            $message->setSubjectCharset($this->get_charset());
            $message->setSubject($this->get_subject());
            
            $message->setMessageCharset($this->get_charset());
            
            if($this->get_content_type() == 'text/html')
                $message->setMessageFromString(null, $this->get_body());
            else
                $message->setMessageFromString($this->get_body(), null);
            
            set_error_handler(array(&$this, 'error_handler'));
            try {
                $ses_response = $ses->sendEmail($message);
            } catch(Exception $e) {
                $this->undeliverable = true;
                $ses_response = $e;
            }
            restore_error_handler();
            
            $this->response_body = serialize($ses_response);
            
            $this->send_status = is_array($ses_response);
            
            $this->post_send_actions();
            
            return $this->send_status;
        }
        
        public function send_via_phpmailer() {
            
            $this->pre_send_actions();
            
            global $phpmailer;
            
            // (Re)create it, if it's gone missing
            if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $phpmailer = new PHPMailer( true );
            }
            
            // Empty out the values that may be set
            $phpmailer->ClearAddresses();
            $phpmailer->ClearAllRecipients();
            $phpmailer->ClearAttachments();
            $phpmailer->ClearBCCs();
            $phpmailer->ClearCCs();
            $phpmailer->ClearCustomHeaders();
            $phpmailer->ClearReplyTos();
            
            // Plugin authors can override the potentially troublesome default
            $phpmailer->From = $this->get_from('address');
            $phpmailer->FromName = $this->get_from('name');
            
            foreach($this->get_to() as $recipient) {
                $phpmailer->AddAddress($recipient['address'], $recipient['name']);
            }
            
            foreach($this->get_cc() as $recipient) {
                $phpmailer->AddCc($recipient['address'], $recipient['name']);
            }
            
            foreach($this->get_cc() as $recipient) {
                $phpmailer->AddBcc($recipient['address'], $recipient['name']);
            }
            
            $phpmailer->Subject = $this->get_subject();
            
            // Set to use PHP's mail()
            $phpmailer->IsMail();
            
            //Set content_type
            $content_type = $this->get_content_type();
            $phpmailer->ContentType = $content_type;
            
            // Set whether it's plaintext, depending on $content_type
            if ( 'text/html' == $content_type ) {
                $phpmailer->IsHTML( true );
            }
            
            $phpmailer->Body = $this->get_body();
            $phpmailer->CharSet = $this->get_charset();
            
            foreach($this->get_headers() as $header_line) {
                $phpmailer->AddCustomHeader($header_line);
            }
            
            foreach ( $this->get_attachments() as $attachment ) {
                try {
                    $phpmailer->AddAttachment($attachment);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
            
            do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );
            
            try {
                $phpmailer->Send();
            } catch ( phpmailerException $e ) {
                $this->send_status = false;
            }
            $this->send_status = true;
            
            $this->post_send_actions();
            
            return $this->send_status;
        }
        
        public function send($force = false) {
            
            $pause_sending = intval(get_option('bogoblast__pause_sending', 0));
            if($pause_sending) return false;

            //make sure we haven't sent already
            if($this->send_status && !$force) return false;
            
            $send_via_ses = intval(get_option('bogoblast__enable_ses', 0));
            if($send_via_ses)
                return $this->send_via_ses();
            else
                return $this->send_via_phpmailer();
        }
        
        public function save() {
            
            if(isset($this->post_id) && intval($this->post_id) > 0) {
                $new_post = get_post($this->post_id, 'ARRAY_A');
            }
            
            //make sure we're at least grabbing something that's the right post type
            if(!is_array($new_post) || $new_post['post_type'] != 'bogoblast_mail') {
                $this->post_id = 0;
                $new_post = array();
            }
            
            $new_post['post_type'] = 'bogoblast_mail';
            $new_post['post_title'] = $this->get_subject();
            $new_post['post_content'] = $this->get_body();
            $new_post['post_status'] = ($this->send_status) ? 'publish' : 'draft';
            if($this->undeliverable) $new_post['post_status'] = 'pending';
            
            $new_post_meta = array();
            
            //for reloading
            $new_post_meta['__from'] = serialize($this->from);
            $new_post_meta['__recipients'] = serialize($this->recipients);
            $new_post_meta['__headers'] = serialize($this->headers);
            $new_post_meta['__attachments'] = serialize($this->attachments);
            $new_post_meta['__charset'] = serialize($this->charset);
            $new_post_meta['__response_body'] = serialize($this->response_body);
            $new_post_meta['__send_status'] = serialize($this->send_status);
            $new_post_meta['__trackables'] = serialize($this->trackables);
            
            //$new_post_meta['__content_type'] = serialize($this->content_type);
            //$new_post_meta['__subject'] = serialize($this->subject);
            //$new_post_meta['__body'] = serialize($this->body);
            //$new_post_meta['__undeliverable'] = serialize($this->undeliverable);
            //$new_post_meta['__scheduled_send_date'] = serialize($this->scheduled_send_date);
            //$new_post_meta['__sent_date'] = serialize($this->sent_date);
            
            //for display
            $new_post_meta['_to'] = implode(',', $this->get_to_as_strings());
            $new_post_meta['_cc'] = implode(',', $this->get_cc_as_strings());
            $new_post_meta['_bcc'] = implode(',', $this->get_bcc_as_strings());
            $new_post_meta['_created'] = time();
            $new_post_meta['_content_type'] = $this->get_content_type();
            $new_post_meta['_subject'] = $this->get_subject();
            $new_post_meta['_body'] = $this->get_body();
            
            //for stats and tracking
            $new_post_meta['_scheduled_send_date'] = $this->scheduled_send_date;
            $new_post_meta['_sent_date'] = $this->sent_date;
            $new_post_meta['_undeliverable'] = $this->undeliverable ? 1 : 0;
            if(is_array($this->trackables)) {
                foreach($this->trackables as $key => $value) {
                    $new_post_meta['_trackable_'.$key] = $value;
                }
            }
            
            if(isset($this->post_id) && intval($this->post_id) > 0) {
                $new_post_id = wp_update_post($new_post);
            } else {
                $new_post_id = wp_insert_post($new_post, true);
            }
            
            if(!is_wp_error($new_post_id) && $new_post_id > 0) {
                
                $this->post_id = $new_post_id;
                
                //set post_meta on inserted post
                foreach($new_post_meta as $meta_key => $meta_value) {
                    add_post_meta($new_post_id, $meta_key, $meta_value, true) or
                        update_post_meta($new_post_id, $meta_key, $meta_value);
                }
                
                return $new_post_id;
            }
            
            return 0;
        }
        
        public function load($post_id) {
            
            if(get_post_type($post_id) != 'bogoblast_mail') return false;
            
            $this->post_id = $post_id;
            
            $this->from = unserialize(get_post_meta($post_id, '__from', true));
            $this->recipients = unserialize(get_post_meta($post_id, '__recipients', true));
            $this->headers = unserialize(get_post_meta($post_id, '__headers', true));
            $this->attachments = unserialize(get_post_meta($post_id, '__attachments', true));
            $this->charset = unserialize(get_post_meta($post_id, '__charset', true));
            $this->response_body = unserialize(get_post_meta($post_id, '__response_body', true));
            $this->send_status = unserialize(get_post_meta($post_id, '__send_status', true));
            $this->trackables = unserialize(get_post_meta($post_id, '__trackables', true));
            
            $this->content_type = get_post_meta($post_id, '_content_type', true);
            $this->subject = get_post_meta($post_id, '_subject', true);
            $this->body = get_post_meta($post_id, '_body', true);
            
            $this->undeliverable = !!intval(get_post_meta($post_id, '_undeliverable', true));
            $this->scheduled_send_date = intval(get_post_meta($post_id, '_scheduled_send_date', true));
            $this->sent_date = intval(get_post_meta($post_id, '_sent_date', true));
            
            //$new_post_meta['__content_type'] = serialize($this->content_type);
            //$new_post_meta['__subject'] = serialize($this->subject);
            //$new_post_meta['__body'] = serialize($this->body);
            //$new_post_meta['__undeliverable'] = serialize($this->undeliverable);
            //$new_post_meta['__scheduled_send_date'] = serialize($this->scheduled_send_date);
            //$new_post_meta['__sent_date'] = serialize($this->sent_date);
            
            return true;
        }
        
        public function reload() {
            $post_id = intval($this->post_id);
            if($post_id > 0) $this->load($post_id);
        }
        
        public function wp_mail( $to, $subject, $message, $headers = '', $attachments = array(), $really_send = true ) {
            
            // Compact the input, apply the filters, and extract them back out
            extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );
        
            if ( !is_array($attachments) )
                $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
            
            //$bogoblast_email_message = new BogoblastEMailMessage();
            
            // Headers
            if ( empty( $headers ) ) {
                $headers = array();
            } else {
                if ( !is_array( $headers ) ) {
                    // Explode the headers out, so this function can take both
                    // string headers and an array of headers.
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
                } else {
                    $tempheaders = $headers;
                }
                $headers = array();
                $cc = array();
                $bcc = array();
        
                // If it's actually got contents
                if ( !empty( $tempheaders ) ) {
                    // Iterate through the raw headers
                    foreach ( (array) $tempheaders as $header ) {
                        if ( strpos($header, ':') === false ) {
                            if ( false !== stripos( $header, 'boundary=' ) ) {
                                $parts = preg_split('/boundary=/i', trim( $header ) );
                                $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                            }
                            continue;
                        }
                        // Explode them out
                        list( $name, $content ) = explode( ':', trim( $header ), 2 );
        
                        // Cleanup crew
                        $name    = trim( $name    );
                        $content = trim( $content );
        
                        switch ( strtolower( $name ) ) {
                            // Mainly for legacy -- process a From: header if it's there
                            case 'from':
                                if ( strpos($content, '<' ) !== false ) {
                                    // So... making my life hard again?
                                    $from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
                                    $from_name = str_replace( '"', '', $from_name );
                                    $from_name = trim( $from_name );
        
                                    $from_email = substr( $content, strpos( $content, '<' ) + 1 );
                                    $from_email = str_replace( '>', '', $from_email );
                                    $from_email = trim( $from_email );
                                } else {
                                    $from_email = trim( $content );
                                }
                                break;
                            case 'content-type':
                                if ( strpos( $content, ';' ) !== false ) {
                                    list( $type, $charset ) = explode( ';', $content );
                                    $content_type = trim( $type );
                                    if ( false !== stripos( $charset, 'charset=' ) ) {
                                        $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
                                    } elseif ( false !== stripos( $charset, 'boundary=' ) ) {
                                        $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
                                        $charset = '';
                                    }
                                } else {
                                    $content_type = trim( $content );
                                }
                                break;
                            case 'cc':
                                $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                break;
                            case 'bcc':
                                $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                break;
                            default:
                                // Add it to our grand headers array
                                $headers[trim( $name )] = trim( $content );
                                break;
                        }
                    }
                }
            }
        
            if ( isset( $from_name ) )
                $this->set_from(null, apply_filters('wp_mail_from_name', $from_name));
        
            if ( isset( $from_email ) )
                $this->set_from(apply_filters('wp_mail_from_email', $from_email), null);
            
            // Set destination addresses
            if ( !is_array( $to ) )
                $to = explode( ',', $to );
        
            foreach ( (array) $to as $recipient ) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                        if ( count( $matches ) == 3 ) {
                            $recipient_name = $matches[1];
                            $recipient = $matches[2];
                        }
                    }
                    $this->add_to($recipient, $recipient_name);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
        
            // Set mail's subject and body
            $this->set_subject($subject);
            $this->set_body($message, $message);
            
            // Add any CC and BCC recipients
            if ( !empty( $cc ) ) {
                foreach ( (array) $cc as $recipient ) {
                    try {
                        // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                        $recipient_name = '';
                        if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                            if ( count( $matches ) == 3 ) {
                                $recipient_name = $matches[1];
                                $recipient = $matches[2];
                            }
                        }
                        $this->add_cc($recipient, $recipient_name);
                    } catch ( phpmailerException $e ) {
                        continue;
                    }
                }
            }
        
            if ( !empty( $bcc ) ) {
                foreach ( (array) $bcc as $recipient) {
                    try {
                        // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                        $recipient_name = '';
                        if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                            if ( count( $matches ) == 3 ) {
                                $recipient_name = $matches[1];
                                $recipient = $matches[2];
                            }
                        }
                        $this->add_bcc($recipient, $recipient_name);
                    } catch ( phpmailerException $e ) {
                        continue;
                    }
                }
            }
        
            // Set Content-Type and charset
            // If we don't have a content-type from the input headers
            if ( !isset( $content_type ) )
                $content_type = 'text/plain';
            
            $content_type = apply_filters( 'wp_mail_content_type', $content_type );
            $this->set_content_type($content_type);
            
            // Set the content-type and charset
            if ( isset( $charset ) )
                $this->set_charset(apply_filters( 'wp_mail_charset', $charset ));
        
            // Set custom headers
            if ( !empty( $headers ) ) {
                foreach( (array) $headers as $name => $content ) {
                    $this->add_header(sprintf( '%1$s: %2$s', $name, $content ));
                }
        
                if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
                    $this->add_header(sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ));
            }
        
            if ( !empty( $attachments ) ) {
                foreach ( $attachments as $attachment ) {
                    $this->add_attachment($attachment);
                }
            }
            
            if($really_send) {
                return $this->send();
            }
        }
        
        private function pre_send_actions() {
            
            
            if(intval($this->post_id) > 0 && $this->get_content_type() == 'text/html') {
                
                //message tracking goodness
                $tracking_params = array(
                    'bogoblast_mail_id' => $this->post_id,
                    'bogoblast_mail_set_trackable' => 'opened',
                );
                if(is_array($this->trackables)) {
                    foreach($this->trackables as $key => $value) {
                        $tracking_params[$key] = $value;
                    }
                }
                $tracking_url = BogoblastUtil::set_url_query_value(get_site_url(), array_keys($tracking_params), array_values($tracking_params));
                
                //do something here to replace all link hrefs and img srcs with trackable urls
                
                //append transparent tracking image to end of message
                $tracking_code = "<img src=\"{$tracking_url}\" alt=\"\">";
                $this->set_body($this->get_body().$tracking_code);
            }
            
        }
        
        private function post_send_actions() {
            if($this->send_status) $this->sent_date = time();
            $this->save();
        }
        
        //Error Handler
        public function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }
?>