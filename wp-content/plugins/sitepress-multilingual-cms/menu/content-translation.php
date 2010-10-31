<?php     
    require_once ICL_PLUGIN_PATH . '/sitepress.php';

    if (isset($_GET['icl_refresh_langs']) || $sitepress->are_waiting_for_translators($sitepress->get_default_language())) {
        $iclsettings = $sitepress->get_settings();
        $iclsettings['last_get_translator_status_call'] = time();
        $sitepress->get_icl_translator_status($iclsettings);
        $sitepress->save_settings($iclsettings);
    }
    
    $active_languages = $sitepress->get_active_languages();
    $default_language = $sitepress->get_default_language();
    // put the default language first.
    foreach ($active_languages as $index => $lang) {
        if ($lang['code'] == $default_language) {
            $default_lang_data = $lang;
            unset($active_languages[$index]);
            break;
        }
    }
    if (isset($default_lang_data)) {
        array_unshift($active_languages, $default_lang_data);
    }
    
    
    $sitepress_settings = $sitepress->get_settings();    
    $icl_account_ready_errors = $sitepress->icl_account_reqs();
    
    $icl_lang_status = $sitepress_settings['icl_lang_status'];    
?>
<?php $sitepress->noscript_notice() ?>
<div class="wrap" id="icl_wrap" style="float:left;width:98%;">
    <div id="icon-options-general" class="icon32<?php if(!$sitepress_settings['basic_menu']) echo ' icon32_adv'?>"><br /></div>
    <h2><?php _e('Professional Translation', 'sitepress') ?></h2>    

    <?php include ICL_PLUGIN_PATH . '/menu/basic_advanced_switch.php' ?>     

    <table style="width:100%; border: none;"><tr>
    <td style="vertical-align:top;">
        <div id="icl_pro_content">

                    
        <?php if(isset($_POST['icl_form_success'])):?>
        <p class="icl_form_success"><?php echo $_POST['icl_form_success'] ?></p>
        <?php endif; ?>  
                
            <h3><?php _e('Translation management', 'sitepress')?></h3>
            <?php include ICL_PLUGIN_PATH . '/modules/icl-translation/icl-translation-dashboard.php'; ?>
        
            <?php if($sitepress->icl_account_configured()): ?>            
            <a href="#" class="icl_account_setup_toggle icl_account_setup_toggle_main"><?php _e('Advanced options', 'sitepress') ?>&nbsp;&raquo;</a>
            <div id="icl_account_setup">
                <?php if(count($active_languages) > 1): ?>
                        <?php include ICL_PLUGIN_PATH . '/menu/content-translation-options.php';?>
                        <br clear="all" />
                <?php else:?>                    
                    <p class='icl_form_errors'><?php echo __('After you configure more languages for your blog, the translation options will show here', 'sitepress'); ?></p>
                <?php endif; ?>                            
            </div> <?php // <div id="icl_account_setup"> ?>
            <?php endif; ?>
            
            <?php if(empty($translators_selected)):?>
            <div class="icl_cyan_box">
            <b><?php echo $sitepress->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=moreinfo-wp&iso=' . 
                $sitepress->get_locale($sitepress->get_admin_language()).'&src='.get_option('home'), 
                array('title'=>__('About Our Translators', 'sitepress'), 'unload_cb'=>'icl_prevent_tb_reload')) ?><?php _e('About Our Translators', 'sitepress'); ?></a></b><br />
            <?php _e('ICanLocalize offers expert translators at competitive rates.', 'sitepress'); ?><br />
            <?php echo $sitepress->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=moreinfo-wp&iso=' . 
                $sitepress->get_locale($sitepress->get_admin_language()).'&src='.get_option('home'), 
                array('title'=>__('About Our Translators', 'sitepress'), 'unload_cb'=>'icl_prevent_tb_reload')) ?><?php _e('Learn more', 'sitepress'); ?></a>
            </div>
            <?php endif; ?>                            
            
            <div class="icl_cyan_box">
                <?php if($sitepress->icl_account_configured() && $sitepress_settings['icl_html_status']): ?>
                <h3><?php _e('ICanLocalize account status', 'sitepress')?></h3>
                <?php echo $sitepress_settings['icl_html_status']; ?>
                <?php else: ?> 
                <?php printf(__('For help getting started, %scontact ICanLocalize%s', 'sitepress'), 
                    '<a href="http://www.icanlocalize.com/site/about-us/contact-us/" target="_blank">', '</a>'); ?>                          
                <?php endif; ?>
            </div>         
            
    </div>    

    </td><td style="vertical-align:top; padding: 21px 0 0 10px;">
        <?php echo $sitepress->show_pro_sidebar() ?>
    </td></tr></table>
    <?php remove_action('icl_menu_footer', array($sitepress, 'menu_footer')) ?>                                                       
    <?php do_action('icl_extra_options_' . $_GET['page']); ?>        
                            
    <?php do_action('icl_menu_footer'); ?>

</div>

