<?php 
$icl_tables = array(
    $wpdb->prefix . 'icl_languages',
    $wpdb->prefix . 'icl_languages_translations',
    $wpdb->prefix . 'icl_translations',
    $wpdb->prefix . 'icl_locale_map',
    $wpdb->prefix . 'icl_flags',
    $wpdb->prefix . 'icl_content_status',
    $wpdb->prefix . 'icl_core_status',
    $wpdb->prefix . 'icl_node',
    $wpdb->prefix . 'icl_plugins_texts',
    $wpdb->prefix . 'icl_strings',
    $wpdb->prefix . 'icl_string_translations',
    $wpdb->prefix . 'icl_string_status',
    $wpdb->prefix . 'icl_string_positions',
    $wpdb->prefix . 'icl_cms_nav_cache',
    $wpdb->prefix . 'icl_message_status',
    $wpdb->prefix . 'icl_reminders',    
);

if( (isset($_POST['icl_reset_allnonce']) && $_POST['icl_reset_allnonce']==wp_create_nonce('icl_reset_all'))){
    if($_POST['icl-reset-all']=='on'){
        foreach($icl_tables as $icl_table){
            mysql_query("DROP TABLE " . $icl_table);
        }
        delete_option('icl_sitepress_settings');
        delete_option('icl_sitepress_version');
        delete_option('_icl_cache');
        delete_option('WPLANG');                
        deactivate_plugins(basename(ICL_PLUGIN_PATH) . '/sitepress.php');
        $ra = get_option('recently_activated');
        $ra[basename(ICL_PLUGIN_PATH) . '/sitepress.php'] = time();
        update_option('recently_activated', $ra);        
        echo '<script type="text/javascript">location.href=\''.admin_url('plugins.php?deactivate=true').'\'</script>';
    }
}                                    


