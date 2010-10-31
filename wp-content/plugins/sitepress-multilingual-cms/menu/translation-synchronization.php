<?php
    require_once ICL_PLUGIN_PATH . '/inc/plugins-texts-functions.php';     
    $sitepress_settings = $sitepress->get_settings();     
    $icl_plugins_texts = icl_pt_get_texts();
    //icl_get_posts_translatable_fields();
    
    if(isset($_POST['icl_pt_file_upload'])){            
        $csv_file_upload_error = icl_pt_handle_upload();    
    }
    
    $notice = '';
    
    $cposts = array();
    $icl_post_types = $sitepress->get_translatable_documents(true);    
    
    foreach($icl_post_types as $k=>$v){
        if(!in_array($k, array('post','page'))){
            $cposts[$k] = $v;        
        }
    }
    
    foreach($cposts as $k=>$cpost){
        if(!isset($sitepress_settings['custom_posts_sync_option'][$k])){
            $cposts_sync_not_set[] = $cpost->labels->name;
        }    
    }    
    if(!empty($cposts_sync_not_set)){
        $notice = '<div class="updated below-h2"><p>';
        $notice .= sprintf(__("You haven't set your synchronization preferences for these custom posts: %s. Default value was selected.", 'sitepress'), 
            '<i>'.join('</i>, <i>', $cposts_sync_not_set) . '</i>');
        $notice .= '</p></div>';
    }
    
    global $wp_taxonomies;
    $ctaxonomies = array_diff(array_keys((array)$wp_taxonomies), array('post_tag','category', 'nav_menu', 'link_category'));    
    foreach($ctaxonomies as $ctax){
        if(!isset($sitepress_settings['taxonomies_sync_option'][$ctax])){
            $tax_sync_not_set[] = $wp_taxonomies[$ctax]->label;
        }    
    }
    if(!empty($tax_sync_not_set)){
        $notice .= '<div class="updated below-h2"><p>';
        $notice .= sprintf(__("You haven't set your synchronization preferences for these taxonomies: %s. Default value was selected.", 'sitepress'), 
            '<i>'.join('</i>, <i>', $tax_sync_not_set) . '</i>');
        $notice .= '</p></div>';
    }
