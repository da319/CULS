<?php $this->noscript_notice() ?>
<p style="float:left;">
<?php printf(__('Language of this %s', 'sitepress'), strtolower($wp_post_types[$post->post_type]->singular_label)); ?>&nbsp;
<select name="icl_post_language" id="icl_post_language">
<?php foreach($active_languages as $lang):?>
<?php if(isset($translations[$lang['code']]->element_id) && $translations[$lang['code']]->element_id != $post->ID) continue ?>
<option value="<?php echo $lang['code'] ?>" <?php if($selected_language==$lang['code']): ?>selected="selected"<?php endif;?>><?php echo $lang['display_name'] ?>&nbsp;</option>
<?php endforeach; ?>
</select> 

<input type="hidden" name="icl_trid" value="<?php echo $trid ?>" />

<?php /*<input type="hidden" name="icl_is_page" value="<?php echo $is_page ?>" /> */?>

</p>

<div id="translation_of_wrap">
    <?php if($selected_language != $default_language || (isset($_GET['lang']) && $_GET['lang']!=$default_language)): ?>
        <div style="clear:both;font-size:1px">&nbsp;</div>
        
        <p style="float:left;">
        <?php echo __('This is a translation of', 'sitepress') ?>&nbsp;
        <select name="icl_translation_of" id="icl_translation_of"<?php if($_GET['action'] != 'edit' && $trid) echo ' disabled="disabled"';?>>
            <?php if($source_language == null || $source_language == $default_language): ?>
                <?php if($trid): ?>
                    <option value="none"><?php echo __('--None--', 'sitepress') ?></option>                    
                    <?php
                        //get source
                        $src_language_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid={$trid} AND language_code='{$default_language}'");                        
                        if(!$src_language_id) {
                            // select the first id found for this trid
                            $src_language_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid={$trid}");
                        }                                                      
                        if($src_language_id && $src_language_id != $post->ID) {
                            $src_language_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = {$src_language_id}");                            
                        }
                    ?>
                    <?php if($src_language_title && !isset($_GET['icl_ajx'])): ?>
                        <option value="<?php echo $src_language_id ?>" selected="selected"><?php echo $src_language_title ?>&nbsp;</option>
                    <?php endif; ?>
                <?php else: ?>
                    <option value="none" selected="selected"><?php echo __('--None--', 'sitepress') ?></option>
                <?php endif; ?>
                <?php foreach($untranslated as $translation_of_id => $translation_of_title):?>
                    <?php if ($translation_of_id != $src_language_id): ?>
                        <option value="<?php echo $translation_of_id ?>"><?php echo $translation_of_title ?>&nbsp;</option>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if($trid): ?>
                    <?php
                        // add the source language
                        $src_language_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid={$trid} AND language_code='{$source_language}'");
                        if($src_language_id) {
                            $src_language_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = {$src_language_id}");
                        }
                    ?>
                    <?php if($src_language_title): ?>
                        <option value="<?php echo $src_language_id ?>" selected="selected"><?php echo $src_language_title ?></option>
                    <?php endif; ?>
                <?php else: ?>
                    <option value="none" selected="selected"><?php echo __('--None--', 'sitepress') ?></option>
                <?php endif; ?>
            <?php endif; ?>
        </select>

        </p>
    <?php endif; ?>
</div><!--//translation_of_wrap-->

<div style="clear:both;font-size:1px">&nbsp;</div>

