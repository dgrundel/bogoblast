<div class="bogoblast_subscriber_export_wrapper wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Subscriber Export &raquo; Options</h2>

    <form enctype="multipart/form-data" method="post" action="<?php echo get_admin_url().'edit.php?post_type=bogoblast_subscriber&page=bogoblast-subscriber-export&action=export'; ?>">
        <table class="form-table">
            <tr>
                <th><label for="bogoblast_business_id">Export Business</label></th>
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
                <td><button class="button-primary" type="submit">Export</button></td>
            </tr>
        </table>
    </form>
</div>