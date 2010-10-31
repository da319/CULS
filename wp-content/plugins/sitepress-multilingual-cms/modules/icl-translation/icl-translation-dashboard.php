<?php 
    $default_language = $sitepress->get_default_language();
    
    if(isset($icl_translation_filter['lang'])){
        $selected_language = $icl_translation_filter['lang']; 
    }else{
        $selected_language = isset($_GET['lang'])?$_GET['lang']:$default_language;
    }
    if(isset($icl_translation_filter['tstatus'])){
        $tstatus = $icl_translation_filter['tstatus']; 
    }else{
        $tstatus = isset($_GET['tstatus'])?$_GET['tstatus']:'all';
    }     
    if(isset($icl_translation_filter['status_on'])){
        $status = $icl_translation_filter['status'];
    }else{
        if(isset($_GET['status_on']) && isset($_GET['status'])){
            $status = $_GET['status'];
        }else{
            $status = false;
            if(isset($icl_translation_filter)){
                unset($icl_translation_filter['status_on']);
                unset($icl_translation_filter['status']);                
            }
        }
    }

    if(isset($icl_translation_filter['type_on'])){
        $type = $icl_translation_filter['type'];
    }else{
        if(isset($_GET['type_on']) && isset($_GET['type'])){
            $type = $_GET['type'];
        }else{
            $type = false;
            if(isset($icl_translation_filter)){
                unset($icl_translation_filter['type_on']);
                unset($icl_translation_filter['type']);
            }
        }
    }   
    
    $active_languages = $sitepress->get_active_languages();
    $sitepress_settings = $sitepress->get_settings();
    $language_pairs = $sitepress_settings['language_pairs'];
    $documents = icl_translation_get_documents($selected_language, $tstatus, $status, $type);
    $icl_post_statuses = array(
        'publish'   =>__('Published', 'sitepress'),
        'draft'     =>__('Draft', 'sitepress'),
        'pending'   =>__('Pending Review', 'sitepress'),
        'future'    =>__('Scheduled', 'sitepress')
    );    
    $icl_post_types = $sitepress->get_translatable_documents();
    