?>
<div class="wrap">
    <div id="icon-options-general" class="icon32 icon32_adv" style="background: transparent url(<?php echo ICL_PLUGIN_URL; ?>/res/img/icon_adv.png) no-repeat;"><br /></div>
    <h2><?php echo __('Troubleshooting', 'sitepress') ?></h2>    
    
    <?php include ICL_PLUGIN_PATH . '/menu/basic_advanced_switch.php' ?>
    
    <?php
    foreach($icl_tables as $icl_table){
        echo '<a href="#'.$icl_table.'_anch">'.$icl_table.'</a> | ';
    }
    echo '<a href="#wpml-settings">'.__('WPML Settings', 'sitepress').'</a>';
    
    foreach($icl_tables as $icl_table){
        echo '<h3  id="'.$icl_table.'_anch" onclick="jQuery(\'#'.$icl_table.'\').toggle(); jQuery(\'#'.$icl_table.'_arrow_up\').toggle(); jQuery(\'#'.$icl_table.'_arrow_dn\').toggle();" style="cursor:pointer">'.$icl_table.'&nbsp;&nbsp;<span id="'.$icl_table.'_arrow_up" style="display:none">&uarr;</span><span id="'.$icl_table.'_arrow_dn">&darr;</span></h3>';        
        if(strtolower($wpdb->get_var("SHOW TABLES LIKE '{$icl_table}'")) != strtolower($icl_table)){
            echo '<p class="error">'.__('Not found!', 'sitepress').'</p>';
        }else{
            $results = $wpdb->get_results("DESCRIBE {$icl_table}", ARRAY_A);
            $keys = array_keys($results[0]);
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                    <?php foreach($keys as $k): ?><th width="<?php echo floor(100/count($keys)) ?>%"><?php echo $k ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $r):?>
                    <tr>
                        <?php foreach($keys as $k): ?><td><?php echo $r[$k] ?></td><?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <tbody>
            </table>
            <?php
            echo '<span id="'.$icl_table.'" style="display:none">';    
            $results = $wpdb->get_results("SELECT * FROM {$icl_table}", ARRAY_A);
            echo '<textarea style="font-size:10px;width:100%" wrap="off" rows="8" readonly="readonly">';
            $inc = 0;
            foreach((array)$results as $res){
                if($inc==0){
                    $columns = array_keys($res);
                    $columns = array_map('__custom_csv_escape', $columns);
                    echo implode(",", $columns) . PHP_EOL;;
                }
                $inc++;
                $res = array_map('__custom_csv_escape', $res);
                echo implode(",", $res) . PHP_EOL;
            }
            echo '</textarea>';
            echo '</span>';        
        }        
        
    }
    
    function __custom_csv_escape($s){
        $s = "&#34;". str_replace('"','&#34;',addslashes($s)) . "&#34;";
        return $s;
    }                         
    echo '<br /><hr /><h3 id="wpml-settings"> ' . __('WPML settings', 'sitepress') . '</h3>';
    echo '<textarea style="font-size:10px;width:100%" wrap="off" rows="16" readonly="readonly">';
    ob_start();
    print_r($sitepress->get_settings());
    $ob = ob_get_contents();
    ob_end_clean();
    echo htmlspecialchars($ob);
    echo '</textarea>';
    
    ?> 
    
    <script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#icl_torubleshooting_more_options').submit(iclSaveForm);
    })
    </script>
    <h3><?php _e('More options', 'sitepress')?></h3>
    <form name="icl_torubleshooting_more_options" id="icl_torubleshooting_more_options" action="">
    <label><input type="checkbox" name="troubleshooting_options[raise_mysql_errors]" value="1" <?php 
        if($sitepress_settings['troubleshooting_options']['raise_mysql_errors']): ?>checked="checked"<?php endif; ?>/>&nbsp;<?php 
        _e('Raise mysql errors on XML-RPC calls', 'sitepress')?></label>
    <p>
        <input class="button" name="save" value="<?php echo __('Apply','sitepress') ?>" type="submit" />
        <span class="icl_ajx_response" id="icl_ajx_response"></span>
    </p>    
    </form>
       
    <h3><?php _e('Database dump', 'sitepress')?></h3>
    <a class="button" href="admin.php?page=<?php echo ICL_PLUGIN_FOLDER ?>/menu/troubleshooting.php&amp;icl_action=dbdump"><?php _e('Download', 'sitepress') ?></a>
    
    <a name="icl-connection-test"></a>
    <h3><?php _e('ICanLocalize connection test', 'sitepress')?></h3>
    <?php if(isset($_GET['icl_action']) && $_GET['icl_action']=='icl-connection-test'): ?>
    <?php 
        $icl_query = new ICanLocalizeQuery();        
        if(isset($_GET['data'])){
            $user = unserialize(base64_decode($_GET['data']));
        }else{                
            $user['create_account'] = 1;
            $user['anon'] = 1;
            $user['platform_kind'] = 2;
            $user['cms_kind'] = 1;
            $user['blogid'] = $wpdb->blogid?$wpdb->blogid:1;
            $user['url'] = get_option('siteurl');
            $user['title'] = get_option('blogname');
            $user['description'] = $sitepress_settings['icl_site_description'];
            $user['is_verified'] = 1;                
           if(defined('ICL_AFFILIATE_ID') && defined('ICL_AFFILIATE_KEY')){
                $user['affiliate_id'] = ICL_AFFILIATE_ID;
                $user['affiliate_key'] = ICL_AFFILIATE_KEY;
            }
            $user['interview_translators'] = $sitepress_settings['interview_translators'];
            $user['project_kind'] = 2;
            $user['pickup_type'] = intval($sitepress_settings['translation_pickup_method']);
            $notifications = 0;
            if ( $sitepress_settings['icl_notify_complete']){
                $notifications += 1;
            }
            if ( $sitepress_settings['alert_delay']){
                $notifications += 2;
            }
            $user['notifications'] = $notifications;
            $user['ignore_languages'] = 0;
            $user['from_language1'] = isset($_GET['lang_from']) ? $_GET['lang_from'] : 'English';            
            $user['to_language1'] = isset($_GET['lang_to']) ? $_GET['lang_to'] : 'French';
        }
        
        define('ICL_DEB_SHOW_ICL_RAW_RESPONSE', true);
        $resp = $icl_query->createAccount($user);                
        echo '<textarea style="width:100%;height:400px;font-size:9px;">' . 
            __('Data', 'sitepress') . "\n----------------------------------------\n" . 
            print_r($user, 1) . 
            __('Response', 'sitepress') . "\n----------------------------------------\n" .
            print_r($resp, 1) . 
        '</textarea>';
                
    ?>
        
    <?php endif; ?>
    <a class="button" href="admin.php?page=<?php echo ICL_PLUGIN_FOLDER ?>/menu/troubleshooting.php&amp=ts=<?php echo time()?>&amp;icl_action=icl-connection-test#icl-connection-test"><?php _e('Connect', 'sitepress') ?></a>
    
    <?php
    
    echo '<br /><hr /><h3 id="wpml-settings"> ' . __('Reset', 'sitepress') . '</h3>';
    echo '<form method="post" onsubmit="return confirm(\''.__('Are you sure you want to reset all languages data? This operation cannot be reversed.', 'sitepress').'\')">';
    wp_nonce_field('icl_reset_all','icl_reset_allnonce');
    echo '<p class="error" style="padding:6px;">' . __("All translations you have sent to ICanLocalize will be lost if you reset WPML's data. They cannot be recovered later.", 'sitepress') 
        . '</p>';
    echo '<label><input type="checkbox" name="icl-reset-all" onchange="if(this.checked) jQuery(\'#reset-all-but\').removeAttr(\'disabled\'); else  jQuery(\'#reset-all-but\').attr(\'disabled\',\'disabled\');" /> ' . __('I am about to reset all language data.', 'sitepress') . '</label><br /><br />';
    echo '<input id="reset-all-but" type="submit" disabled="disabled" class="button-primary" value="'.__('Reset all language data and deactivate WPML', 'sitepress').'" />';    
    echo '</form>';
    
    
    
    ?>
    <?php do_action('icl_menu_footer'); ?>
</div>