<?php if($_GET['action'] == 'edit' && $trid): ?>       
<?php 
    $translations_count = count($translations) - 1;
    $language_count = count($active_languages) - 1;        
    
    // get languages with translators
    $languages_translated = $languages_not_translated = array();
    foreach((array)$this->settings['icl_lang_status'] as $k=>$language_pair){
        if(!is_numeric($k)) continue;
        if($language_pair['from'] == $selected_language && !empty($language_pair['translators'])){
            $languages_translated[] = $language_pair['to'];
            $lang_rates[$language_pair['to']] = $language_pair['max_rate'];
        }
    }
    $languages_not_translated = array_diff(array_keys($active_languages), array_merge(array($selected_language), $languages_translated));
    
    // previous translations
    foreach($languages_translated as $l){
        $language_names[] = $active_languages[$l]['english_name'];    
    }    
    if(!empty($language_names)){
        $previous_rid = icl_get_request_ids_for_post($post->ID, $selected_language, apply_filters('icl_server_languages_map', $language_names));
    }
    if(!empty($previous_rid)){
        foreach($previous_rid as $serverlang => $pr){
            if(!empty($pr)){
                $_target_lang = $wpdb->get_var("SELECT code FROM {$wpdb->prefix}icl_languages WHERE english_name='".$wpdb->escape(apply_filters('icl_server_languages_map', $serverlang, true))."'");
                $status = $wpdb->get_var("SELECT status FROM {$wpdb->prefix}icl_core_status WHERE rid={$pr}");
                $was_translated = $wpdb->get_var("
                            SELECT cr.status 
                            FROM {$wpdb->prefix}icl_content_status cs 
                                JOIN {$wpdb->prefix}icl_core_status cr ON cr.rid = cs.rid 
                            WHERE 
                                
                                cr.origin ='{$selected_language}'
                                AND target = '{$_target_lang}'
                                AND cs.nid = {$post->ID} 
                                AND cr.status = ".CMS_TARGET_LANGUAGE_DONE."
                            LIMIT 1");
                if($was_translated){
                    $langs_done[apply_filters('icl_server_languages_map', $serverlang, true)] = 1;
                }         
                if(!is_null($status) && $status != CMS_TARGET_LANGUAGE_DONE){
                    // translation is still in progress for one or more languages.
                    $langs_in_progress[apply_filters('icl_server_languages_map', $serverlang, true)] = 1;
                }
                
                // needs update ?
                if($wpdb->get_var("SELECT n.md5<>c.md5 FROM {$wpdb->prefix}icl_node n JOIN {$wpdb->prefix}icl_content_status c ON n.nid = c.nid WHERE n.nid={$post->ID} AND c.rid={$pr}")){
                    $langs_need_update[apply_filters('icl_server_languages_map', $serverlang, true)] = 1;
                }
            }
        }        
    }
    ?>
    <div class="icl_cyan_box">
    <div class="clear" style="font-size: 0px">&nbsp;</div>    
    <a id="icl_pt_hide" href="#" style="float:right;<?php if($this->settings['hide_professional_translation_controls']):?>display:none;<?php endif; ?>"><?php _e('hide', 'sitepress') ?></a>
    <a id="icl_pt_show" href="#" style="float:right;<?php if(!$this->settings['hide_professional_translation_controls']):?>display:none;<?php endif; ?>"><?php _e('show', 'sitepress') ?></a>    
    <strong><?php _e('Professional translation', 'sitepress'); ?></strong>    
    <div id="icl_pt_controls" <?php if($this->settings['hide_professional_translation_controls']):?>style="display:none;"<?php endif; ?>>
    <?php 
        if(!empty($languages_translated)){ 
            echo '<ul>';
            foreach($languages_translated as $lang){
                if(isset($langs_in_progress[$active_languages[$lang]['english_name']]) || 
                    ($langs_done[$active_languages[$lang]['english_name']] && !isset($langs_need_update[$active_languages[$lang]['english_name']]))){
                    $disabled = ' disabled="disabled"';
                }else{
                    $disabled = '';
                }
                echo '<li><label>';
                echo '<input type="hidden" id="icl_pt_rate_'.$lang.'" value="'.$lang_rates[$lang].'" />';
                echo '<input type="checkbox" id="icl_pt_to_'.$lang.'" value="'.$active_languages[$lang]['english_name'].'"'.$disabled.'/>&nbsp;';
                if(isset($langs_done[$active_languages[$lang]['english_name']])){
                    printf(__('Update %s translation', 'sitepress'), $active_languages[$lang]['display_name']);
                }else{
                    printf(__('Translate to %s', 'sitepress'), $active_languages[$lang]['display_name']);
                }
                if(isset($langs_in_progress[$active_languages[$lang]['english_name']])){
                    echo '&nbsp;<small>('.__('in progress', 'sitepress').')</small>';
                }elseif($langs_done[$active_languages[$lang]['english_name']] && !isset($langs_need_update[$active_languages[$lang]['english_name']])){
                    echo '&nbsp;<small>('.__('up to date', 'sitepress').')</small>';
                }
                echo '</label></li>';
            }    
            echo '</ul>';
        }
        if(!empty($languages_not_translated)){ 
            echo '<ul>';
            foreach($languages_not_translated as $lang){
                echo '<li>'.$this->create_icl_popup_link("@select-translators;{$selected_language};{$lang}@", 
                    array(
                        'ar'=>1, 
                        'title'=>__('Select translators', 'sitepress'),
                        'unload_cb' => 'icl_pt_reload_translation_box'
                    )
                ); // <a> included
                printf(__('Get %s translators', 'sitepress'), $active_languages[$lang]['display_name']);
                echo '</a></li>';
            }    
            echo '</ul>';            
        }
        if(!empty($languages_translated)){
            $note = trim(get_post_meta($post->ID, '_icl_translator_note', true));
        }
    ?>
    <?php if(!empty($languages_translated)): ?>
    <div id="icl_post_add_notes">
        <h4><a href="#"><?php _e('Note for the translators', 'sitepress')?></a></h4>
        <div id="icl_post_note">
            <textarea id="icl_pt_tn_note" name="icl_tn_note" rows="5"><?php echo $note ?></textarea> 
            <table width="100%"><tr>
            <td><input id="icl_tn_clear" type="button" class="button" value="<?php _e('Clear', 'sitepress')?>" <?php if(!$note): ?>disabled="disabled"<?php endif; ?> /></td>            <td align="right"><input id="icl_tn_save"  type="button" class="button-primary" value="<?php _e('Close', 'sitepress')?>" /></td>
            </tr></table>
            <input id="icl_tn_cancel_confirm" type="hidden" value="<?php _e('Your changes to the note for the translators are not saved.', 'sitepress') ?>" />
        </div>
        <div id="icl_tn_not_saved"><?php _e('Note not saved yet', 'sitepress'); ?></div>
    </div>    

    <div style="text-align: right;margin:0 5px 5px 0;"><?php printf(__('Cost: %s USD', 'sitepress'), '<span id="icl_pt_cost_estimate">0.00</span>', ' href="#"');?></div>
    <input type="hidden" id="icl_pt_wc" value="<?php echo icl_estimate_word_count($post, $selected_language) + icl_estimate_custom_field_word_count($post->ID, $selected_language) ?>" />

    <input type="hidden" id="icl_pt_post_id" value="<?php echo $post->ID ?>" />
    <input type="hidden" id="icl_pt_post_type" value="<?php echo $post->post_type ?>" />
    <input type="button" disabled="disabled" id="icl_pt_send" class="button-primary alignright" value="<?php echo esc_html(__('Send to translation', 'sitepress')) ?>"/>
    <br clear="all" />
    <?php else:?>
    <?php 
        $estimated_cost = sprintf("%.2f", (icl_estimate_word_count($post, $selected_language) + icl_estimate_custom_field_word_count($post->ID, $selected_language)) * 0.07);
    ?>
    <div style="text-align: right;margin:0 5px 5px 0;white-space:nowrap;">
    <?php printf( __('Estimated cost: %s USD', 'sitepress'), $estimated_cost);?><br />
    (<?php echo $this->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=cms-cost-estimate&iso='.
        $this->get_locale($this->get_admin_language()).'&src='.get_option('home'), 
        array(
            'ar'=>1, 
            'title'=>__('Cost estimate', 'sitepress'),
        )
    ) 
        . __('why estimated?', 'sitepress');?></a>)
    </div>
    
    <br />
    <p><b><?php echo $this->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=moreinfo-wp&iso='.
        $this->get_locale($this->get_admin_language()).'&src='.get_option('home'), 
        array('title' => __('About Our Translators', 'sitepress'), 'ar' => 1)) ?><?php _e('About Our Translators', 'sitepress'); ?></a></b></p>
    <p><?php _e('ICanLocalize offers expert translators at competitive rates.', 'sitepress'); ?></p>
    <p><?php echo $this->create_icl_popup_link('http://www.icanlocalize.com/destinations/go?name=moreinfo-wp&iso='.
        $this->get_locale($this->get_admin_language()).'&src='.get_option('home'), 
        array('title' => __('About Our Translators', 'sitepress'), 'ar' => 1)) ?><?php _e('Learn more', 'sitepress'); ?></a></p>
    
    <?php endif; ?>
    
    </div>
    
    <div id="icl_pt_error" class="icl_error_text" style="display: none;margin-top: 4px;"><?php _e('Failed sending to translation.', 'sitepress') ?></div>    
    <?php if(isset($_GET['icl_message']) && $_GET['icl_message']=='success'):?>
    <div id="icl_pt_success" class="icl_valid_text" style="margin-top: 8px;"><?php _e('Sent to translation.', 'sitepress') ?></div>    
    <?php endif; ?>
    </div>    
    
    <?php do_action('icl_post_languages_options_before', $post->ID);?>

    <div id="icl_translate_options">
    <?php
        // count number of translated and un-translated pages.
        $translations_found = 0;
        $untranslated_found = 0;
        foreach($active_languages as $lang) {
            if($selected_language==$lang['code']) continue;
            if(isset($translations[$lang['code']]->element_id)) {
                $translations_found += 1;
            } else {
                $untranslated_found += 1;
            }
        }
    ?>
    
    <?php if($untranslated_found > 0): ?>    
        <?php if($this->get_icl_translation_enabled()):?>
            <p style="clear:both;"><b><?php _e('or, translate manually:', 'sitepress'); ?> </b>
        <?php else: ?>
            <p style="clear:both;"><b><?php _e('Translate yourself', 'sitepress'); ?></b>
        <?php endif; ?>
        <table>
        <?php foreach($active_languages as $lang): if($selected_language==$lang['code']) continue; ?>
        <tr>
            <?php if(!isset($translations[$lang['code']]->element_id)):?>
                <td><?php echo $lang['display_name'] ?></td>
                <?php
                    $add_link = get_option('siteurl') . "/wp-admin/post-new.php?post_type={$post->post_type}&trid=" . $trid . "&lang=" . $lang['code'] . "&source_lang=" . $selected_language;
                ?>
                <td><a href="<?php echo $add_link?>"><?php echo __('add','sitepress') ?></a></td>
            <?php endif; ?>        
        </tr>
        <?php endforeach; ?>
        </table>
        </p>
    <?php endif; ?>
    <?php if($translations_found > 0): ?>    
        <p style="clear:both;">
            <b><?php _e('Translations', 'sitepress') ?></b> 
            (<a class="icl_toggle_show_translations" href="#" <?php if(!$this->settings['show_translations_flag']):?>style="display:none;"<?php endif;?>><?php _e('hide','sitepress')?></a><a class="icl_toggle_show_translations" href="#" <?php if($this->settings['show_translations_flag']):?>style="display:none;"<?php endif;?>><?php _e('show','sitepress')?></a>)                
        <table width="97%" cellspacing="1" id="icl_translations_table" <?php if(!$this->settings['show_translations_flag']):?>style="display:none;"<?php endif;?>>        
        <?php foreach($active_languages as $lang): if($selected_language==$lang['code']) continue; ?>
        <tr>
            <?php if(isset($translations[$lang['code']]->element_id)):?>
                <td><?php echo $lang['display_name'] ?></td>
                <td align="right" width="20%"><?php echo isset($translations[$lang['code']]->post_title)?'<a href="'.get_edit_post_link($translations[$lang['code']]->element_id).'" title="'.__('Edit','sitepress').'">'.apply_filters('the_title', __('edit','sitepress')).'</a>':__('n/a','sitepress') ?></td>
                
            <?php endif; ?>        
        </tr>
        <?php endforeach; ?>
        </table>
        
        <?php if($this->get_icl_translation_enabled()):?>
            <p style="clear:both;"><b><?php echo __('ICanlocalize translation status:', 'sitepress') ?></b> (<a href="javascript:;" 
            onclick="jQuery('#icl_translations_status').toggle();jQuery('#noupdate_but').toggle();if(jQuery(this).html()=='<?php echo __('hide','sitepress')?>') jQuery(this).html('<?php echo __('show','sitepress')?>'); else jQuery(this).html('<?php echo __('hide','sitepress')?>')"><?php echo __('show','sitepress')?></a>)</p>

            <?php icl_display_post_translation_status($post->ID, &$post_translation_statuses, true); ?>
            <table width="100%" id="icl_translations_status" style="display:none;">
            
            <?php foreach($active_languages as $lang): if($selected_language==$lang['code']) continue; ?>
            <tr>
                <?php if(isset($translations[$lang['code']]->element_id)):?>
                    <td><?php echo $lang['display_name'] ?></td>
                    <td class="icl_translation_status_msg">
                    <?php echo isset($post_translation_statuses[$lang['code']]) ? $post_translation_statuses[$lang['code']] : __('Not translated','sitepress'); ?>
                    </td>
                    
                <?php endif; ?>        
            </tr>
            <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        
        
    <?php endif; ?>
    
    <br clear="all" style="line-height:1px;" />
    </div>
<?php endif; ?>

<?php if($this->get_icl_translation_enabled() 
        && !$wpdb->get_var("SELECT source_language_code FROM {$wpdb->prefix}icl_translations WHERE element_type='post' AND element_id={$post->ID}") 
        && !isset($_GET['source_lang'])):?>
<?php endif; ?>

<?php do_action('icl_post_languages_options_after') ?>
