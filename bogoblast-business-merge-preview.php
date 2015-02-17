<?php
    $bogoblast_business_id = intval($_POST['bogoblast_business_id']);
    
    $bogoblast_business_merge_ids = array();
    if(is_array($_POST['bogoblast_business_merge_ids'])) {
        foreach($_POST['bogoblast_business_merge_ids'] as $id => $selected) {
            if(intval($selected) && intval($id) != $bogoblast_business_id) $bogoblast_business_merge_ids[] = intval($id);
        }
    }
?>
<div class="bogoblast_business_merge_wrapper wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Business Merge &raquo; Preview</h2>
    
    <?php if(sizeof($error_messages) > 0): ?>
        <ul class="import_error_messages">
            <?php foreach($error_messages as $message):?>
                <li><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <form enctype="multipart/form-data" method="post" action="<?php echo get_admin_url().'edit.php?post_type=bogoblast_business&page=bogoblast-business-merge&action=result'; ?>">
        <input type="hidden" name="bogoblast_business_id" value="<?php echo $bogoblast_business_id; ?>">
        <?php foreach($bogoblast_business_merge_ids as $bogoblast_business_merge_id): ?>
            <input type="hidden" name="bogoblast_business_merge_ids[<?php echo $bogoblast_business_merge_id; ?>]" value="1" />
        <?php endforeach; ?>
        <input type="hidden" name="limit" value="5">
        
        <table class="form-table">
            <tr>
                <th><label>Merging</label></th>
                <td>
                    <ul class="bogoblast_businesses"><?php
                        foreach($bogoblast_business_merge_ids as $bogoblast_business_merge_id): ?>
                            <li><?php echo get_the_title($bogoblast_business_merge_id); ?> (ID: <?php echo $bogoblast_business_merge_id; ?>), <?php echo Bogoblast::count_subscribers_for($bogoblast_business_merge_id); ?> Subscribers</li>
                        <?php endforeach;
                    ?></ul>
                </td>
            </tr>
            <tr>
                <th><label>Into</label></th>
                <td><?php echo get_the_title($bogoblast_business_id); ?> (ID: <?php echo $bogoblast_business_id; ?>), <?php echo Bogoblast::count_subscribers_for($bogoblast_business_id); ?> Subscribers</td>
            </tr>
            <tr>
                <th></th>
                <td><button class="button-primary" type="submit">Merge Businesses</button></td>
            </tr>
        </table>
    </form>
</div>