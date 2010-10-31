<?php
$iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);       

switch($_REQUEST['icl_ajx_req']){
    case 'get_translation_details':
        $rids = explode("-", $_REQUEST['rid']);
        sort($rids);
        ?>
        <table class="widefat fixed">
        <thead>
        <tr>
            <th scope="col"><?php echo __('Language', 'sitepress') ?></th>
            <th scope="col"><?php echo __('Status', 'sitepress') ?></th>        
            <th scope="col"><?php echo __('Translator', 'sitepress') ?></th>        
        </tr>  
        </thead>
        <?php
        $language_status = array();
        foreach($rids as $rid) {
            $details = $iclq->cms_request_translations($rid);
            $upload = $details['cms_uploads']['cms_upload'];
            $target_languages = $details['cms_target_languages']['cms_target_language'];
            if ($target_languages != null){
                // HACK: If we only have one target language then the $target_languages
                // array no longer has an array of languages but returns just the target language
                if(!isset($target_languages[0])){
                    $target = $target_languages;
                    $target_languages = array(0 => $target);
                }                                
                foreach($target_languages as $l){
                    $lang_server = $l['attr']['language'];
                    $lang = apply_filters('icl_server_languages_map', $lang_server, true);
                    $lang_loc = $wpdb->get_var("
                        SELECT lt.name FROM {$wpdb->prefix}icl_languages_translations lt 
                        JOIN {$wpdb->prefix}icl_languages l ON lt.language_code=l.code 
                        WHERE lt.display_language_code='" . $sitepress->get_default_language(). "' AND lt.name='{$lang}'");
                    if(!$lang_loc){
                        $lang_loc = $lang;
                    }
                    $language_status[$lang]['lang'] = $lang_loc;
                    $language_status[$lang]['words'] = sprintf(__('Job size: %s words', 'sitepress'), number_format($l['attr']['word_count']));
                    $language_status[$lang]['status'] = icl_decode_translation_status_id($l['attr']['status']);
                    if($l['translator']['attr']['id']){
                        $language_status[$lang]['translator'] = $sitepress->create_icl_popup_link(ICL_API_ENDPOINT .'/websites/'.$iclq->setting('site_id').'/cms_requests/'.$rid.'/chat?lang='.str_replace(' ','%20',$lang_server), array('title'=>'ICanLocalize')).$l['translator']['attr']['nickname'].'</a>';
                    } else {
                        $language_status[$lang]['translator'] = __('None assigned', 'sitepress');
                    }
                    $language_status[$lang]['project'] = $sitepress->create_icl_popup_link(ICL_API_ENDPOINT.'/websites/'.$iclq->setting('site_id').'/cms_requests/'.$rid, array('title'=>'ICanLocalize')) . __('Project page on ICanLocalize.com', 'sitepress').'</a>';
                    $language_status[$lang]['sent'] = sprintf(__('Sent for translation: %s', 'sitepress'), date('m/d/Y H:i', $details['attr']['created_at']));
                }
            }
        }
        ?>
        <?php foreach ($language_status as $data): ?>
            <tr>
                <td>
                    <?php echo $data['lang']?>
                </td>
                <td>
                    <?php echo $data['status']?>
                </td>
                <td>
                    <?php echo $data['translator']?>
                </td>
            </tr>
            
            <tr>
                <td colspan=3>
                    <?php echo $data['project']?><br />
                    <?php echo $data['sent']?><br />
                    <?php echo $data['words']?>
                </td>
            </tr>

        <?php endforeach; ?>
        </table>
        <?php

        break;
}

exit;
?>
