<?php

class ICL_account_wizard {


	function __construct() {
        }
        
        function use_existing_support_account() { 
            global $sitepress;
        ?>
            <form class="icl_account_form" id="icl_use_account" method="post" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER  ?>/menu/content-translation.php#icl_create_account_form" <?php if(!$sitepress->icl_support_configured()): ?>style="display:none"<?php endif; ?>>
            <?php wp_nonce_field('icl_use_account', 'icl_use_account_nonce') ?>
			
            <p style="line-height:1.5"><?php _e('Choose this option if you want to pay for translation work through your existing account.', 'sitepress'); ?></p>
			
            <p class="submit">                                        
                <div style="text-align:right">
                    <?php //Hidden button for catching "Enter" key ?>                                            
                    <input id="icl_content_trans_setup_finish_enter" class="button-primary" name="icl_content_trans_setup_finish_enter" value="<?php echo __('Add project to my account and finish', 'sitepress') ?>" type="submit" style="display:none"/>

                    <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                    <input id="icl_content_trans_setup_back_2" class="button-primary" name="icl_content_trans_setup_back_2" value="<?php echo __('Back', 'sitepress') ?>" type="submit" />
                    <input id="icl_content_trans_setup_finish" class="button-primary" name="icl_content_trans_setup_finish" value="<?php echo __('Use existing account and Finish', 'sitepress') ?>" type="submit" />
                </div>
            </p>
            </form>
            <?php
        }
        
        
        function create_account($transfer_after_create) {
            global $sitepress;
        
        ?>
        
            <?php if(!$sitepress->icl_account_configured()): ?>        
				<form class="icl_account_form" id="icl_create_account" method="post" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER  ?>/menu/content-translation.php#icl_create_account_form" <?php if($sitepress->icl_support_configured()): ?>style="display:none"<?php endif; ?>>
				<?php
					if ($transfer_after_create) {
						wp_nonce_field('icl_create_account_and_transfer', 'icl_create_account_and_transfer_nonce');
						?>
						<p style="line-height:1.5"><?php _e('Choose this option to create a new account, which would be responsible for paying for translation work.', 'sitepress'); ?></p>
						<?php
					} else {
						wp_nonce_field('icl_create_account', 'icl_create_account_nonce');
						?>
						<p style="line-height:1.5"><?php _e('Creating an account in ICanLocalize is free. You will only need to pay when sending posts and pages for translation.', 'sitepress'); ?></p>
						<?php
					}
				?>
			<?php else: ?>
	            <div class="icl_account_form" id="icl_create_account" <?php if($_POST['icl_transfer'] == '3'): ?>style="display:none"<?php endif;?>>
				<p style="line-height:1.5"><?php _e('Choose this option to create a new account, which would be responsible for paying for translation work.', 'sitepress'); ?></p>
			<?php endif; ?>

            
            <table class="form-table icl-account-setup">
                <tbody>
                <tr class="form-field">
                    <th scope="row"><?php echo __('First name', 'sitepress')?></th>
                    <td><input name="user[fname]" type="text" value="<?php echo $_POST['user']['fname']?$_POST['user']['fname']:$current_user->first_name ?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><?php echo __('Last name', 'sitepress')?></th>
                    <td><input name="user[lname]" type="text" value="<?php echo  $_POST['user']['lname']?$_POST['user']['lname']:$current_user->last_name ?>" /></td>
                </tr>        
                <tr class="form-field">
                    <th scope="row"><?php echo __('Email', 'sitepress')?></th>
                    <td><input name="user[email]" type="text" value="<?php echo  $_POST['user']['email']?$_POST['user']['email']:$current_user->data->user_email ?>" /></td>
                </tr>
                </tbody>
            </table>
            <?php if(!$sitepress->icl_account_configured()): ?>        
                <p class="submit">                                        
                    <div style="text-align:right">
                        <?php //Hidden button for catching "Enter" key ?>                                            
                        <input id="icl_content_trans_setup_finish_enter" class="button-primary" name="icl_content_trans_setup_finish_enter" value="<?php echo __('Add project to my account and finish', 'sitepress') ?>" type="submit" style="display:none"/>

                        <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                        <input id="icl_content_trans_setup_back_2" class="button-primary" name="icl_content_trans_setup_back_2" value="<?php echo __('Back', 'sitepress') ?>" type="submit" />
                        <input id="icl_content_trans_setup_finish" class="button-primary" name="icl_content_trans_setup_finish" value="<?php echo __('Create account and Finish', 'sitepress') ?>" type="submit" />
                    </div>
                </p>
                <div class="icl_progress"><?php _e('Saving. Please wait...', 'sitepress'); ?></div>
	            </form>
			<?php else: ?>
	            </div>
			<?php endif; ?>
        <?php                                
        }
        
