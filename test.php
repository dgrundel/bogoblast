<?php
    if ( !defined('ABSPATH') ) {
        /* Set up WordPress environment */
        require_once('../../../wp-load.php');
    }
    
    $v = isset($_REQUEST['v']) ? $_REQUEST['v'] : 0;
    $l = isset($_REQUEST['l']) ? $_REQUEST['l'] : 0;
    
    $to = 'daniel@bogoblast.com';
    $msg = 'This is a test message.';
    $subj = 'Test Message ['.$v.'] at '.time();
    
    //if($l) {
    //    $bogoblast_email_message = new BogoblastEMailMessage($l);
    //    var_dump($bogoblast_email_message);
    //    var_dump($bogoblast_email_message->send());
    //    var_dump($bogoblast_email_message->send(true));
    //    var_dump($bogoblast_email_message);
    //}
    
    $bogoblast_email_message = new BogoblastEMailMessage();
    $bogoblast_email_message->wp_mail($to, $subj, $msg, '', array(), false);
    var_dump($bogoblast_email_message->save());