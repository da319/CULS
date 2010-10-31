<?php
require_once ABSPATH . WPINC . '/pluggable.php';
if(isset($_GET['debug_action']) && $_GET['nonce']==wp_create_nonce($_GET['debug_action']))
switch($_GET['debug_action']){
    case 'reset_pro_translation_configuration':
        $sitepress_settings = get_option('icl_sitepress_settings');
        
        $sitepress_settings['content_translation_languages_setup'] = false;
        $sitepress_settings['content_translation_setup_complete'] = false;        
        unset($sitepress_settings['content_translation_setup_wizard_step']);
        unset($sitepress_settings['site_id']);
        unset($sitepress_settings['access_key']);
        unset($sitepress_settings['translator_choice']);
        unset($sitepress_settings['icl_lang_status']);
        unset($sitepress_settings['icl_balance']);
        unset($sitepress_settings['icl_support_ticket_id']);
        unset($sitepress_settings['icl_current_session']);
        unset($sitepress_settings['last_get_translator_status_call']);
        unset($sitepress_settings['last_icl_reminder_fetch']);
        unset($sitepress_settings['icl_account_email']);

        update_option('icl_sitepress_settings', $sitepress_settings);
        
        mysql_query("TRUNCATE TABLE {$wpdb->prefix}icl_core_status");
        mysql_query("TRUNCATE TABLE {$wpdb->prefix}icl_content_status");
        mysql_query("TRUNCATE TABLE {$wpdb->prefix}icl_string_status");
        mysql_query("TRUNCATE TABLE {$wpdb->prefix}icl_node");
        mysql_query("TRUNCATE TABLE {$wpdb->prefix}icl_reminders");
        
        header("Location: admin.php?page=".basename(ICL_PLUGIN_PATH).'/menu/content-translation.php');
        exit;
    
}
  
?>