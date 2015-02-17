<?php
    register_taxonomy(
        "bogoblast_business_category",
        array("bogoblast_business"), 
        array(
            "hierarchical" => true, 
            "label" => "Business Categories", 
            "singular_label" => "Business Category", 
            "show_ui" => true,
            "show_in_nav_menus" => true,
            "rewrite" => array('slug' => 'categories')
        )
    );
    
    register_taxonomy(
        "bogoblast_city",
        array("bogoblast_business"), 
        array(
            "hierarchical" => true, 
            "label" => "Cities", 
            "singular_label" => "City", 
            "show_ui" => true,
            "show_in_nav_menus" => true,
            "rewrite" => array('slug' => 'cities')
        )
    );
?>