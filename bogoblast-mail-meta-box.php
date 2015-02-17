<?php global $post; ?>
<style>
    .bogoblast_mail {
        font-size: 13px;
        line-height: 16px;
        color: #333;
    }
    .bogoblast_mail_envelope table { width: 100%; }
    .bogoblast_mail_envelope th,
    .bogoblast_mail_envelope td {
        padding: 4px;
        font-size: 13px;
        line-height: 16px;
        color: #333;
    }
    .bogoblast_mail_envelope th {
        width: 10%;
        text-align: right;
    }
    .bogoblast_mail_envelope td {
        text-align: left;
        font-family: monospace;
    }
    .bogoblast_mail .body {
        background-color: #fff;
        border: 1px solid #efefef;
        padding: 10px 4px;
        margin: 10px 0;
        text-align: left;
        font-size: medium;
        line-height: normal;
    }
    .bogoblast_mail .body.other { font-family: monospace; }
</style>
<div class="bogoblast_mail">
    <table class="bogoblast_mail_envelope">
        <thead>
            <tr>
                <th>To</th>
                <td><?php echo get_post_meta($post->ID, '_to', true); ?></td>
            </tr>
            <tr>
                <th>CC</th>
                <td><?php echo get_post_meta($post->ID, '_cc', true); ?></td>
            </tr>
            <tr>
                <th>BCC</th>
                <td><?php echo get_post_meta($post->ID, '_bcc', true); ?></td>
            </tr>
            <tr>
                <th>Subject</th>
                <td><?php echo get_post_meta($post->ID, '_subject', true); ?></td>
            </tr>
        </thead>
    </table><?php
    
    $content_type = get_post_meta($post->ID, '_content_type', true);
    
    if($content_type == 'text/html') {
        echo '<div class="body html">';
        
        $message_content = get_post_meta($post->ID, '_body', true);
        
        //remove head tag with contents.
        //remove html tags, leaving contents.
        $message_content = preg_replace(
            array('@<head[^>]*?>.*?</head>@siu', '/<html>/', '/<\/html>/', '/<body[^>]*?>/', '/<\/body>/'),
            array('', '', '', '', ''),
            $message_content);
        
        echo $message_content;
        
        echo '</div>';
    } else {
        echo '<div class="body other">'.nl2br(htmlspecialchars(get_post_meta($post->ID, '_body', true))).'</div>';
    }
    
    $response_body = get_post_meta($post->ID, '__response_body', true);
    if(strlen($response_body) > 0) {
        echo '<h4>Raw Response</h4>';
        echo '<pre>';
        var_dump(maybe_unserialize($response_body));
        echo '</pre>';
    } ?>
</div>