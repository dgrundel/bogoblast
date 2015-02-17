<?php
    register_post_status('expired', array(
        'label' => 'Expired',
        'public' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => false,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' ),
    ));
?>