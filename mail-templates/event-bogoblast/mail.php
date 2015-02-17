<table bgcolor="#f2f2f2" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr><td colspan="3" height="20" style="height: 20px;"></td></tr>
    <tr>
        <td valign="top">
            &nbsp;
        </td>
        <td valign="top" width="650">
            <table border="0" cellpadding="0" cellspacing="0" width="650">
                <tr>
                    <td align="left" valign="bottom" bgcolor="#595959" style="text-align: left; font-size: 16px; background-color: #595959; color: #fff; padding: 4px;">
                        Presenting an exciting event from<br />
                        <span style="font-size: 28px;"><?php echo $bogoblast_business->post_title; ?></span>
                    </td>
                    <td align="right" bgcolor="#595959" style="text-align: right; background-color: #595959; padding: 4px;">
                        <img src="<?php echo BOGOBLAST_URL; ?>mail-templates/bogoblast-default/img/bogoblast-logo.gif" alt="Bogoblast.com" />
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" colspan="2" style="background-color: #fff;">
                        <div style="border: 2px dashed #595959; margin: 10px; padding: 10px;">
                            <table width="100%">
                                <tr>
                                    <td align="center" valign="top" width="40%" style="text-align: center; vertical-align: top; color: #000; font-size: 18px;">
                                        <p><?php Bogoblast::post_thumbnail($bogoblast_business->ID); ?><p>
                                        <?php if(strlen($business_phone) > 0): ?><p><?php echo BogoblastUtil::format_phone($business_phone); ?></p><?php endif; ?>
                                        <?php if(strlen($business_address_1) > 0): ?>
                                            <p>
                                                <?php echo $business_address_1; ?><br />
                                                <?php if(strlen($business_address_2) > 0): ?>
                                                    <?php echo $business_address_2; ?><br />
                                                <?php endif; ?>
                                                <?php echo $business_city; ?>,
                                                <?php echo $business_state; ?>
                                                <?php echo $business_zip; ?>
                                            </p>
                                            <?php if(strlen($business_directions) > 0): ?>
                                                <p><?php echo $business_directions; ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if(strlen($business_url) > 0): ?><p><a href="<?php echo $business_url; ?>">Visit Our Website</a></p><?php endif; ?>
                                    </td>
                                    <td align="center" width="60%" style="text-align: center;">
                                        <div style="color: #000; font-size: 22px;"><?php echo nl2br($bogoblast_promotion->post_content); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="color: #595959; font-size: 12px;">
                                        <?php echo get_post_meta($bogoblast_promotion->ID, '_promotion_fine_print', true); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="left" valign="bottom" bgcolor="#6cd420" colspan="2" style="text-align: left; font-size: 22px; background-color: #8EBC45; color: #fff; padding: 12px;">
                        <a href="<?php echo get_site_url() ?>/businesses?utm_source=bogoblast&utm_medium=email&utm_campaign=email_footer_directory_link" style="color: #fff; font-weight: bold; text-decoration: none;">Click here for great local deals at BogoBlast.com!</a>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="text-align: left; padding: 6px 0 0 0;">
                        <a href="<?php echo get_site_url() ?>/subscription-preferences/?utm_source=bogoblast&utm_medium=email&utm_campaign=email_footer_unsubscribe_link" style="color: #595959; font-size: 13px; text-decoration: none;">Click here to edit your subscription preferences.</a>
                    </td>
                    <td align="right" style="text-align: right; padding: 6px 0 0 0;">
                        <a href="<?php echo get_site_url() ?>/for-business/?utm_source=bogoblast&utm_medium=email&utm_campaign=email_footer_for_business_link&landing" style="color: #595959; font-size: 13px; text-decoration: none;">Local business? Let's talk!</a>
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