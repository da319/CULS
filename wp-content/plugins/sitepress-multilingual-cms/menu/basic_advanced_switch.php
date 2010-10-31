    <div class="icl_advanced_switch">        
        <?php if($sitepress_settings['basic_menu']): ?>
        <span><?php printf(__('Using WordPress as CMS? Try WPML\'s <span title="%s" %s>advanced features</span>.','sitepress'),__('Theme localization, translation synchronization, comments translation, CMS navigation, Sticky links','sitepress'), 'style="border-bottom: 1pt dotted #707070;"') ?> <a class="button icl_golden_button" href="admin.php?page=<?php echo $_GET['page'] ?>&amp;icl_action=advanced&amp;nonce=<?php echo wp_create_nonce('icl_enable_advanced_mode') ?>" title="<?php _e("Switch to advanced setup mode", 'sitepress') ?>"><?php _e('Go Advanced', 'sitepress')?></a></span>
        <?php else: ?>
        <?php 
            if($_GET['page']==basename(ICL_PLUGIN_PATH).'/menu/content-translation.php'){
                $_bgotopage = $_GET['page'];
            }else{
                $_bgotopage = basename(ICL_PLUGIN_PATH).'/menu/languages.php';
            }
        ?>
        <span>&nbsp;<a class="button" href="admin.php?page=<?php echo $_bgotopage ?>&amp;icl_action=basic&amp;nonce=<?php echo wp_create_nonce('icl_enable_basic_mode') ?>" title="<?php _e("Switch to basic setup mode", 'sitepress') ?>"><?php _e('Back to Basic mode', 'sitepress')?></a></span>
        <?php endif; ?>
    </div>
