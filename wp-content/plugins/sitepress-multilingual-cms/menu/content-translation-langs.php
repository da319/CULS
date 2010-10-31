        <?php if(!$sitepress_settings['content_translation_languages_setup']): ?>        
            <form id="icl_language_pairs_form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
            <?php wp_nonce_field('icl_language_pairs_form','icl_language_pairs_formnounce') ?>
            
            <?php
                // see if any languages are selected
                $selected_count = 0;
                foreach ($active_languages as $lang) {
                    if ($sitepress->get_icl_translation_enabled($lang['code'])) {
                        $selected_count += 1;
                    }
                }
                if ($selected_count == 0) {
                    $enable_default = true;
                }
            ?>
            <?php endif; ?>
                
            
                                    
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Translation pairs', 'sitepress') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php if($sitepress_settings['content_translation_languages_setup']): ?>        
                                <form id="icl_language_pairs_form" name="icl_language_pairs_form" action="">
                                <?php
                                    $enable_default = false;
                                    $lang_to_style = 'float:left;list-style:none;width:100%;';
                                ?>
                            <?php else: $lang_to_style = 'float:left;list-style:none;width:30%;';?>
                            <?php endif; ?>

                                <?php $show_enabled_first = array(true, false) ?>
                                <?php foreach($show_enabled_first as $show_enabled): ?>
                                    <?php if($show_enabled): ?>
                                        <div id="icl_languages_enabled" >
                                        <ul class="icl_language_pairs">
                                    <?php else: ?>
                                        <p style="clear:both;"><a href="#icl-show_disabled_langs"><span><?php _e('Show more translation pairs &raquo;','sitepress') ?></span><span style="display:none;"><?php _e('&laquo; Hide additional languages','sitepress') ?></span></a></p>
                                        <div id="icl_languages_disabled" style="display:none;">
                                        <ul class="icl_language_pairs">
                                    <?php endif; ?>
                                    <?php 
                                        if($sitepress_settings['st']['strings_language']){
                                            $active_languages[$sitepress_settings['st']['strings_language']] = $sitepress->get_language_details($sitepress_settings['st']['strings_language']);
                                        }
                                        
                                    ?>
                                    <?php foreach($active_languages as $lang): ?>                                                    
                                        <?php
                                            $enabled = $sitepress->get_icl_translation_enabled($lang['code']);
                                            if ($enable_default && $lang['code'] == $default_language){
                                                $enabled = true;
                                            }
                                        ?>
                                        <?php if(($show_enabled && ($enabled || $lang['code'] == $default_language)) || (!$show_enabled && !($enabled || $lang['code'] == $default_language))): ?>
                                            <li style="float:left;width:98%;">
                                                <?php
                                                    $set_check = $enabled;
                                                ?>
                                                <label><input class="icl_tr_from" type="checkbox" name="icl_lng_from_<?php echo $lang['code']?>" id="icl_lng_from_<?php echo $lang['code']?>" <?php if($set_check): ?>checked="checked"<?php endif?> />
                                                <?php printf(__('Translate from %s to these languages','sitepress'), $lang['display_name']) ?></label>
                                                <ul id="icl_tr_pair_sub_<?php echo $lang['code'] ?>" <?php if(!$enabled): ?>style="display:none"<?php endif?>>
                                                <?php foreach($active_languages as $langto): if($lang['code']==$langto['code']) continue; ?>        
                                                    <?php 
                                                    if($langto['code'] == $sitepress_settings['st']['strings_language'] 
                                                        && !in_array($sitepress_settings['st']['strings_language'], array_keys($sitepress->get_active_languages()))) continue;
                                                    ?>
                                                    <li style="<?php echo $lang_to_style?>">
                                                        <label><input class="icl_tr_to" type="checkbox" name="icl_lng_to_<?php echo $lang['code']?>_<?php echo $langto['code']?>" id="icl_lng_from_<?php echo $lang['code']?>_<?php echo $langto['code']?>" <?php if($sitepress->get_icl_translation_enabled($lang['code'],$langto['code'])): ?>checked="checked"<?php endif?> />
                                                            <?php echo $langto['display_name'] . ' '?>
                                                            <span class="icl-tr-not-avail-to" id="icl_lng_from_status_<?php echo $lang['code']?>_<?php echo $langto['code']?>">
                                                                <?php echo $sitepress->get_language_status_text($lang['code'], $langto['code']) ?>
                                                            </span>
                                                        </label>
                                                    </li>    
                                                <?php endforeach; ?>
                                                </ul>
                                            </li>
                                            
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </ul>
                                    </div>
                                <?php endforeach; ?>

                            <?php if($sitepress_settings['content_translation_languages_setup']): ?>        
                                <input id="icl_save_language_pairs" type="button" class="button-secondary action" value="<?php echo __('Save', 'sitepress') ?>" />
                                <span class="icl_ajx_response" id="icl_ajx_response"></span>
                                </form>
                            <?php endif; ?>

                        </td>
                    </tr>
                </tbody>
            </table>
                         
        <?php if(!$sitepress_settings['content_translation_languages_setup']): ?>        
            <br />   
            <div style="text-align:right">
                <input class="button" name="icl_content_trans_setup_cancel" value="<?php echo __('Cancel', 'sitepress') ?>" type="button" />
                <input class="button-primary" name="icl_content_trans_setup_next_1" disabled="disabled" value="<?php echo __('Next', 'sitepress') ?>" type="submit" />
            </div>
            </form>
        <?php endif; ?>
                            
