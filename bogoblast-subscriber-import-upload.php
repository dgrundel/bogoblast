<div class="bogoblast_subscriber_import_wrapper wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Subscriber Import &raquo; Upload</h2>
    
    <form enctype="multipart/form-data" method="post" action="<?php echo get_admin_url().'edit.php?post_type=bogoblast_subscriber&page=bogoblast-subscriber-import&action=preview'; ?>">
        <table class="form-table">
            <tr>
                <th><label for="import_csv">File to Import</label></th>
                <td><input type="file" name="import_csv"></td>
            </tr>
            <tr>
                <th><label for="header_row">First Row is Header Row</label></th>
                <td><input type="checkbox" name="header_row" id="header_row" value="1"></td>
            </tr>
            <tr>
                <th><label for="bogoblast_business_id">Import to Business</label></th>
                <td>
                    <?php
                        //$query_vars = array(
                        //    'numberposts' => -1,
                        //    'post_type' => 'bogoblast_business',
                        //    'orderby' => 'title',
                        //    'order' => 'ASC',
                        //    'post_status' => 'any');
                        //$bogoblast_businesses = get_posts($query_vars);
                        $bogoblast_businesses = Bogoblast::get_businesses();
                    ?>
                    <select name="bogoblast_business_id" id="bogoblast_business_id">
                        <option value="0">Multiple (Business Name or ID in CSV)</option>
                        <?php if(is_array($bogoblast_businesses) && sizeof($bogoblast_businesses) > 0):
                            foreach($bogoblast_businesses as $bogoblast_business): ?>
                                <option value="<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th></th>
                <td><button class="button-primary" type="submit">Upload and Preview</button></td>
            </tr>
        </table>
    </form>
</div>