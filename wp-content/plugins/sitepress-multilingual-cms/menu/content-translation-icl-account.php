<?php

require_once ICL_PLUGIN_PATH . '/menu/content-translation-icl-account-wizard.php';

$wizard = new ICL_account_wizard();

?>

        <?php if($sitepress->icl_account_configured()): /* run wizard */?>        
            <?php if(isset($_POST['icl_form_errors'])):  ?>
            <div class="icl_form_errors">
                <?php echo $_POST['icl_form_errors'] ?>
            </div>
            <br />
            <?php endif; ?>
        <?php endif; ?>

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('ICanlocalize account setup', 'sitepress') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                              
                            <?php if(!$sitepress->icl_account_configured()): ?>
                            
                                <h3 id="icl_create_account_form"><?php echo __('Translation jobs go through an account at ICanLocalize.', 'sitepress') ?></h3>             

                                <?php if(isset($_POST['icl_form_errors']) || ($icl_account_ready_errors && !$sitepress->icl_account_configured() )):  ?>
                                <div class="icl_form_errors">
                                    <?php echo $_POST['icl_form_errors'] ?>
                                    <?php if($icl_account_ready_errors):  ?>
                                    <?php echo __('Before you create an ICanLocalize account you need to fix these:', 'sitepress'); ?>
                                    <ul>
                                    <?php foreach($icl_account_ready_errors as $err):?>        
                                    <li><?php echo $err ?></li>    
                                    <?php endforeach ?>
                                    </ul>   
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            
                                <?php if($sitepress->icl_support_configured()): ?>
                                <p style="line-height:1.5">You already have an ICanLocalize account for technical support. <br />
                                You can continue using this account, create a new account for translations or transfer this project to another account.</p>
                                <br />
                                <ul>
                                    <li style="list-style: none;">
                                        <label><input id="icl_existing" type="radio" value="0" onclick="<?php echo $wizard->on_click(0);?>" <?php if($sitepress->icl_support_configured()): ?>checked="checked"<?php endif; ?>/>
                                            <?php echo sprintf(__('Use my existing ICanLocalize account - <b>%s</b>', 'sitepress'), $sitepress_settings['support_icl_account_email']); ?>
                                        </label>
                                        <?php $wizard->use_existing_support_account(); ?>
                                    </li>
                                    <?php endif; ?>
                                    <li style="list-style: none;">
                                        <label><input id="icl_new" type="radio" value="1" onclick="<?php echo $wizard->on_click(1);?>" <?php if(!$sitepress->icl_support_configured()): ?>checked="checked"<?php endif;?> />
                                            <?php echo __('Create a new account in ICanLocalize', 'sitepress'); ?>
                                        </label>
                                        <?php $wizard->create_account($sitepress->icl_support_configured()); ?>
                                    </li>
                                    <?php if(!$sitepress->icl_support_configured()): ?>
                                    <li style="list-style: none;">
                                        <label><input id="icl_add" type="radio" value="2" onclick="<?php echo $wizard->on_click(2);?>" />
                                            <?php echo __('Add to an existing account at ICanLocalize', 'sitepress'); ?>
                                        </label>
                                            
                                        <?php $wizard->configure_account(); ?>
                                    </li>
                                    <?php endif; ?>
                                    <?php if($sitepress->icl_support_configured()): ?>
                                    <li style="list-style: none;">
                                        <label><input id="icl_transfer" type="radio" value="3" onclick="<?php echo $wizard->on_click(3);?>" />
                                            <?php echo __('Transfer to an existing account at ICanLocalize', 'sitepress'); ?>
                                        </label>
                                            
                                        <?php $wizard->transfer_to_account(); ?>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                                    
                                
                            <?php else: // if account configured ?>   

                                <form id="icl_transfer_this_account" method="post" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER  ?>/menu/content-translation.php#icl_create_account_form" <?php if($_POST['icl_acct_option2']):?>style="display:none"<?php endif?>>
                                <?php wp_nonce_field('icl_transfer_this_account','icl_transfer_this_accountnonce') ?>    
                                <p class="submit">                                    
                                    <?php echo __('You can transfer this site to another ICanLocalize account.', 'sitepress')?>
                                    <a href="javascript:;" onclick="jQuery('#icl_transfer_this_account').hide();jQuery('#icl_configure_account_transfer').fadeIn();"><?php echo __('Show settings &raquo;', 'sitepress') ?></a>
                                </p>
                                </form> 
                
                                <form id="icl_configure_account_transfer" action="" style="display:none">
                                <?php wp_nonce_field('icl_change_website_access_data','icl_change_website_access_data_nonce') ?>

                                <?php echo __('You can transfer this site to another ICanLocalize account.', 'sitepress')?>
                                <br />
                                <br />
                                <ul>
                                    <li>
                                        <label><input id="icl_new" name="icl_new" type="radio" value="1" onclick="<?php echo $wizard->on_click(1);?>" checked="checked" />
                                            <?php echo __('Create a new account in ICanLocalize', 'sitepress'); ?>
                                        </label>
                                        <?php $wizard->create_account(true); ?>
                                    </li>
                                    <li>
                                        <label><input id="icl_transfer" name="icl_transfer" type="radio" value="3" onclick="<?php echo $wizard->on_click(3);?>" />
                                            <?php echo __('Transfer to an existing account at ICanLocalize', 'sitepress'); ?>
                                        </label>
                                            
                                        <?php $wizard->transfer_to_account(); ?>
                                    </li>
                                </ul>
                                    
                                <div class="icl_form_errors" id="icl_account_errors" style="display:none">
                                </div>
                                <div class="icl_form_success" id="icl_account_success" style="display:none">
                                </div>
                                    
                                <p class="submit">                                         
                                    <input type="hidden" name="create_account" value="0" />
                                    <input id="icl_save_account_transfer" type="button" class="button-secondary action" value="<?php echo __('Transfer this site', 'sitepress') ?>" />
                                    <span class="icl_ajx_response" id="icl_ajx_response_account"></span>
                                    <a href="javascript:;" onclick="jQuery('#icl_configure_account_transfer').hide();jQuery('#icl_transfer_this_account').fadeIn();"><?php echo __('I don\'t want to transfer this site.', 'sitepress') ?></a>
                                </p>
                                </form>    
                
                                
                            <?php endif; ?>
         
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php if($sitepress->icl_account_configured() ): ?>
             <p class="alignright">   
             <input type="button" class="icl_account_setup_toggle button-primary" value="<?php _e('Close', 'sitepress')?>" />   
             </p>
             
            <?php if($sitepress_settings['content_translation_setup_complete']): ?>
                <p><input id="icl_disable_content_translation" type="button" class="button-secondary" 
                    value="<?php echo __('Disable professional translation','sitepress') ?>" /></p>
            <?php endif; ?>        

             <div class="clear"></div>
                          
             <?php endif; ?>
             
