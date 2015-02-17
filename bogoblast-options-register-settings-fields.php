<?php
    //General
    add_settings_section('bogoblast_options_general_section', 'General Options', array(&$this, 'bogoblast_options_general_section_description_html'), 'bogoblast_options');
    
    register_setting('bogoblast_options', 'bogoblast__send_mail_from_name', 'trim');
    add_settings_field('bogoblast__send_mail_from_name', '<label for="bogoblast__send_mail_from_name">Send Mail From Name</label>' , array(&$this, 'bogoblast_options_send_mail_from_name_html') , 'bogoblast_options', 'bogoblast_options_general_section');
    
    register_setting('bogoblast_options', 'bogoblast__send_mail_from_address', 'trim');
    add_settings_field('bogoblast__send_mail_from_address', '<label for="bogoblast__send_mail_from_address">Send Mail From Address</label>' , array(&$this, 'bogoblast_options_send_mail_from_address_html') , 'bogoblast_options', 'bogoblast_options_general_section');
    
    register_setting('bogoblast_options', 'bogoblast__pause_sending', 'intval');
    add_settings_field('bogoblast__pause_sending', '<label for="bogoblast__pause_sending">Pause Sending</label>' , array(&$this, 'bogoblast_options_pause_sending_html') , 'bogoblast_options', 'bogoblast_options_general_section');

    //Amazon SES
    add_settings_section('bogoblast_options_ses_section', 'Amazon SES', array(&$this, 'bogoblast_options_ses_section_description_html'), 'bogoblast_options');
    
    register_setting('bogoblast_options', 'bogoblast__enable_ses', 'intval');
    add_settings_field('bogoblast__enable_ses', '<label for="bogoblast__enable_ses">Enable Amazon SES</label>' , array(&$this, 'bogoblast_options_enable_ses_html') , 'bogoblast_options', 'bogoblast_options_ses_section');
    
    register_setting('bogoblast_options', 'bogoblast__ses_access_key', 'trim');
    add_settings_field('bogoblast__ses_access_key', '<label for="bogoblast__ses_access_key">Access Key</label>' , array(&$this, 'bogoblast_options_ses_access_key_html') , 'bogoblast_options', 'bogoblast_options_ses_section');
    
    register_setting('bogoblast_options', 'bogoblast__ses_secret_key', 'trim');
    add_settings_field('bogoblast__ses_secret_key', '<label for="bogoblast__ses_secret_key">Secret Key</label>' , array(&$this, 'bogoblast_options_ses_secret_key_html') , 'bogoblast_options', 'bogoblast_options_ses_section');
    
    //Twitter
    add_settings_section('bogoblast_options_twitter_section', 'Twitter', array(&$this, 'bogoblast_options_twitter_section_description_html'), 'bogoblast_options');
    
    register_setting('bogoblast_options', 'bogoblast__twitter_consumer_key', 'trim');
    add_settings_field('bogoblast__twitter_consumer_key', '<label for="bogoblast__twitter_consumer_key">Consumer Key</label>' , array(&$this, 'bogoblast_options_twitter_consumer_key_html') , 'bogoblast_options', 'bogoblast_options_twitter_section');
    
    register_setting('bogoblast_options', 'bogoblast__twitter_consumer_secret', 'trim');
    add_settings_field('bogoblast__twitter_consumer_secret', '<label for="bogoblast__twitter_consumer_secret">Consumer Secret</label>' , array(&$this, 'bogoblast_options_twitter_consumer_secret_html') , 'bogoblast_options', 'bogoblast_options_twitter_section');
    
    register_setting('bogoblast_options', 'bogoblast__twitter_user_token', 'trim');
    add_settings_field('bogoblast__twitter_user_token', '<label for="bogoblast__twitter_user_token">User Token</label>' , array(&$this, 'bogoblast_options_twitter_user_token_html') , 'bogoblast_options', 'bogoblast_options_twitter_section');
    
    register_setting('bogoblast_options', 'bogoblast__twitter_user_secret', 'trim');
    add_settings_field('bogoblast__twitter_user_secret', '<label for="bogoblast__twitter_user_secret">User Secret</label>' , array(&$this, 'bogoblast_options_twitter_user_secret_html') , 'bogoblast_options', 'bogoblast_options_twitter_section');
?>