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
    <h2>Business Merge &raquo; Results</h2>
    
    <ul class="import_error_messages">
    </ul>
    
    <table id="inserted_rows" class="wp-list-table widefat fixed pages" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 30px;"></th>
                <th style="width: 80px;">CSV Row</th>
                <th style="width: 80px;">Subscriber ID</th>
                <th>E-Mail Address</th>
                <th>Businesses</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody><!-- rows inserted via AJAX --></tbody>
    </table>
    
    <p><a id="show_debug" href="#" class="button">Show Raw AJAX Responses</a></p>
    <div id="debug"><!-- server responses get logged here --></div>
</div>