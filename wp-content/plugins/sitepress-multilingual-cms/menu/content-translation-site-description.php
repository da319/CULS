        <?php if(!$sitepress_settings['content_translation_setup_complete']): /* run wizard */?>        
            <?php if(isset($_POST['icl_form_errors'])):  ?>
            <div class="icl_form_errors">
                <?php echo $_POST['icl_form_errors'] ?>
            </div>
            <br />
            <?php endif; ?>
        <?php endif; ?>


        <?php if(!$sitepress_settings['content_translation_setup_complete']): /* run wizard */?>        
            <form id="icl_more_options_wizard" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
            <?php wp_nonce_field('icl_site_description_wizard','icl_site_description_wizardnounce') ?>
        <?php else: ?>
            <form id="icl_more_options_wizard" name="icl_more_options_wizard" action="">
            <?php wp_nonce_field('icl_site_description_wizard','icl_site_description_wizardnounce') ?>
        <?php endif; ?>

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Information about your website', 'sitepress') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <h4><?php echo __('Describe your website', 'sitepress') ?></h4>             
                            <textarea id="icl_site_description" name="icl_description" type="textarea" cols="60" rows="5"><?php echo  $sitepress_settings['icl_site_description'] ?></textarea>
                            <p>Provide a short description of the website so that translators know what background is required from them.</p>

                            <?php if($sitepress_settings['content_translation_setup_complete']): ?>
                                <input id="icl_save_site_description" type="button" class="button-secondary action" value="<?php echo __('Save', 'sitepress') ?>" />
                                <span class="icl_ajx_response" id="icl_ajx_response_site"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php if(!$sitepress_settings['content_translation_setup_complete']): ?>
            <br />
            <div style="text-align:right">
                <?php //Hidden button for catching "Enter" key ?>
                <input id="icl_content_trans_setup_next_2_enter" class="button-primary" name="icl_content_trans_setup_next_2_enter" value="<?php echo __('Next', 'sitepress') ?>" type="submit" style="display:none"/>
                
                <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                <input id="icl_content_trans_setup_back_2" class="button-primary" name="icl_content_trans_setup_back_2" value="<?php echo __('Back', 'sitepress') ?>" type="submit" />
                <input id="icl_content_trans_setup_next_2" class="button-primary" name="icl_content_trans_setup_next_2" value="<?php echo __('Next', 'sitepress') ?>" type="submit" />
            </div>
        <?php endif; ?>
        </form>
 