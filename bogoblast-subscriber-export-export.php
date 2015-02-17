<?php
    $bogoblast_business_id = intval($_POST['bogoblast_business_id']);

    $subscriber_query = array(
        'numberposts' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
        'meta_key' => '_bogoblast_business_id',
        'meta_query' => array(
            array(
                'key'=>'_bogoblast_business_id',
                'value'=> $bogoblast_business_id,
                'compare' => '='
            )
        ),
        'post_type' => 'bogoblast_subscriber');
    $bogoblast_subscribers = get_posts($subscriber_query);
?>
<div class="bogoblast_subscriber_import_wrapper wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Subscriber Export &raquo; Export</h2>

    <?php if(sizeof($error_messages) > 0): ?>
        <ul class="import_error_messages">
            <?php foreach($error_messages as $message):?>
                <li><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form enctype="multipart/form-data" method="post" action="<?php echo get_admin_url().'edit.php?post_type=bogoblast_subscriber&page=bogoblast-subscriber-import&action=result'; ?>">
        <table class="wp-list-table widefat fixed pages" cellspacing="0">
            <thead>
                <tr>
                    <th>E-Mail Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if(is_array($bogoblast_subscribers) && sizeof($bogoblast_subscribers) >= 0):
                        foreach($bogoblast_subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo $subscriber->post_title; ?></td>
                            </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </form>
</div>