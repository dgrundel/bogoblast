<?php
    register_post_type(
        "bogoblast_subscriber",
        array(
            "labels" => array(
                "name" => __( "Subscribers" ),
                "singular_name" => __( "Subscriber" )
            ),
            "public" => false,
            "has_archive" => false,
            "show_ui" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "rewrite" => false,
            "menu_icon" => BOGOBLAST_URL.'img/user.png',
            "supports" => array('title')
        )
    );
    remove_post_type_support('bogoblast_subscriber', 'editor');
    
    register_post_type(
        "bogoblast_business",
        array(
            "labels" => array(
                "name" => __( "Businesses" ),
                "singular_name" => __( "Business" )
            ),
            "public" => true,
            "has_archive" => true,
            "show_ui" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "rewrite" => array('slug' => 'businesses'),
            "menu_icon" => BOGOBLAST_URL.'img/building.png',
            "supports" => array('title', 'thumbnail', 'editor', 'comments', 'author')
        )
    );
    //remove_post_type_support('bogoblast_business', 'editor');
    
    register_post_type(
        "bogoblast_promotion",
        array(
            "labels" => array(
                "name" => __( "Promotions" ),
                "singular_name" => __( "Promotion" )
            ),
            "public" => false,
            "has_archive" => false,
            "show_ui" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "rewrite" => false,
            "menu_icon" => BOGOBLAST_URL.'img/tag_green.png',
            "supports" => array('title', 'editor', 'revisions', 'author')
        )
    );
    
    register_post_type(
        "bogoblast_event_log",
        array(
            "labels" => array(
                "name" => __( "Event Log" ),
                "singular_name" => __( "Event Log" )
            ),
            "public" => false,
            "has_archive" => false,
            "show_ui" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "rewrite" => false,
            "menu_icon" => BOGOBLAST_URL.'img/page_gear.png',
            "supports" => array('title', 'editor')
        )
    );
    
    register_post_type(
        "bogoblast_mail",
        array(
            "labels" => array(
                "name" => __( "Mail" ),
                "singular_name" => __( "Mail" )
            ),
            "public" => false,
            "has_archive" => false,
            "show_ui" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "rewrite" => false,
            "menu_icon" => BOGOBLAST_URL.'img/email.png',
            "supports" => array()
        )
    );
    remove_post_type_support('bogoblast_mail', 'editor');
    remove_post_type_support('bogoblast_mail', 'title');
?>