        function configure_account() {
        
            ?>
            <form class="icl_account_form" id="icl_configure_account" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER  ?>/menu/content-translation.php#icl_create_account_form" method="post" style="display:none">
            <?php wp_nonce_field('icl_configure_account','icl_configure_account_nonce') ?>    
            <table class="form-table icl-account-setup">
                <tbody>
                <tr class="form-field">
                    <th scope="row"><?php echo __('Email', 'sitepress')?></th>
                    <td><input name="user[email]" type="text" value="<?php echo  $_POST['user']['email']?$_POST['user']['email']:$current_user->data->user_email ?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><?php echo __('Password', 'sitepress')?></th>
                    <td><input name="user[password]" type="password" /></td>
                </tr>        
                </tbody>
            </table>
            <?php if(!$sitepress_settings['content_translation_setup_complete']): ?>        
                <p class="submit">                                        
                    <div style="text-align:right">
                        <?php //Hidden button for catching "Enter" key ?>
                        <input id="icl_content_trans_setup_finish_enter" class="button-primary" name="icl_content_trans_setup_finish_enter" value="<?php echo __('Add project to my account and finish', 'sitepress') ?>" type="submit" style="display:none"/>
                        
                        <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                        <input id="icl_content_trans_setup_back_2" class="button-primary" name="icl_content_trans_setup_back_2" value="<?php echo __('Back', 'sitepress') ?>" type="submit" />
                        <input id="icl_content_trans_setup_finish" class="button-primary" name="icl_content_trans_setup_finish" value="<?php echo __('Add project to my account and finish', 'sitepress') ?>" type="submit" />
                    </div>
                </p>
                <div class="icl_progress"><?php _e('Saving. Please wait...', 'sitepress'); ?></div>                                        
            <?php else: ?>
                <p class="submit">                                        
                    <input type="hidden" name="create_account" value="0" />                                        
                    <input class="button" name="configure account" value="<?php echo __('Add this project to my account', 'sitepress') ?>" type="submit" 
                        <?php if($icl_account_ready_errors):  ?>disabled="disabled"<?php endif; ?> />
                    <a href="javascript:;" onclick="jQuery('#icl_configure_account').hide();jQuery('#icl_create_account').fadeIn();"><?php echo __('Create a new ICanLocalize account', 'sitepress') ?></a>                                        
                </p>                                    
                <div class="icl_progress"><?php _e('Saving. Please wait...', 'sitepress'); ?></div>
            <?php endif; ?>
            </form>    
        <?php                                
        }
  
        function transfer_to_account() {
			global $sitepress;
        
            ?>
            <?php if(!$sitepress->icl_account_configured()): ?>        
	            <form class="icl_account_form" id="icl_transfer_account" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER  ?>/menu/content-translation.php#icl_create_account_form" method="post" style="display:none">
	            <?php wp_nonce_field('icl_transfer_account','icl_transfer_account_nonce') ?>
			<?php else: ?>
	            <div class="icl_account_form" id="icl_transfer_account" <?php if($_POST['icl_transfer'] != '3'): ?>style="display:none"<?php endif;?>>
			<?php endif; ?>
			
		    <p style="line-height:1.5"><?php _e('Choose this option to transfer this project to another ICanLocalize account, which will be responsible for paying for translation work.', 'sitepress'); ?></p>

            <table class="form-table icl-account-setup">
                <tbody>
                <tr class="form-field">
                    <th scope="row"><?php echo __('Email', 'sitepress')?></th>
                    <td><input name="user[email2]" type="text" value="<?php echo  $_POST['user']['email']?$_POST['user']['email']:$current_user->data->user_email ?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><?php echo __('Password', 'sitepress')?></th>
                    <td><input name="user[password]" type="password" /></td>
                </tr>        
                </tbody>
            </table>
            <?php if(!$sitepress->icl_account_configured()): ?>        
                <p class="submit">                                        
                    <div style="text-align:right">
                        <?php //Hidden button for catching "Enter" key ?>
                        <input id="icl_content_trans_setup_finish_enter" class="button-primary" name="icl_content_trans_setup_finish_enter" value="<?php echo __('Add project to my account and finish', 'sitepress') ?>" type="submit" style="display:none"/>
                        
                        <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                        <input id="icl_content_trans_setup_back_2" class="button-primary" name="icl_content_trans_setup_back_2" value="<?php echo __('Back', 'sitepress') ?>" type="submit" />
                        <input id="icl_content_trans_setup_finish" class="button-primary" name="icl_content_trans_setup_finish" value="<?php echo __('Add project to user\'s account and finish', 'sitepress') ?>" type="submit" />
                    </div>
                </p>
                <div class="icl_progress"><?php _e('Saving. Please wait...', 'sitepress'); ?></div>                                        
	            </form>
			<?php else: ?>
	            </div>
			<?php endif; ?>
        <?php                                
        }
  
        function on_click($item) {
            switch($item) {
                case 0:
                    return "jQuery('#icl_use_account').fadeIn();
                            jQuery('#icl_create_account').hide();
                            jQuery('#icl_configure_account').hide();
                            jQuery('#icl_transfer_account').hide();
                            jQuery('#icl_new').attr('checked', '');
                            jQuery('#icl_add').attr('checked', '');
                            jQuery('#icl_transfer').attr('checked', '');";
                case 1:
                    return "jQuery('#icl_use_account').hide();
                            jQuery('#icl_create_account').fadeIn();
                            jQuery('#icl_configure_account').hide();
                            jQuery('#icl_transfer_account').hide();
                            jQuery('#icl_existing').attr('checked', '');
                            jQuery('#icl_add').attr('checked', '');
                            jQuery('#icl_transfer').attr('checked', '');";
                case 2:
                    return "jQuery('#icl_use_account').hide();
                            jQuery('#icl_create_account').hide();
                            jQuery('#icl_configure_account').fadeIn();
                            jQuery('#icl_transfer_account').hide();
                            jQuery('#icl_existing').attr('checked', '');
                            jQuery('#icl_new').attr('checked', '');
                            jQuery('#icl_transfer').attr('checked', '');";
                case 3:
                    return "jQuery('#icl_use_account').hide();
                            jQuery('#icl_create_account').hide();
                            jQuery('#icl_configure_account').hide();
                            jQuery('#icl_transfer_account').fadeIn();
                            jQuery('#icl_existing').attr('checked', '');
                            jQuery('#icl_new').attr('checked', '');
                            jQuery('#icl_add').attr('checked', '');";
            }
        }
}

?>