        <?php if($sitepress_settings['translator_choice'] === null) {
            $sitepress_settings['translator_choice'] = 0;
        }
        ?>


            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Advanced translation options', 'sitepress') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
            
                            <form name="icl_more_options" id="icl_more_options" action="">
                            
                            <div id="icl-content-translation-advanced-options">
                
                                    <?php if(defined('ICL_DEBUG_DEVELOPMENT') && ICL_DEBUG_DEVELOPMENT): ?>
                                    <a style="float:right;" href="admin.php?page=<?php echo basename(ICL_PLUGIN_PATH)?>/menu/content-translation.php&amp;debug_action=reset_pro_translation_configuration&amp;nonce=<?php echo wp_create_nonce('reset_pro_translation_configuration')?>" class="button">Reset pro translation configuration</a>
                                    <?php endif; ?>
                
                                    <h3><?php echo __('Translation delivery','sitepress') ?></h3>    
                                    <ul>
                                        <li>
                                            <?php echo __("Select the desired translation delivery mehtod:", 'sitepress') ?><br />
                                        </li>
                                        <li>
                                            <ul>
                                                <li>
                                                    <label><input name="icl_delivery_method" type="radio" value="0" 
                                                        <?php if((int)$sitepress_settings['translation_pickup_method'] == 0): ?>checked="checked"<?php endif;?> /> 
                                                        <?php echo __('Translations will be posted back to this website via XML-RPC.', 'sitepress'); ?>
                                                    </label><br />
                                                </li>                        
                                                <li>
                                                    <label><input name="icl_delivery_method" type="radio" value="1" 
                                                        <?php if($sitepress_settings['translation_pickup_method'] == 1): ?>checked="checked"<?php endif;?> disabled="disabled" /> 
                                                        <?php echo __('This WordPress installation will poll for translations.', 'sitepress'); ?>
                                                    </label><br />
                                                </li>                        
                                            </ul>
                                        </li>
                                        <li>
                                            <i><?php echo __("Choose polling if your site is inaccessible from the Internet.", 'sitepress') ?></i><br />
                                        </li>
                                    </ul>
                
                                <h3><?php echo __("Notification preferences:", 'sitepress') ?></h3>
                                <ul>
                                    <li>
                                        <ul>
                                            <li>
                                                <label><input name="icl_notify_complete" type="checkbox" value="1" 
                                                    <?php if($sitepress_settings['notify_complete']): ?>checked="checked"<?php endif;?> /> 
                                                    <?php echo __('Send an email notification when translations complete.', 'sitepress'); ?>
                                                </label><br />
                                            </li>
                                            <li>
                                                <label><input name="icl_alert_delay" type="checkbox" value="1" <?php if($sitepress_settings['alert_delay']): ?>checked="checked"<?php endif;?> /> <?php echo __('Send an alert when translations delay for more than 4 days.', 'sitepress'); ?></label><br />
                                            </li>
                                        </ul>
                    
                                    </li>
                                    <li>
                                        <i><?php echo __("ICanLocalize will send email notifications for these events.", 'sitepress') ?></i><br />
                                    </li>
                                </ul>
                                
                                <h3><?php echo __("Translated document status:", 'sitepress') ?></h3>
                                <ul>
                                    <li>
                                        <ul>
                                            <li>
                                                <label><input type="radio" name="icl_translated_document_status" value="0" 
                                                    <?php if(!$sitepress_settings['translated_document_status']): ?>checked="checked"<?php endif;?> /> 
                                                    <?php echo __('Draft', 'sitepress') ?>
                                                </label>
                                            </li>
                                            <li>
                                                <label><input type="radio" name="icl_translated_document_status" value="1" 
                                                    <?php if($sitepress_settings['translated_document_status']): ?>checked="checked"<?php endif;?> /> 
                                                    <?php echo __('Same as the original document', 'sitepress') ?>
                                                </label>
                                            </li>
                                        </ul>
                    
                                    </li>
                                    <li>
                                        <i><?php echo __("Choose if translations should be published when received. Note: If Publish is selected, the translation will only be published if the original node is published when the translation is received.", 'sitepress') ?></i><br />
                                    </li>
                                </ul>
                
                                <h3><?php echo __("Remote control translation management:", 'sitepress') ?></h3>
                                <ul>
                                    <li>
                                        <ul>
                                            <li>
                                                <label><input name="icl_remote_management" type="checkbox" value="1" 
                                                    <?php if($sitepress_settings['remote_management']): ?>checked="checked"<?php endif;?> /> 
                                                    <?php echo __('Enable remote control over the translation management.', 'sitepress'); ?>
                                                </label><br />
                                            </li>
                                        </ul>
                    
                                    </li>
                                    <li>
                                        <i><?php _e("This feature is intended for blog networks. It allows controlling the translation process remotely via XML-RPC calls without going through the WordPress admin pages.<br />If you are running a single site, you don't need to enable this.", 'sitepress') ?></i><br />
                                    </li>
                                </ul>
                                
                            </div> <?php // div id="icl-content-translation-advanced-options ?>
                            
                                <input id="icl_translation_options_save" class="button" name="create account" value="<?php echo __('Save', 'sitepress') ?>" type="submit" />
                                <span class="icl_ajx_response" id="icl_ajx_response2"></span>    
                                <input class="button" type="button" value="<?php _e('Cancel', 'sitepress')?>" onclick="jQuery('#icl_account_setup').slideUp(function(){jQuery('.icl_account_setup_toggle').show();jQuery('#icl_languages_translators_stats').show()});" />
                                </form>
                          
                        </td>
                    </tr>
                </tbody>
            </table>