?>
<?php $sitepress->noscript_notice() ?>
<div class="wrap">
    <div id="icon-options-general" class="icon32 icon32_adv"><br /></div>
    <h2><?php echo __('Translation synchronization', 'sitepress') ?></h2>    
    <?php if(isset($notice)) echo $notice ?>
    <?php include ICL_PLUGIN_PATH . '/menu/basic_advanced_switch.php' ?>
    <div id="icl_plugin_texts_wrapper" class="metabox-holder">
    <div class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div id="dashboard_wpml_plugin_texts" class="postbox">
                <div class="handlediv" title="<?php echo __('Click to toggle', 'sitepress'); ?>">
                    <br/>
                </div>
                <h3 class="hndle">
                    <span><?php echo __('Plugins texts translation', 'sitepress')?></span>
                </h3>                    
                <div class="inside">
            
                    <p><?php echo __('Select what other texts (besides title and body) you want to include in the translation.', 'sitepress') ?></p>
                    <form name="icl_plugins_texts" action="">
                    <table class="widefat" cellspacing="0">
                    <thead>
                    <tr>
                    <th scope="col"><?php echo __('Enable translation', 'sitepress') ?></th>
                    <th scope="col"><?php echo __('Plugin', 'sitepress') ?></th>
                    <th scope="col"><?php echo __('List of fields we translate', 'sitepress') ?></th>        
                    </tr>        
                    </thead>        
                    <tfoot>
                    <tr>
                    <th scope="col"><?php echo __('Enable translation', 'sitepress') ?></th>
                    <th scope="col"><?php echo __('Plugin', 'sitepress') ?></th>
                    <th scope="col"><?php echo __('List of fields we translate', 'sitepress') ?></th>        
                    </tr>        
                    </tfoot>                
                    <tbody>        
                    <?php foreach($icl_plugins_texts as $ipt): ?>
                    <tr>
                    <td scope="col"><input type="checkbox" name="icl_plugins_texts_enabled[]" value="<?php echo $ipt['plugin_name'] ?>" <?php if(!$ipt['active']): ?>disabled="disabled"<?php endif;?> <?php if($ipt['enabled']): ?>checked="checked"<?php endif;?>/></td>
                    <td scope="col"><?php echo $ipt['plugin_name_short'] ?></td>
                    <td scope="col"><?php echo $ipt['fields_list'] ?></td>
                    </tr>
                    <?php endforeach; ?>                                                                  
                    </tbody>        
                    </table>   
                    <br />
                    <p class="submit">
                        <input class="button" name="create account" value="<?php echo __('Save', 'sitepress') ?>" type="submit" />
                        <span class="icl_ajx_response" id="icl_ajx_response3"></span>    
                    </p>        
                    </form>
                    <br />
                    
                    <form method="post" action="admin.php?page=<?php echo $_GET['page'] ?>#icl_plugins_texts" enctype="multipart/form-data">
                    <?php if(isset($csv_file_upload_error) && $csv_file_upload_error): ?>
                    <p class="icl_form_errors"><?php echo $csv_file_upload_error ?></p>            
                    <?php endif; ?>            
                    <!--<input type="hidden" name="icl_ajx_action" value="icl_plugins_texts" />-->
                    <input type="hidden" name="icl_pt_file_upload" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
                    <p><?php echo __('If your plugin does not appear in this table, you can upload a CSV file that describes its texts.', 'sitepress') ?> <a href="http://wpml.org/?page_id=2065"><?php echo __('Read more', 'sitepress') ?></a></p>
                    <p>
                        <?php echo __('CSV plugin description', 'sitepress') ?>
                        <input class="button" type="file" name="plugins_texts_csv" />             
                        <input class="button" id="icl_pt_upload" type="submit" value="<?php echo __('Submit', 'sitepress')?>" />                            
                        <?php if(isset($csv_file_upload_error) && empty($csv_file_upload_error)):?>&nbsp;<span class="icl_ajx_response" style="display:inline">CSV file uploaded</span><?php endif;?>    
                    </p>
                    </form>            
                 </div>
            </div>
        </div>
    </div>
    </div>
    
    <form id="icl_page_sync_options" name="icl_page_sync_options" action="">        
    <table class="widefat">
        <thead>
            <tr>
                <th colspan="2"><?php _e('Posts and pages synchronization', 'sitepress');?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <br />                    
                    <p>
                        <label><input type="checkbox" id="icl_sync_page_ordering" name="icl_sync_page_ordering" <?php if($sitepress_settings['sync_page_ordering']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize page order for translations.', 'sitepress') ?></label>                        
                    </p>
                    <p>
                        <label><input type="checkbox" id="icl_sync_page_parent" name="icl_sync_page_parent" <?php if($sitepress_settings['sync_page_parent']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Set page parent for translation according to page parent of the original language.', 'sitepress') ?></label>                        
                    </p>
                    <p>
                        <label><input type="checkbox" name="icl_sync_page_template" <?php if($sitepress_settings['sync_page_template']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize page template.', 'sitepress') ?></label>                        
                    </p>                    
                    <p>
                        <label><input type="checkbox" name="icl_sync_comment_status" <?php if($sitepress_settings['sync_comment_status']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize comment status.', 'sitepress') ?></label>                        
                    </p>                    
                    <p>
                        <label><input type="checkbox" name="icl_sync_ping_status" <?php if($sitepress_settings['sync_ping_status']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize ping status.', 'sitepress') ?></label>                        
                    </p>                                        
                    <p>
                        <label><input type="checkbox" name="icl_sync_sticky_flag" <?php if($sitepress_settings['sync_sticky_flag']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize sticky flag.', 'sitepress') ?></label>                        
                    </p>                                                            
                    <p>
                        <label><input type="checkbox" name="icl_sync_private_flag" <?php if($sitepress_settings['sync_private_flag']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('Synchronize private flag.', 'sitepress') ?></label>                        
                    </p>                                                                                
                    <p>
                        <input class="button" name="save" value="<?php echo __('Save','sitepress') ?>" type="submit" />
                        <span class="icl_ajx_response" id="icl_ajx_response_mo"></span>
                    </p>                    
                </td>
                <td>
                    <br />                    
                    <p>
                        <label><input type="checkbox" name="icl_sync_delete" <?php if($sitepress_settings['sync_delete']): ?>checked="checked"<?php endif; ?> value="1" />
                        <?php echo __('When deleting a post, delete translations as well.', 'sitepress') ?></label>                        
                    </p>                                                                                
                </td>
            </tr>
        </tbody>
    </table>
    </form>                
    <br />
    <?php if(!empty($cposts)): ?>    
    <form id="icl_custom_posts_sync_options" name="icl_custom_posts_sync_options" action="">        
    <table class="widefat">
        <thead>
            <tr>
                <th width="60%"><?php _e('Custom posts', 'sitepress');?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cposts as $k=>$cpost): ?>
            <tr>
                <td><?php echo $cpost->labels->name; ?></td>
                <td>
                    <label><input type="radio" name="icl_sync_custom_posts[<?php echo $k ?>]" value="1" <?php
                        if($sitepress_settings['custom_posts_sync_option'][$k]==1) echo ' checked="checked"'
                    ?> />&nbsp;<?php _e('Translate', 'sitepress') ?></label>&nbsp;
                    <label><input type="radio" name="icl_sync_custom_posts[<?php echo $k ?>]" value="0" <?php
                        if($sitepress_settings['custom_posts_sync_option'][$k]==0) echo ' checked="checked"'
                    ?> />&nbsp;<?php _e('Do nothing', 'sitepress') ?></label>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2">
                <p>
                    <input type="submit" class="button" value="<?php _e('Save', 'sitepress') ?>" />
                    <span class="icl_ajx_response" id="icl_ajx_response_cp"></span>
                </p>
                </td>
            </tr>
        </tbody>
    </table>
    </form>    
    
    <?php endif; ?>     
    <?php if(!empty($ctaxonomies)): ?>
    <form id="icl_custom_tax_sync_options" name="icl_custom_tax_sync_options" action="">        
    <table class="widefat">
        <thead>
            <tr>
                <th width="60%"><?php _e('Custom taxonomies', 'sitepress');?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ctaxonomies as $ctax): ?>
            <tr>
                <td><?php echo $wp_taxonomies[$ctax]->label; ?></td>
                <td>
                    <label><input type="radio" name="icl_sync_tax[<?php echo $ctax ?>]" value="1" <?php
                        if($sitepress_settings['taxonomies_sync_option'][$ctax]==1) echo ' checked="checked"'
                    ?> />&nbsp;<?php _e('Translate', 'sitepress') ?></label>&nbsp;
                    <label><input type="radio" name="icl_sync_tax[<?php echo $ctax ?>]" value="0" <?php
                        if($sitepress_settings['taxonomies_sync_option'][$ctax]==0) echo ' checked="checked"'
                    ?> />&nbsp;<?php _e('Do nothing', 'sitepress') ?></label>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2">
                <p>
                    <input type="submit" class="button" value="<?php _e('Save', 'sitepress') ?>" />
                    <span class="icl_ajx_response" id="icl_ajx_response_ct"></span>
                </p>
                </td>
            </tr>
        </tbody>
    </table>
    </form>    
    
    <?php endif; ?> 
    <br clear="all" />
     
    <?php do_action('icl_menu_footer'); ?>       
</div>