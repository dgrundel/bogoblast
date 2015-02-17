<div class="bogoblast_business_merge_wrapper wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Business Merge &raquo; Select</h2>
    
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
    
    <form enctype="multipart/form-data" method="post" action="<?php echo get_admin_url().'edit.php?post_type=bogoblast_business&page=bogoblast-business-merge&action=preview'; ?>">
        <table class="form-table">
            <tr>
                <th><label>Businesses to Merge</label></th>
                <td>
                    <ul class="bogoblast_businesses">
                        <?php if(is_array($bogoblast_businesses) && sizeof($bogoblast_businesses) > 0):
                            foreach($bogoblast_businesses as $bogoblast_business): ?>
                                
                                <li>
                                    <input type="hidden" name="bogoblast_business_merge_ids[<?php echo $bogoblast_business->ID ?>]" value="0">
                                    <input type="checkbox"
                                        id="bogoblast_business_merge_ids_<?php echo $bogoblast_business->ID ?>"
                                        name="bogoblast_business_merge_ids[<?php echo $bogoblast_business->ID ?>]"
                                        value="1" />
                                    <label for="bogoblast_business_merge_ids_<?php echo $bogoblast_business->ID ?>"><?php echo $bogoblast_business->post_title; ?> (ID: <?php echo $bogoblast_business->ID ?>)</label>
                                </li>
                                
                            <?php endforeach;
                        endif; ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th><label for="bogoblast_business_id">Merge into Business</label></th>
                <td>
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
                <td><button class="button-primary" type="submit">Preview Merge</button></td>
            </tr>
        </table>
    </form>
</div>