<?php
    $term_name = 'bogoblast_city';
    extract($args); 
    echo "$before_widget";
    echo "$before_title Cities $after_title";
    $terms = get_terms($term_name);
    $term_slug = get_query_var($term_name);
    
    if(sizeof($terms) > 0):
        echo '<ul>';
        
        foreach($terms as $term):
?>
    <li id="menu-item-<?php echo $term->term_id; ?>" class="menu-item menu-item-<?php echo $term->term_id; ?> <?php if($term->slug == $term_slug) echo 'current-menu-item'; ?>"><a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a></li>
<?php
        endforeach;
        
        echo '</ul>';
    endif;
    echo $after_widget;
?>