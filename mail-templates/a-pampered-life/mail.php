<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0; padding: 0;" bgcolor="#dfe6ce" background="<?php echo BOGOBLAST_URL; ?>mail-templates/a-pampered-life/img/bg-tile.gif">
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr><td colspan="3" height="20" style="height: 20px;"></td></tr>
                <tr>
                    <td valign="top">
                        &nbsp;
                    </td>
                    <td valign="top" width="620">
                        <table border="0" cellpadding="0" cellspacing="0" width="620">
                            <tr>
                                <td align="center" valign="bottom" height="106" style="text-align: center; padding: 0; margin: 0;"><img src="<?php echo BOGOBLAST_URL; ?>mail-templates/a-pampered-life/img/header.gif" alt="A Pampered Life" style="display:block;" /></td>
                            </tr>
                            <tr>
                                <td bgcolor="#ffffff" background="<?php echo BOGOBLAST_URL; ?>mail-templates/a-pampered-life/img/body-bg.gif" style="font-family: Georgia, serif; font-style: italic; font-size: 16px; padding: 30px;">
                                    <?php echo nl2br($bogoblast_promotion->post_content); ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="middle" bgcolor="#000" style="text-align: center; padding: 16px; vertical-align: middle; color: #fff; font-family: Palatino Linotype, Book Antiqua, Palatino, serif; font-style: normal; font-size: 17px;">
                                    <img src="<?php echo BOGOBLAST_URL; ?>mail-templates/a-pampered-life/img/trident.gif" alt="" />
                                    
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                    <?php echo $business_address_1; ?> <?php echo $business_address_2; ?> <?php echo $business_city; ?>, <?php echo $business_state; ?> <?php echo $business_zip; ?>
                                    
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                    <?php echo BogoblastUtil::format_phone($business_phone); ?>
                                    
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <img src="<?php echo BOGOBLAST_URL; ?>mail-templates/a-pampered-life/img/trident.gif" alt="" />
                                </td>
                            </tr>
                            <tr>
                                <td align="left" style="text-align: left; padding: 6px 0 0 0;">
                                    <a href="<?php echo get_site_url() ?>/subscription-preferences/?utm_source=bogoblast&utm_medium=email&utm_campaign=email_footer_unsubscribe_link" style="color: #595959; font-size: 13px; text-decoration: none;">Click here to edit your subscription preferences.</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td valign="top">
                        &nbsp;
                    </td>
                </tr>
                <tr><td colspan="3" height="20" style="height: 20px;"></td></tr>
            </table>
        </td>
    </tr>
</table>