?>
<?php $sitepress->noscript_notice() ?>

    <?php if ($_GET['message'] == 'icl_message_error'): ?>    
        <div class="icl_form_errors"><?php echo __('Error sending some documents to translation', 'sitepress')?></div>
    <?php endif;?>
    <?php if ($_GET['message'] == 'icl_message_1'): ?>    
        <div class="icl_form_success"><?php echo __('All documents sent to translation', 'sitepress')?></div>
    <?php endif;?>
         
        
    <?php if(isset($_GET['post_id'])): ?>
    <a href="admin.php?page=<?php echo $_GET['page']?>"><?php echo __('Show all documents', 'sitepress')?></a> / <a href="post.php?action=edit&amp;post=<?php echo $_GET['post_id'] ?>"><?php printf(__("Back to editing '%s'", 'sitepress'),$documents[$_GET['post_id']]->post_title); ?></a>
    <script type="text/javascript">
    jQuery(document).ready(function(){
            jQuery('#icl-estimated-words-count').html('<?php echo count(explode(' ', $documents[$_GET['post_id']]->post_content)) ?>');
            jQuery('#icl-estimated-quote').html('<?php echo 0.07 * count(explode(' ', $documents[$_GET['post_id']]->post_content)) ?>');
            jQuery('#icl-estimated-quote-all').html('<?php echo (count($active_languages)-1) * 0.07 * count(explode(' ', $documents[$_GET['post_id']]->post_content)) ?>');            
    });
    </script>
    <?php else: ?>
    <form method="post" name="translation-dashboard-filter" action="admin.php?page=<?php echo ICL_PLUGIN_FOLDER; ?>/menu/content-translation.php">
    <table class="form-table widefat fixed" style="width:auto">
        <thead>
        <tr>
            <th scope="col" colspan="3"><strong><?php _e('Select which documents to display','sitepress')?></strong></th>
        </tr>
        </thead>        
        <tr valign="top">
            <th scope="row"><strong><?php echo __('Show documents in:', 'sitepress') ?></strong></th>
            <td colspan="2">
                <?php foreach($active_languages as $lang): ?>
                    <label><input type="radio" name="filter[lang]" value="<?php echo $lang['code'] ?>" <?php if($selected_language==$lang['code']): ?>checked="checked"<?php endif;?>/><?php echo $lang['display_name'] ?></label>&nbsp;&nbsp;
                <?php endforeach; ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><strong><?php echo __('Translation status:', 'sitepress') ?></strong>    </th>
            <td colspan="2">
                <select name="filter[tstatus]">
                    <?php
                        $option_status = array(
                                               'all' => __('All documents', 'sitepress'),
                                               'not' => __('Not translated or needs updating', 'sitepress'),
                                               'in_progress' => __('Translation in progress', 'sitepress'),
                                               'complete' => __('Translation complete', 'sitepress'));
                    ?>
                    <?php foreach($option_status as $k=>$v):?>
                    <option value="<?php echo $k ?>" <?php if($tstatus==$k):?>selected="selected"<?php endif?>><?php echo $v ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><strong><?php echo __('Filter furter by:', 'sitepress') ?></strong>    </th>
            <td>
                <label><input type="checkbox" name="filter[status_on]" <?php if(isset($icl_translation_filter['status_on'])):?>checked="checked"<?php endif?> />&nbsp;
                    Status:</label> 
                    <select name="filter[status]">
                        <?php foreach($icl_post_statuses as $k=>$v):?>
                        <option value="<?php echo $k ?>" <?php if(isset($icl_translation_filter['status_on']) && $icl_translation_filter['status']==$k):?>selected="selected"<?php endif?>><?php echo $v ?></option>
                        <?php endforeach; ?>
                    </select>
                &nbsp;&nbsp;    
                <label><input type="checkbox" name="filter[type_on]" <?php if(isset($icl_translation_filter['type_on'])):?>checked="checked"<?php endif?> />&nbsp;
                    Type:</label> 
                    <select name="filter[type]">
                        <?php foreach($icl_post_types as $k=>$v):?>
                        <option value="<?php echo $k ?>" <?php if(isset($icl_translation_filter['type_on']) && $icl_translation_filter['type']==$k):?>selected="selected"<?php endif?>><?php echo $v->labels->singular_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                                        
            </td>
            <td align="right"><input name="translation_dashboard_filter" class="button" type="submit" value="<?php echo __('Display','sitepress')?>" /></td>
        </tr>
    </table>
    </form>
    <br />
    <?php endif; ?>

    <table class="widefat fixed" id="icl-translation-dashboard" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" <?php if(isset($_GET['post_id'])) echo 'checked="checked"'?>/></th>
            <th scope="col"><?php echo __('Title', 'sitepress') ?></th>
            <th scope="col" class="manage-column column-date">
                <img title="<?php _e('Note for translators', 'sitepress') ?>" src="<?php echo ICL_PLUGIN_URL ?>/res/img/notes.png" alt="note" width="16" height="16" /></th>
            <th scope="col" class="manage-column column-date"><?php echo __('Type', 'sitepress') ?></th>
            <th scope="col" class="manage-column column-date"><?php echo __('Status', 'sitepress') ?></th>        
            <th scope="col" class="manage-column column-date"><?php echo __('Translation', 'sitepress') ?></th>        
        </tr>        
        </thead>
        <tfoot>
        <tr>
            <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" <?php if(isset($_GET['post_id'])) echo 'checked="checked"'?>/></th>
            <th scope="col"><?php echo __('Title', 'sitepress') ?></th>
            <th scope="col" class="manage-column column-date">
                <img title="<?php _e('Note for translators', 'sitepress') ?>" src="<?php echo ICL_PLUGIN_URL ?>/res/img/notes.png" alt="note" width="16" height="16" /></th>
            <th scope="col" class="manage-column column-date"><?php echo __('Type', 'sitepress') ?></th>
            <th scope="col" class="manage-column column-date"><?php echo __('Status', 'sitepress') ?></th>        
            <th scope="col" class="manage-column column-date"><?php echo __('Translation', 'sitepress') ?></th>        
        </tr>        
        </tfoot>                    
        <tbody>
            <?php if(!$documents): ?>
            <tr>
                <td scope="col" colspan="5" align="center"><?php echo __('No documents found', 'sitepress') ?></td>
            </tr>                
            <?php else: $oddcolumn = false; ?>
            <?php foreach($documents as $doc): $oddcolumn=!$oddcolumn; ?>
            <?php 
            if($doc->rid[0] != null){
                if(isset($doc->in_progress) && $doc->in_progress > 0){                        
                    $tr_status = __('In progress', 'sitepress');
                }elseif($doc->updated){                            
                    $tr_status = __('Needs update', 'sitepress');
                }else{
                    $tr_status = __('Complete', 'sitepress');
                }
            }else{
                $tr_status = __('Not Translated', 'sitepress');
            }
            
            ?>            
            <tr<?php if($oddcolumn): ?> class="alternate"<?php endif;?>>
                <td scope="col">
                    <input type="checkbox" value="<?php echo $doc->post_id ?>" name="post[]" <?php if(isset($_GET['post_id'])) echo 'checked="checked"'?> />                    
                </td>
                <td scope="col" class="post-title column-title">
                    <a href="<?php echo get_edit_post_link($doc->post_id) ?>"><?php echo $doc->post_title ?></a>
                    <?php
                        $wc = icl_estimate_word_count($doc, $selected_language);
                        $wc += icl_estimate_custom_field_word_count($doc->post_id, $selected_language);
                    ?>
                    <span id="icl-cw-<?php echo $doc->post_id ?>" style="display:none"><?php echo $wc; $wctotal+=$wc; ?></span>
                    <span class="icl-tr-details">&nbsp;</span>
                    <div class="icl_post_note" id="icl_post_note_<?php echo $doc->post_id ?>">
                        <?php 
                            if($wpdb->get_var("SELECT source_language_code FROM {$wpdb->prefix}icl_translations WHERE element_type='post_{$doc->post_type}' AND element_id={$doc->post_id}")){
                                $_is_translation = true;
                            }else{
                                $_is_translation = false;
                                $note = get_post_meta($doc->post_id, '_icl_translator_note', true); 
                                if($note){
                                    $note_text = __('Edit note for the translators', 'sitepress');
                                    $note_icon = 'edit_translation.png';
                                }else{
                                    $note_text = __('Add note for the translators', 'sitepress');
                                    $note_icon = 'add_translation.png';
                                }
                            }
                        ?>
                        <?php _e('Note for the translators', 'sitepress')?> 
                        <textarea rows="5"><?php echo $note ?></textarea> 
                        <table width="100%"><tr>
                        <td style="border-bottom:none">
                            <input type="button" class="icl_tn_clear button" 
                                value="<?php _e('Clear', 'sitepress')?>" <?php if(!$note): ?>disabled="disabled"<?php endif; ?> />        
                            <input class="icl_tn_post_id" type="hidden" value="<?php echo $doc->post_id ?>" />
                        </td>
                        <td align="right" style="border-bottom:none"><input type="button" class="icl_tn_save button-primary" value="<?php _e('Save', 'sitepress')?>" /></td>
                        </tr></table>
                    </div>
                </td>
                <td scope="col" class="icl_tn_link" id="icl_tn_link_<?php echo $doc->post_id ?>">
                    <?php if($_is_translation):?>
                    &nbsp;
                    <?php else: ?>
                    <a title="<?php echo $note_text ?>" href="#"><img src="<?php echo ICL_PLUGIN_URL ?>/res/img/<?php echo $note_icon ?>" width="16" height="16" /></a>
                    <?php endif; ?>
                </td>
                <td scope="col">
                    <?php echo $icl_post_types[$doc->post_type]->labels->singular_name; ?>
                    <input class="icl_td_post_type" name="icl_post_type[<?php echo $doc->post_id ?>]" type="hidden" value="<?php echo $doc->post_type ?>" />
                </td>
                <td scope="col"><?php echo $icl_post_statuses[$doc->post_status]; ?></td>
                <td scope="col" id="icl-tr-status-<?php echo $doc->post_id ?>">
                    <?php if($doc->rid[0]): ?>
                    <a href="#translation-details-<?php echo implode('-', $doc->rid) ; ?>" class="translation_details_but">
                    <?php endif; ?>
                    <?php echo $tr_status ?>
                    <?php if($doc->rid[0]): ?></a><?php endif; ?>
                </td>
            </tr>                            
            <?php endforeach;?>
            <?php endif;?>
        </tbody> 
    </table>
    <span id="icl-cw-total" style="display:none"><?php echo $wctotal; ?></span>    
    <div class="tablenav">
    <div style="float:left;margin-top:4px;"><strong><?php echo __('Translation Cost Estimate:', 'sitepress') ?></strong> 
        <?php printf(__('%s words, %s USD.', 'sitepress'), 
                '<span id="icl-estimated-words-count">0</span>', 
                '<strong><span id="icl-estimated-quote">0.00</span></strong>') 
              . '<span id="icl-estimated-all" style="display:none;"></span>' ?></div>
    <?php   
        $page_links = paginate_links( array(
            'base' => add_query_arg('paged', '%#%' ),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $wp_query->max_num_pages,
            'current' => $_GET['paged'],
            'add_args' => isset($icl_translation_filter)?$icl_translation_filter:array() 
        ));         
    ?>
    <?php if ( $page_links ) { ?>
    <div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'sitepress' ) . '</span>%s',
        number_format_i18n( ( $_GET['paged'] - 1 ) * $wp_query->query_vars['posts_per_page'] + 1 ),
        number_format_i18n( min( $_GET['paged'] * $wp_query->query_vars['posts_per_page'], $wp_query->found_posts ) ),
        number_format_i18n( $wp_query->found_posts ),
        $page_links
    ); echo $page_links_text; ?></div>
    <?php } ?>
    </div>
    
        <ul id="icl-tr-opt">
            <?php   
                if (isset($icl_lang_status)){
                    foreach($icl_lang_status as $lang){
                        if($lang['from'] == $selected_language) {
                            $target_status[$lang['to']] = !empty($lang['translators']);
                            $target_rate[$lang['to']] = $lang['max_rate'];
                        }
                    }
                }
                $translators_selected = false;
                $languages_without_translators = false;
                foreach($active_languages as $lang){
                    if($selected_language==$lang['code']) continue;  
                    if($target_status[$lang['code']]){
                        $translators_selected = true;
                    }else{
                        $languages_without_translators = true;
                    }
                } 
            ?>
            <?php foreach($active_languages as $lang): if($selected_language==$lang['code']) continue; ?>
                <?php 
                    if($target_status[$lang['code']]){
                        $disabled =  ''; 
                        $checked='checked="checked"';                        
                    }else{
                        $disabled =  ' disabled="disabled"'; 
                        $checked='';
                    }
                ?>
                <li>
                    <label>
                        <input type="checkbox" name="icl-tr-to-<?php echo $lang['code']?>" value="<?php echo $lang['english_name']?>"<?php echo $checked ?><?php echo $disabled ?> />&nbsp;
                        <?php printf(__('Translate to %s %s','sitepress'), $lang['display_name'], $sitepress->get_language_status_text($selected_language, $lang['code'])); ?><?php if(empty($target_status[$lang['code']])):?>*<?php endif; ?>
                    </label>
                    <input type="hidden" id="icl_tr_rate_<?php echo $lang['code'] ?>" value="<?php echo $target_rate[$lang['code']] ?>" />
                </li>
            <?php endforeach; ?>    
            
            <li>
                <input<?php if(!isset($_GET['post_id'])): ?> disabled="disabled"<?php endif; ?> type="submit" class="button-primary" id="icl-tr-sel-doc" value="<?php 
                    echo __('Translate selected documents', 'sitepress') ?>" />
                <span class="icl_ajx_response" id="icl_ajx_response_td"><?php 
                    echo __('Sending translation requests. Please wait!', 'sitepress') ?>&nbsp;<img src="<?php echo ICL_PLUGIN_URL ?>/res/img/ajax-loader.gif" alt="" /></span>
            </li>
        </ul>
        
        <span id="icl_message_1" style="display:none"><?php echo __('All documents sent to translation', 'sitepress')?></span>
        <span id="icl_message_error" style="display:none"><?php echo __('Error sending some documents to translation', 'sitepress')?></span>
        <span id="icl_message_2" style="display:none"><?php echo __('Translation in progress', 'sitepress')?></span>
        
        <?php if($languages_without_translators):?>
        <p>
        *&nbsp;<?php _e('You need to select translators before sending document to translation.', 'sitepress'); ?>
        <?php 
        echo $sitepress->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=wp-about-translators&iso='.
            $sitepress->get_locale($sitepress->get_admin_language()).'&src='.get_option('home'), 
            array(
                'ar'=>1, 
                'title'=>__('About translators', 'sitepress'),
                'unload_cb' => 'icl_prevent_tb_reload'
            )
        )  . __('Learn more.', 'sitepress') ?></a>
        </p>
        <?php endif; ?>        