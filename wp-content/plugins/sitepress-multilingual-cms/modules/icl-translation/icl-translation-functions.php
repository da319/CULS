<?php

function icl_translation_js(){
    wp_enqueue_script('icl-translation-scripts', ICL_PLUGIN_URL . '/modules/icl-translation/js/icl-translation.js', array(), '0.1');
}

function icl_get_request_ids_for_post($post_id, $source_language, $target_languages){
    global $sitepress;
    
    foreach((array)$target_languages as $target){
        $target_code = $sitepress->get_language_code($target);
        
        $rid[$target] = icl_get_latest_request_id($post_id, $source_language, $target_code);
        
    }
    
    return $rid;
    
}

function icl_initialize_db(){
    include_once ICL_PLUGIN_PATH . '/modules/icl-translation/db-scheme.php';
}

function icl_get_latest_request_id($post_id, $source_language, $target_code){
    global $wpdb;
    
    $sql = "SELECT content.rid FROM
                {$wpdb->prefix}icl_content_status content
            JOIN 
               {$wpdb->prefix}icl_core_status core
            ON
               content.rid = core.rid
            WHERE
               content.nid = {$post_id} AND
               core.origin = '{$source_language}' AND
               core.target = '{$target_code}'
            ORDER BY rid DESC
            LIMIT 1
            ";
            
    return $wpdb->get_var($sql);
}

function icl_get_all_languages_associated($post_id, $source_language, $rid) {
    global $wpdb;
    
    $languages = $wpdb->get_col("SELECT target FROM {$wpdb->prefix}icl_core_status WHERE rid={$rid}");
                                                
    // check other request_ids that include these languages.

    $other_rids = array();    
    foreach($languages as $target_code) {
        $sql = "SELECT content.rid FROM
                    {$wpdb->prefix}icl_content_status content
                JOIN 
                   {$wpdb->prefix}icl_core_status core
                ON
                   content.rid = core.rid
                WHERE
                   content.nid = {$post_id} AND
                   core.origin = '{$source_language}' AND
                   core.target = '{$target_code}'
                ";
                
        foreach($wpdb->get_col($sql) as $id){
            if (!in_array($id, $other_rids)){
                $other_rids[] = $id;
            }
        }
    }
    foreach($other_rids as $id){
        foreach($wpdb->get_col("SELECT target FROM {$wpdb->prefix}icl_core_status WHERE rid={$id}") as $other_lang){
            if (!in_array($other_lang, $languages)){
                $languages[] = $other_lang;
            }
        }
        
    }
    
    return $languages;
    
}

function icl_translation_send_post($post_id, $target_languages, $post_type='post'){
    global $sitepress_settings, $wpdb, $sitepress;
    $post = get_post($post_id);
    
    if(!$post){
        return false;
    }
    
    icl_translation_save_md5($post_id); // make sure the md5 is up to date.
    $post_md5 = $wpdb->get_var("SELECT md5 FROM {$wpdb->prefix}icl_node WHERE nid=" . $post_id);
    

    $source_lang = $sitepress->get_language_for_element($post_id, 'post_' . $post_type);
    
    // get the previous request ids for this node and these langauges.
    $previous_rid = icl_get_request_ids_for_post($post_id,
                                                    $source_lang,
                                                    $target_languages);
    $targets_available = array();
            
    foreach($target_languages as $target){
        // Make sure the previous request is complete.
        $available = true;
        
        $rid = $previous_rid[$target];
        if (isset($previous_rid[$target])){
            // check to make sure this is the latest $rid
            // get all languages for this rid
            $langs = icl_get_all_languages_associated($post_id, $source_lang, $rid);
            
            // see if we have any later rids for any language.
            foreach($langs as $lang){
                $test_rid = icl_get_latest_request_id($post_id,
                                                     $source_lang,
                                                     $lang);
                $status = $wpdb->get_col("SELECT status FROM {$wpdb->prefix}icl_core_status WHERE rid={$test_rid}");
                foreach($status as $state){
                    if($state != CMS_TARGET_LANGUAGE_DONE){
                        // translation is still in progress for one or more languages.
                        $available = false;
                    }
                }
            }
            if ($available){
                // check md5 is different.
                $request_md5 = $wpdb->get_var("SELECT md5 FROM {$wpdb->prefix}icl_content_status WHERE rid={$rid}");
                if($request_md5 == $post_md5){
                    $available = false;
                }
            }
        }
        if ($available){
            if (isset($previous_rid[$target])){
                $targets_available[$rid][] = $target;
            } else {
                $targets_available[] = array($target);
            }
        }
        
    }
            
    if (sizeof($targets_available) == 0){
        return false;
    }
    
    $target_languages = $targets_available;
  
    $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);
    
    //$post_url       = get_permalink($post_id);
    if($post->post_type=='page'){
        $post_url       = get_option('home') . '?page_id=' . ($post_id);
    }else{
        $post_url       = get_option('home') . '?p=' . ($post_id);
    }
    /*
    }else{
        global $wp_post_types;
        $post_url = get_option('home') . '?' . $wp_post_types[$post_type]->query_var . '=' . ($post->post_name);
    }
    */
    
    $orig_lang = $wpdb->get_var("
        SELECT l.english_name 
        FROM {$wpdb->prefix}icl_translations t 
        JOIN {$wpdb->prefix}icl_languages l ON t.language_code=l.code 
        WHERE t.element_id={$post_id} AND t.element_type='post_{$post_type}'"
        );
    
    $orig_lang_for_server = apply_filters('icl_server_languages_map', $orig_lang);
            
    /*if($post_type=='post'){*/
        foreach(wp_get_object_terms($post_id, 'post_tag') as $tag){
            $post_tags[$tag->term_taxonomy_id] = $tag->name;
        }   
        
        if(is_array($post_tags)){
            //only send tags that don't have a translation
            foreach($post_tags as $term_taxonomy_id=>$pc){
                $trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id='{$term_taxonomy_id}' AND element_type='tax_post_tag'");
                foreach($target_languages as $lang){
                    $lang = $lang[0]; // get the languag name (string)
                    $not_translated = false;
                    if($trid != $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations t JOIN {$wpdb->prefix}icl_languages l ON l.code = t.language_code WHERE l.english_name='{$lang}' AND trid='{$trid}'")){
                        $not_translated = true;
                        break;
                    }                
                }
                if($not_translated){
                    $tags_to_translate[$term_taxonomy_id] = $pc; 
                }            
            }              
            sort($post_tags, SORT_STRING);
        } 
               
        foreach(wp_get_object_terms($post_id, 'category') as $cat){
            $post_categories[$cat->term_taxonomy_id] = $cat->name;
        }      
                      
        if(is_array($post_categories)){
            //only send categories that don't have a translation
            foreach($post_categories as $term_taxonomy_id=>$pc){
                $trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id='{$term_taxonomy_id}' AND element_type='tax_category'");
                foreach($target_languages as $lang){
                    $lang = $lang[0]; // get the languag name (string)
                    $not_translated = false;
                    if($trid != $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations t JOIN {$wpdb->prefix}icl_languages l ON l.code = t.language_code WHERE l.english_name='{$lang}' AND trid='{$trid}'")){
                        $not_translated = true;
                        break;
                    }                
                }
                if($not_translated){
                    $categories_to_translate[$term_taxonomy_id] = $pc; 
                }            
            }  
            sort($post_categories, SORT_STRING);
        }
        
        // get custom taxonomies
        $taxonomies = $wpdb->get_col("
            SELECT DISTINCT tx.taxonomy 
            FROM {$wpdb->term_taxonomy} tx JOIN {$wpdb->term_relationships} tr ON tx.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = {$post_id}
        ");
        foreach($taxonomies as $t){
            if($sitepress_settings['taxonomies_sync_option'][$t] == 1){
                $object_terms = $wpdb->get_results("
                    SELECT x.term_taxonomy_id, t.name 
                    FROM {$wpdb->terms} t 
                        JOIN {$wpdb->term_taxonomy} x ON t.term_id=x.term_id
                        JOIN {$wpdb->term_relationships} r ON x.term_taxonomy_id = r.term_taxonomy_id
                    WHERE x.taxonomy = '{$t}' AND r.object_id = $post_id
                    ");
                foreach($object_terms as $trm){
                    $trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations 
                        WHERE element_id='{$trm->term_taxonomy_id}' AND element_type='tax_{$t}'");
                    foreach($target_languages as $lang){
                        $lang = $lang[0]; // get the languag name (string)
                        $not_translated = false;
                        if($trid != $wpdb->get_var("
                                SELECT trid FROM {$wpdb->prefix}icl_translations t 
                                JOIN {$wpdb->prefix}icl_languages l ON l.code = t.language_code WHERE l.english_name='{$lang}' AND trid='{$trid}'
                        ")){
                            $not_translated = true;
                            break;
                        }                
                    }
                    if($not_translated){
                        $taxonomies_to_translate[$t][$trm->term_taxonomy_id] = $trm->name; 
                    }            
                }      
            }
        }
    /*}*/
    $timestamp = date('Y-m-d H:i:s');
    
    $md5 = icl_translation_calculate_md5($post_id);    
    
    
    // send off to each language/s as a separate cms_request
    // $target_languages is an array of groups of languages to be sent together
    // if there is no previous cms_request it will be something like:
    // array(array("Spanish), array("German"), array("French"))
    // if there was a previous cms_request then some languages may be grouped.
    // array(array("Spanish", "German"), array("French"))
        
    
    foreach($target_languages as $target){
        $target_for_server = apply_filters('icl_server_languages_map', $target); //filter some language names to match the names on the server
        $data = array(
            'url'=>htmlentities($post_url), 
            'contents'=>array(
                'title' => array(
                    'translate'=>1,
                    'data'=>base64_encode($post->post_title),
                    'format'=>'base64'
                ),
                'body' => array(
                    'translate'=>1,
                    'data'=>base64_encode($post->post_content),
                    'format'=>'base64'
                ),
                'original_id' => array(
                    'translate'=>0,
                    'data'=>$post_id
                ),
                            
            ),
            'target_languages' => $target_for_server
        );

        include_once ICL_PLUGIN_PATH . '/inc/plugins-texts-functions.php';
        
        $custom_fields = icl_get_posts_translatable_fields();
        foreach($custom_fields as $id => $cf){
            if ($cf->translate) {
                $custom_fields_value = get_post_meta($post_id, $cf->attribute_name, true);
                if ($custom_fields_value != '') {
                    $data['contents']['field-'.$id] = array(
                        'translate' => 1,
                        'data' => base64_encode($custom_fields_value),
                        'format' => 'base64',
                    );
                    $data['contents']['field-'.$id.'-name'] = array(
                        'translate' => 0,
                        'data' => $cf->attribute_name,
                    );
                    $data['contents']['field-'.$id.'-type'] = array(
                        'translate' => 0,
                        'data' => $cf->attribute_type,
                    );
                    $data['contents']['field-'.$id.'-plugin'] = array(
                        'translate' => 0,
                        'data' => $cf->plugin_name,
                    );
                }
            }
        }
                
                

        
        /*if($post_type=='post'){*/
            if(is_array($categories_to_translate)){
                $data['contents']['categories'] = array(
                        'translate'=>1,
                        'data'=> implode(',', array_map(create_function('$e', 'return \'"\'.base64_encode($e).\'"\';'), $categories_to_translate)),
                        'format'=>'csv_base64'
                    );    
                $data['contents']['category_ids'] = array(
                        'translate'=>0,
                        'data'=> implode(',', array_keys($categories_to_translate)),
                        'format'=>''
                    );                
            }
            
            if(is_array($tags_to_translate)){
                $data['contents']['tags'] = array(
                        'translate'=>1,
                        'data'=> implode(',', array_map(create_function('$e', 'return \'"\'.base64_encode($e).\'"\';'), $tags_to_translate)),
                        'format'=>'csv_base64'
                    );                
                $data['contents']['tag_ids'] = array(
                        'translate'=>0,
                        'data'=> implode(',', array_keys($tags_to_translate)),
                        'format'=>''
                    );                            
            }
            
            if(is_array($taxonomies_to_translate)){
                foreach($taxonomies_to_translate as $k=>$v){
                    $data['contents'][$k] = array(
                            'translate'=>1,
                            'data'=> implode(',', array_map(create_function('$e', 'return \'"\'.base64_encode($e).\'"\';'), $v)),
                            'format'=>'csv_base64'
                        );                
                    $data['contents'][$k.'_ids'] = array(
                            'translate'=>0,
                            'data'=> implode(',', array_keys($v)),
                            'format'=>''
                        );                            
                }
            }
        /*}*/
        $previous_rid_for_target = $previous_rid[$target[0]];
        if ($previous_rid_for_target == null){
            $previous_rid_for_target = false;
        }
        
        if($post->post_status=='publish'){
            $permlink = $post_url;
        }else{
            $permlink = false;
        }
        
        $note = get_post_meta($post_id, '_icl_translator_note', true);
        
        $xml = $iclq->build_cms_request_xml($data, $orig_lang_for_server, $previous_rid_for_target);
        
        $res = $iclq->send_request($xml, $post->post_title, $target_for_server, $orig_lang_for_server, $permlink, $note);
        
        if($res > 0){
            $wpdb->insert($wpdb->prefix.'icl_content_status', array('rid'=>$res, 'nid'=>$post_id, 'timestamp'=>$timestamp, 'md5'=>$md5)); //insert rid   
    
            foreach($target as $targ_lang){
                $wpdb->insert($wpdb->prefix.'icl_core_status', array('rid'=>$res,
                                                                     'origin'=>$sitepress->get_language_code($orig_lang),
                                                                     'target'=>$sitepress->get_language_code($targ_lang),
                                                                     'status'=>CMS_REQUEST_WAITING_FOR_PROJECT_CREATION));
            }
            
            $ret = $res;  
                  
        }else{
            // sending to translation failed
            $ret = 0;
            
        }
    }
    return $ret;
    
}

function icl_translation_save_md5($p){
    global $wpdb;
    if($_POST['autosave']) return;
    if($_POST['action']=='post-quickpress-publish'){
        $post_id = $p;            
        $_POST['post_type']='post';
    }elseif(isset($_POST['post_ID'])){
        $post_id = $_POST['post_ID'];
    }else{
        $post_id = $p;
    }
    
    $md5 = icl_translation_calculate_md5($post_id);    
    
    if($wpdb->get_var("SELECT nid FROM {$wpdb->prefix}icl_node WHERE nid='{$post_id}'")){
        $wpdb->update($wpdb->prefix . 'icl_node', array('md5'=>$md5), array('nid'=>$post_id));
    }else{
        $wpdb->insert($wpdb->prefix . 'icl_node', array('nid'=>$post_id, 'md5'=>$md5));
    }

    // minor edit - update the current cms_request md5
    if($_POST['icl_minor_edit']){
        if($wpdb->get_var("SELECT nid FROM {$wpdb->prefix}icl_content_status WHERE nid='{$p}'")){
            $wpdb->update($wpdb->prefix . 'icl_content_status', array('md5'=>$md5), array('nid'=>$p));
        }
    } 
    
}

function icl_translation_calculate_md5($post_id){
    $post = get_post($post_id);
    $post_type = $post->post_type;
    
    if($post_type=='post'){
        foreach(wp_get_object_terms($post_id, 'post_tag') as $tag){
            $post_tags[] = $tag->name;
        }
        if(is_array($post_tags)){
            sort($post_tags, SORT_STRING);
        }        
        foreach(wp_get_object_terms($post_id, 'category') as $cat){
            $post_categories[] = $cat->name;
        }    
        if(is_array($post_categories)){
            sort($post_categories, SORT_STRING);
        }
        
        global $wpdb, $sitepress_settings;
        // get custom taxonomies
        $taxonomies = $wpdb->get_col("
            SELECT DISTINCT tx.taxonomy 
            FROM {$wpdb->term_taxonomy} tx JOIN {$wpdb->term_relationships} tr ON tx.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = {$post_id}
        ");
        sort($taxonomies, SORT_STRING);
        foreach($taxonomies as $t){
            if($sitepress_settings['taxonomies_sync_option'][$t] == 1){
                $taxs = array();
                foreach(wp_get_object_terms($post_id, $t) as $trm){
                    $taxs[] = $trm->name;
                }
                if($taxs){
                    sort($taxs,SORT_STRING);
                    $all_taxs[] = '['.$t.']:'.join(',',$taxs);
                }
            }
        }
    }
    
    include_once ICL_PLUGIN_PATH . '/inc/plugins-texts-functions.php';

    $custom_fields = icl_get_posts_translatable_fields();
    $custom_fields_values = array();
    foreach($custom_fields as $cf){
        if ($cf->translate) {
            $custom_fields_values[] = get_post_meta($post_id, $cf->attribute_name, true);
        }
    }
    
    $md5str =         
        $post->post_title . ';' . 
        $post->post_content . ';' . 
        join(',',(array)$post_tags).';' . 
        join(',',(array)$post_categories) . ';' . 
        join(',', $custom_fields_values);
    if(!empty($all_taxs)){
        $md5str .= ';' . join(';', $all_taxs);
    }    
    $md5 = md5($md5str);
                
    return $md5;
}

function icl_translation_get_documents($lang,
                                       $tstatus,
                                       $status=false,
                                       $type=false,
                                       $limit = 20,
                                       $from_date = false,
                                       $to_date = false){
    global $wpdb, $wp_query, $sitepress;
    
    $where = "WHERE 1";
    $order = "ORDER BY p.post_date DESC";
    
    if(isset($_GET['post_id'])){ // this overrides the others
        $where .= " AND p.ID=" . (int)$_GET['post_id'];  
    }else{
        if ($tstatus == 'in_progress' or $tstatus == 'complete') {
            $where .= " AND (c.rid IS NOT NULL)";
            $order = "ORDER BY c.rid DESC";
        }
        
        $t_el_types = array_keys($sitepress->get_translatable_documents());
        if($type){
            $where .= " AND p.post_type = '{$type}'";
            $icl_el_type_where = " AND t.element_type = 'post_{$type}'";
        }else{
            $where .= " AND p.post_type IN ('".join("','",$t_el_types)."')";
            foreach($t_el_types as $k=>$v){
                $t_el_types[$k] = 'post_' . $v;
            }
            $icl_el_type_where .= " AND t.element_type IN ('".join("','",$t_el_types)."')";
        }  
        
        if($status){
            $where .= " AND p.post_status = '{$status}'";
        }        
        
        $where .= " AND t.language_code='{$lang}'";
        
        if($from_date and $to_date){
            $where .= " AND p.post_date > '{$from_date}' AND p.post_date < '{$to_date}'";
        }
    }
        
    if(!isset($_GET['paged'])) $_GET['paged'] = 1;
    $offset = ($_GET['paged']-1)*$limit;
    
    // exclude trashed posts
    $where .= " AND p.post_status <> 'trash'";
    
    $sql = "
        SELECT SQL_CALC_FOUND_ROWS p.ID as post_id, p.post_title, p.post_type, p.post_status, post_content, 
            c.rid,
            cs.target,
            n.md5<>c.md5 AS updated
        FROM {$wpdb->posts} p
            JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id {$icl_el_type_where}
            LEFT JOIN {$wpdb->prefix}icl_node n ON p.ID = n.nid
            LEFT JOIN {$wpdb->prefix}icl_content_status c ON c.nid=p.ID
            LEFT JOIN {$wpdb->prefix}icl_core_status cs ON cs.rid = c.rid
        {$where}                
        {$order} 
    ";    
    $results = $wpdb->get_results($sql);    
        
    // only use the latest rids for each language.
    
    $latested_per_language = array();
    foreach($results as $r){
        $key = $r->post_id.$r->target;
        if(isset($latested_per_language[$key])) {
            // keep the latest rid for a language
            if ($r->rid > $latested_per_language[$key]->rid) {
                $latested_per_language[$key] = $r;
            }
        } else {
            $latested_per_language[$key] = $r;
        }
    }
    
    $results = $latested_per_language;
    
    $pids = array(0);
    foreach($results as $r){
        $pids[] = $r->post_id;
    }
    
    $sql = "
        SELECT p.ID as post_id, COUNT(r.rid) AS inprogress_count 
        FROM {$wpdb->posts} p
            JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id {$icl_el_type_where}
            LEFT JOIN {$wpdb->prefix}icl_content_status c ON c.nid=p.ID
            LEFT JOIN {$wpdb->prefix}icl_core_status r ON c.rid = r.rid
            WHERE p.ID IN (".join(',', $pids).")
            AND status <> ".CMS_TARGET_LANGUAGE_DONE."
            GROUP BY (r.rid) HAVING inprogress_count > 0 
        ORDER BY p.post_date DESC 
    ";
    
    $in_progress = $wpdb->get_results($sql);

    $count = 0;
    $nodes_processed = array();
    foreach($results as $r){
        $post_ok = false;
        if ($tstatus == 'in_progress'){
            foreach ($in_progress as $item){
                if($item->post_id == $r->post_id){
                    $post_ok = true;
                    break;
                }
            }
        } else if ($tstatus == 'complete'){
            $found = false;
            foreach ($in_progress as $item){
                if($item->post_id == $r->post_id){
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $post_ok = true;
            }
    
        } else if ($tstatus == 'not'){
            // check for posts that haven't been translated before or are updated.
            if($r->rid == null or $r->updated){
                $post_ok = true;
            }
        } else {
            $post_ok = true;
        }
        if($post_ok){
            if ($count >= $offset and $count < $offset + $limit){
                if (isset($documents[$r->post_id])){
                    $documents[$r->post_id]->rid[] = $r->rid;
                    if (!isset($documents[$r->post_id]->updated)) {
                        $documents[$r->post_id]->updated = $r->updated;
                    } else {
                        if(!$documents[$r->post_id]->updated) { // make sure we don't reset the updated status
                            $documents[$r->post_id]->updated = $r->updated;
                        }
                    }
                } else {
                    $documents[$r->post_id] = $r;
                    $documents[$r->post_id]->rid = array($r->rid);
                }
            }
            if (!in_array($r->post_id, $nodes_processed)){
                $count++;
                $nodes_processed[] = $r->post_id;
            }
        }
    }
    
    foreach($in_progress as $v){
        if(isset($documents[$v->post_id])){
            $documents[$v->post_id]->in_progress = $v->inprogress_count;
        }
    }

    $wp_query->found_posts = $count;
    $wp_query->query_vars['posts_per_page'] = $limit;
    $wp_query->max_num_pages = ceil($wp_query->found_posts/$limit);
      
    return $documents;
    
}

function icl_translation_delete_post($post_id){
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->prefix}icl_node WHERE nid=".$post_id);
    $rid = $wpdb->get_var("SELECT rid FROM {$wpdb->prefix}icl_content_status WHERE nid=".$post_id);
    $wpdb->query("DELETE FROM {$wpdb->prefix}icl_content_status WHERE nid=".$post_id);
    if($rid){
        $wpdb->query("DELETE FROM {$wpdb->prefix}icl_core_status WHERE rid=".$rid);
    }
}

function icl_add_post_translation($trid, $translation, $lang, $rid){
    global $wpdb, $sitepress_settings, $sitepress, $wp_taxonomies;
    $taxonomies = array_diff(array_keys((array)$wp_taxonomies), array('post_tag','category'));
    $lang_code = $wpdb->get_var("SELECT code FROM {$wpdb->prefix}icl_languages WHERE english_name='".$wpdb->escape($lang)."'");
    if(!$lang_code){        
        return false;
    }

    $original_post_details = $wpdb->get_row("
        SELECT p.post_author, p.post_type, p.post_status, p.comment_status, p.ping_status, p.post_parent, p.menu_order, t.language_code
        FROM {$wpdb->prefix}icl_translations t 
        JOIN {$wpdb->posts} p ON t.element_id = p.ID AND CONCAT('post_',p.post_type) = t.element_type
        WHERE trid='{$trid}' AND p.ID = '{$translation['original_id']}'
    ");
    
    //is the original post a sticky post?
    remove_filter('option_sticky_posts', array($sitepress,'option_sticky_posts')); // remove filter used to get language relevant stickies. get them all
    $sticky_posts = get_option('sticky_posts');
    $is_original_sticky = $original_post_details->post_type=='post' && in_array($translation['original_id'], $sticky_posts);
    
    _icl_content_fix_image_paths_in_body($translation);
    _icl_content_fix_relative_link_paths_in_body($translation);
    _icl_content_decode_shortcodes($translation);
        
    /*if($original_post_details->post_type=='post'){*/
        
        // deal with tags
        if(isset($translation['tags'])){
            $translated_tags = $translation['tags'];   
            $translated_tag_ids = explode(',', $translation['tag_ids']);
            foreach($translated_tags as $k=>$v){
                $tag_trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id='{$translated_tag_ids[$k]}' AND element_type='tax_post_tag'");
                
                // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang                                        
                // same term name exists in a different language?
                $term_different_language = $wpdb->get_var("
                    SELECT tm.term_id 
                    FROM {$wpdb->term_taxonomy} tx
                        JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                        JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id
                    WHERE tm.name='".$wpdb->escape($v)."' AND tr.element_type LIKE 'tax\\_%' AND tr.language_code <> '{$lang_code}'
                ");
                if($term_different_language){
                    $v .= ' @'.$lang_code;    
                }
                
                //tag exists? (in the current language)
                $term_taxonomy_id = $wpdb->get_var("
                    SELECT term_taxonomy_id 
                    FROM {$wpdb->term_taxonomy} tx 
                        JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                        JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id AND tr.element_type = 'tax_post_tag' AND tr.language_code = '{$lang_code}'
                    WHERE tm.name='".$wpdb->escape($v)."' OR tm.name='".$wpdb->escape($v)." @{$lang_code}' AND taxonomy='post_tag'");
                if(!$term_taxonomy_id){                                          
                    $tmp = wp_insert_term($v, 'post_tag');
                    if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_post_tag','element_id'=>$tmp['term_taxonomy_id']));
                    }
                }else{
                    
                    // check whether we have an orphan translation - the same trid and language but a different element id                                                     
                    $__translation_id = $wpdb->get_var("
                        SELECT translation_id FROM {$wpdb->prefix}icl_translations 
                        WHERE   trid = '{$tag_trid}' 
                            AND language_code = '{$lang_code}' 
                            AND element_id <> '{$term_taxonomy_id}'
                    ");
                    if($__translation_id){
                        $wpdb->query("DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id={$__translation_id}");    
                    }
                    
                    $tag_translation_id = $wpdb->get_var("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE element_id={$term_taxonomy_id} AND element_type='tax_post_tag'");                        
                    if($tag_translation_id){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_post_tag','translation_id'=>$tag_translation_id));                
                    }else{                                                
                        $wpdb->insert($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'element_type'=>'tax_post_tag', 'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                
                    }
                }        
            }
        }
        
        foreach(wp_get_object_terms($translation['original_id'] , 'post_tag') as $t){
            $original_post_tags[] = $t->term_taxonomy_id;
        }    
        if($original_post_tags){
            $tag_trids = $wpdb->get_col("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_post_tag' AND element_id IN (".join(',',$original_post_tags).")");    
            $tag_tr_tts = $wpdb->get_col("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_post_tag' AND language_code='{$lang_code}' AND trid IN (".join(',',$tag_trids).")");    
            $translated_tags = $wpdb->get_col("SELECT t.name FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tx ON tx.term_id = t.term_id WHERE tx.taxonomy='post_tag' AND tx.term_taxonomy_id IN (".join(',',$tag_tr_tts).")");
        }
                                          
        // deal with categories
        if(isset($translation['categories'])){
            $translated_cats = $translation['categories'];   
            $translated_cats_ids = explode(',', $translation['category_ids']);    
            foreach($translated_cats as $k=>$v){
                $cat_trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id='{$translated_cats_ids[$k]}' AND element_type='tax_category'");
                
                // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang                                        
                // same term name exists in a different language?
                $term_different_language = $wpdb->get_var("
                    SELECT tm.term_id 
                    FROM {$wpdb->term_taxonomy} tx
                        JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                        JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id
                    WHERE tm.name='".$wpdb->escape($v)."' AND tr.element_type LIKE 'tax\\_%' AND tr.language_code <> '{$lang_code}'
                ");
                if($term_different_language){
                    $v .= ' @'.$lang_code;    
                }
                
                //cat exists?
                $term_taxonomy_id = $wpdb->get_var("
                    SELECT term_taxonomy_id 
                    FROM {$wpdb->term_taxonomy} tx 
                        JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                        JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id AND tr.element_type = 'tax_category' AND tr.language_code = '{$lang_code}'
                        WHERE tm.name='".$wpdb->escape($v)."' OR tm.name='".$wpdb->escape($v)." @{$lang_code}' AND taxonomy='category'");
                if(!$term_taxonomy_id){  
                    // get original category parent id
                    $original_category_parent_id = $wpdb->get_var("SELECT parent FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id=".$translated_cats_ids[$k]);
                    if($original_category_parent_id){                        
                        $original_category_parent_id = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='category' AND term_id=".$original_category_parent_id);
                        $category_parent_trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_category' AND element_id=".$original_category_parent_id); 
                        // get id of the translated category parent
                        $category_parent_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE language_code='{$lang_code}' AND trid=".$category_parent_trid); 
                        if($category_parent_id){
                            $category_parent_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='category' AND term_taxonomy_id=".$category_parent_id);
                        }                        
                    }else{
                        $category_parent_id = 0;
                    }
                    $tmp = wp_insert_term($v, 'category', array('parent'=>$category_parent_id));
                    if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_category','element_id'=>$tmp['term_taxonomy_id']));
                    }
                }else{
                    
                    // check whether we have an orphan translation - the same trid and language but a different element id                                                     
                    $__translation_id = $wpdb->get_var("
                        SELECT translation_id FROM {$wpdb->prefix}icl_translations 
                        WHERE   trid = '{$cat_trid}' 
                            AND language_code = '{$lang_code}' 
                            AND element_id <> '{$term_taxonomy_id}'
                    ");
                    if($__translation_id){
                        $wpdb->query("DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id={$__translation_id}");    
                    }
                    
                    $cat_translation_id = $wpdb->get_var("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE element_id={$term_taxonomy_id} AND element_type='tax_category'");    
                    if($cat_translation_id){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_category','translation_id'=>$cat_translation_id));                
                    }else{
                        $wpdb->insert($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'element_type'=>'tax_category', 'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                
                    }            
                }        
            }
        }
        $original_post_cats = array();    
        foreach(wp_get_object_terms($translation['original_id'] , 'category') as $t){
            $original_post_cats[] = $t->term_taxonomy_id;
        }
        if($original_post_cats){    
            $cat_trids = $wpdb->get_col("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_category' AND element_id IN (".join(',',$original_post_cats).")");
            $cat_tr_tts = $wpdb->get_col("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_category' AND language_code='{$lang_code}' AND trid IN (".join(',',$cat_trids).")");
            $translated_cats_ids = $wpdb->get_col("SELECT t.term_id FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tx ON tx.term_id = t.term_id WHERE tx.taxonomy='category' AND tx.term_taxonomy_id IN (".join(',',$cat_tr_tts).")");
        }   
                
        // deal with custom taxonomies        
        if(!empty($sitepress_settings['taxonomies_sync_option'])){
            foreach($sitepress_settings['taxonomies_sync_option'] as $taxonomy=>$value){
                if($value == 1 && isset($translation[$taxonomy])){
                    $translated_taxs[$taxonomy] = $translation[$taxonomy];   
                    $translated_tax_ids[$taxonomy] = explode(',', $translation[$taxonomy.'_ids']);
                    foreach($translated_taxs[$taxonomy] as $k=>$v){
                        $tax_trid = $wpdb->get_var("
                                SELECT trid FROM {$wpdb->prefix}icl_translations 
                                WHERE element_id='{$translated_tax_ids[$taxonomy][$k]}' AND element_type='tax_{$taxonomy}'");
                        // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang
                        // same term name exists in a different language?                        
                        $term_different_language = $wpdb->get_var("
                                SELECT tm.term_id 
                                FROM {$wpdb->term_taxonomy} tx
                                    JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                                    JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id
                                WHERE tm.name='".$wpdb->escape($v)."' AND tr.element_type LIKE 'tax\\_%' AND tr.language_code <> '{$lang_code}'
                            ");
                        if($term_different_language){
                            $v .= ' @'.$lang_code;    
                        }
                            
                        //tax exists? (in the current language)
                        $term_taxonomy_id = $wpdb->get_var("
                                SELECT term_taxonomy_id 
                                FROM {$wpdb->term_taxonomy} tx 
                                    JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id 
                                    JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id 
                                        AND tr.element_type = 'tax_{$taxonomy}' AND tr.language_code = '{$lang_code}'
                                WHERE tm.name='".$wpdb->escape($v)."' OR tm.name='".$wpdb->escape($v)." @{$lang_code}' AND taxonomy='{$taxonomy}'");
                        if(!$term_taxonomy_id){                                          
                            $tmp = wp_insert_term($v, $taxonomy);                            
                            if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                                $wpdb->update($wpdb->prefix.'icl_translations', 
                                        array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'source_language_code'=>$original_post_details->language_code), 
                                        array('element_type'=>'tax_'.$taxonomy,'element_id'=>$tmp['term_taxonomy_id']));
                                }
                            }else{
                                // check whether we have an orphan translation - the same trid and language but a different element id                             
                                $__translation_id = $wpdb->get_var("
                                    SELECT translation_id FROM {$wpdb->prefix}icl_translations 
                                    WHERE   trid = '{$tax_trid}' 
                                        AND language_code = '{$lang_code}' 
                                        AND element_id <> '{$term_taxonomy_id}'
                                ");
                                if($__translation_id){
                                    $wpdb->query("DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id={$__translation_id}");    
                                }
                                
                                $tax_translation_id = $wpdb->get_var("
                                    SELECT translation_id FROM {$wpdb->prefix}icl_translations 
                                    WHERE element_id={$term_taxonomy_id} AND element_type='tax_{$taxonomy}'");                        
                                if($tax_translation_id){
                                    $wpdb->update($wpdb->prefix.'icl_translations', 
                                        array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'source_language_code'=>$original_post_details->language_code), 
                                        array('element_type'=>'tax_'.$taxonomy,'translation_id'=>$tax_translation_id));                
                                }else{                                                
                                    $wpdb->insert($wpdb->prefix.'icl_translations', 
                                        array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'element_type'=>'tax_'.$taxonomy, 
                                            'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                                      }
                            }        
                        }
                    }
                    
                    foreach(wp_get_object_terms($translation['original_id'] , $taxonomy) as $t){
                        $original_post_taxs[$taxonomy][] = $t->term_taxonomy_id;
                    }    
                    if($original_post_taxs[$taxonomy]){
                        $tax_trids = $wpdb->get_col("SELECT trid FROM {$wpdb->prefix}icl_translations 
                            WHERE element_type='tax_{$taxonomy}' AND element_id IN (".join(',',$original_post_taxs[$taxonomy]).")");    
                        $tax_tr_tts = $wpdb->get_col("SELECT element_id FROM {$wpdb->prefix}icl_translations 
                            WHERE element_type='tax_{$taxonomy}' AND language_code='{$lang_code}' AND trid IN (".join(',',$tax_trids).")");    
                        if($wp_taxonomies[$taxonomy]->hierarchical){
                            $translated_tax_ids[$taxonomy] = $wpdb->get_col("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id IN (".join(',',$tax_tr_tts).")");
                        }else{
                            $translated_taxs[$taxonomy] = $wpdb->get_col("SELECT t.name FROM {$wpdb->terms} t 
                                JOIN {$wpdb->term_taxonomy} tx ON tx.term_id = t.term_id 
                                WHERE tx.taxonomy='{$taxonomy}' AND tx.term_taxonomy_id IN (".join(',',$tax_tr_tts).")");                    
                        }
                }
            }
        }
                     
    /*}elseif($original_post_details->post_type=='page'){*/
    
        // handle the page parent and set it to the translated parent if we have one.
        if($original_post_details->post_parent){
            $post_parent_trid = $wpdb->get_var("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='post_{$original_post_details->post_type}' AND element_id='{$original_post_details->post_parent}'");
            if($post_parent_trid){
                $parent_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='post_{$original_post_details->post_type}' AND trid='{$post_parent_trid}' AND language_code='{$lang_code}'");
            }            
        }
                
    /*}*/
    
    // determine post id based on trid
    $post_id = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='post_{$original_post_details->post_type}' AND trid='{$trid}' AND language_code='{$lang_code}'");
    if($post_id){
        // see if the post really exists - make sure it wasn't deleted while the plugin was 
        if(!$wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE ID={$post_id}")){
            $is_update = false;
            $wpdb->query("DELETE FROM {$wpdb->prefix}icl_translations WHERE element_type='post_{$original_post_details->post_type}' AND element_id={$post_id}");
        }else{
            $is_update = true;
            $postarr['ID'] = $_POST['post_ID'] = $post_id;
        }
    }else{
        $is_update = false;
    } 
    
    $postarr['post_title'] = $translation['title'];
    $postarr['post_content'] = $translation['body'];
    if(is_array($translated_tags)){
        $postarr['tags_input'] = join(',',(array)$translated_tags);
    }
    if(is_array($translated_taxs)){
        foreach($translated_taxs as $taxonomy=>$values){
            $postarr['tax_input'][$taxonomy] = join(',',(array)$values);
        }
    } 
    if(is_array($translated_tax_ids)){
        $postarr['tax_input'] = $translated_tax_ids;
    }           
    if(isset($translated_cats_ids)){
        $postarr['post_category'] = $translated_cats_ids;        
    }
    $postarr['post_author'] = $original_post_details->post_author;  
    $postarr['post_type'] = $original_post_details->post_type;
    if($sitepress_settings['sync_comment_status']){
        $postarr['comment_status'] = $original_post_details->comment_status;
    }
    if($sitepress_settings['sync_ping_status']){
        $postarr['ping_status'] = $original_post_details->ping_status;
    }
    if($sitepress_settings['sync_page_ordering']){
        $postarr['menu_order'] = $original_post_details->menu_order;
    }
    if($sitepress_settings['sync_private_flag'] && $original_post_details->post_status=='private'){    
        $postarr['post_status'] = 'private';
    }
    if(!$is_update){
        $postarr['post_status'] = !$sitepress_settings['translated_document_status'] ? 'draft' : $original_post_details->post_status;
    } else {
        // set post_status to the current post status.
        $postarr['post_status'] = $wpdb->get_var("SELECT post_status FROM {$wpdb->prefix}posts WHERE ID = ".$post_id);
    }
    
    if(isset($parent_id) && $sitepress_settings['sync_page_parent']){
        $_POST['post_parent'] = $postarr['post_parent'] = $parent_id;  
        $_POST['parent_id'] = $postarr['parent_id'] = $parent_id;  
    }
    
    $_POST['trid'] = $trid;
    $_POST['lang'] = $lang_code;
    $_POST['skip_sitepress_actions'] = true;
    
    
    global $wp_rewrite;
    if(!isset($wp_rewrite)) $wp_rewrite = new WP_Rewrite();
        
    kses_remove_filters();
    $new_post_id = wp_insert_post($postarr);    
    
    // associate custom taxonomies by hand
    if ( !empty($postarr['tax_input']) ) {
        foreach ( $postarr['tax_input'] as $taxonomy => $tags ) {
            wp_set_post_terms( $new_post_id, $tags, $taxonomy );
        }
    }
    
    
    // set stickiness
    if($is_original_sticky && $sitepress_settings['sync_sticky_flag']){
        stick_post($new_post_id);
    }else{
        if($original_post_details->post_type=='post' && $is_update){
            unstick_post($new_post_id); //just in case - if this is an update and the original post stckiness has changed since the post was sent to translation
        }
    }
    
    //sync plugins texts
    require_once ICL_PLUGIN_PATH . '/inc/plugins-texts-functions.php';
    $fields_2_sync = icl_get_posts_translatable_fields(true);
    foreach($fields_2_sync as $f2s){
        update_post_meta($new_post_id, $f2s->attribute_name, get_post_meta($translation['original_id'],$f2s->attribute_name,true));
    }
    
    // set specific custom fields
    $copied_custom_fields = array('_top_nav_excluded', '_cms_nav_minihome');    
    foreach($copied_custom_fields as $ccf){
        $val = get_post_meta($translation['original_id'], $ccf, true);
        update_post_meta($new_post_id, $ccf, $val);
    }    
    
    // sync _wp_page_template
    if($sitepress_settings['sync_page_template']){
        $_wp_page_template = get_post_meta($translation['original_id'], '_wp_page_template', true);
        update_post_meta($new_post_id, '_wp_page_template', $_wp_page_template);
    }
    
    
    // set the translated custom fields if we have some.
    $custom_fields = icl_get_posts_translatable_fields();
    foreach($custom_fields as $id => $cf){
        if ($cf->translate) {
            $field_name = $cf->attribute_name;
            // find it in the translation
            foreach($translation as $name => $data) {
                if ($data == $field_name) {
                    if (preg_match("/field-(.*?)-name/", $name, $match)) {
                        $field_id = $match[1];
                        $field_translation = $translation['field-'.$field_id];
                        $field_type = $translation['field-'.$field_id.'-type'];
                        if ($field_type == 'custom_field') {
                            $field_translation = str_replace ( '&#0A;', "\n", $field_translation );
                            
                            // always decode html entities  eg decode &amp; to &
                            $field_translation = html_entity_decode($field_translation);
                            update_post_meta($new_post_id, $field_name, $field_translation);
                        }
                    }
                }
            }
        }
    }
    
    if(!$new_post_id){
        return false;
    }
        
    // record trids
    if(!$is_update){
        $wpdb->insert($wpdb->prefix.'icl_translations', 
            array(
                'element_type'=>'post_' . $original_post_details->post_type, 
                'element_id'=>$new_post_id, 
                'trid'=> $trid, 
                'language_code'=>$lang_code, 
                'source_language_code'=>$original_post_details->language_code
                )
        );
    }
    
    update_post_meta($new_post_id, '_icl_translation', 1);
    
    _icl_content_fix_links_to_translated_content($new_post_id, $lang_code, 'post');
    icl_st_fix_links_in_strings($new_post_id);
    
    // update translation status
    $wpdb->update($wpdb->prefix.'icl_core_status', array('status'=>CMS_TARGET_LANGUAGE_DONE), array('rid'=>$rid, 'target'=>$sitepress->get_language_code($lang)));
    // 
    
    // Now try to fix links in other translated content that may link to this post.
    $sql = "SELECT
                nid
            FROM
                {$wpdb->prefix}icl_node n
            JOIN
                {$wpdb->prefix}icl_translations t
            ON
                n.nid = t.element_id
            WHERE
                n.links_fixed = 0 AND t.element_type = 'post_{$original_post_details->post_type}' AND t.language_code = '{$lang_code}'";
                
    $needs_fixing = $wpdb->get_results($sql);
    foreach($needs_fixing as $id){
        if($id->nid != $new_post_id){ // fix all except the new_post_id. We have already done this.
            _icl_content_fix_links_to_translated_content($id->nid, $lang_code, 'post');
        }
    }
    
    // if this is a parent page then make sure it's children point to this.
    icl_fix_translated_children($translation['original_id'], $new_post_id, $lang_code);
    
    return true;
}

function icl_fix_translated_children($original_id, $translated_id, $lang_code){
    global $wpdb, $sitepress;

    // get the children of of original page.
    $original_children = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = {$original_id} AND post_type = 'page'");
    foreach($original_children as $original_child){
        // See if the child has a translation.
        $trid = $sitepress->get_element_trid($original_child);
        if($trid){
            $translations = $sitepress->get_element_translations($trid);
            if (isset($translations[$lang_code])){
                $current_parent = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = ".$translations[$lang_code]->element_id);
                if ($current_parent != $translated_id){
                    $wpdb->query("UPDATE {$wpdb->posts} SET post_parent={$translated_id} WHERE ID = ".$translations[$lang_code]->element_id);
                }
            }
        }
    }
}

function icl_fix_translated_parent($original_id, $translated_id, $lang_code){
    global $wpdb, $sitepress;

    $original_parent = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = {$original_id} AND post_type = 'page'");
    if ($original_parent){
        $trid = $sitepress->get_element_trid($original_parent);
        if($trid){
            $translations = $sitepress->get_element_translations($trid);
            if (isset($translations[$lang_code])){
                $current_parent = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = ".$translated_id);
                if ($current_parent != $translations[$lang_code]->element_id){
                    $wpdb->query("UPDATE {$wpdb->posts} SET post_parent={$translations[$lang_code]->element_id} WHERE ID = ".$translated_id);
                }
            }
        }
    }
}

function icl_process_translated_document($request_id, $language){
    global $sitepress_settings, $wpdb, $sitepress;
    $ret = false;
    $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);       
    $post_type = $wpdb->get_var($wpdb->prepare("SELECT p.post_type FROM {$wpdb->posts} p JOIN {$wpdb->prefix}icl_content_status c ON p.ID = c.nid WHERE c.rid=%d", $request_id));
    $trid = $wpdb->get_var($wpdb->prepare("
        SELECT trid 
        FROM {$wpdb->prefix}icl_translations t 
        JOIN {$wpdb->prefix}icl_content_status c ON t.element_id = c.nid AND t.element_type='post_{$post_type}' AND c.rid=%d",$request_id));
    $translation = $iclq->cms_do_download($request_id, $language);                           
    
    if($translation){
        if (icl_is_string_translation($translation)){
            $ret = icl_translation_add_string_translation($trid, $translation, apply_filters('icl_server_languages_map', $language, true), $request_id); //the 'reverse' language filter
        } else {
            $ret = icl_add_post_translation($trid, $translation, apply_filters('icl_server_languages_map', $language, true), $request_id); //the 'reverse' language filter
            if ($ret){
                $translations = $sitepress->get_element_translations($trid, 'post_'.$post_type);
                $iclq->report_back_permalink($request_id, $language, $translations[$sitepress->get_language_code(icl_server_languages_map($language, 1))]);
            }
        }
        if($ret){
            $iclq->cms_update_request_status($request_id, CMS_TARGET_LANGUAGE_DONE, $language);
        } 
        
    }        
    // if there aren't any other unfullfilled requests send a global 'done'               
    if(0 == $wpdb->get_var("SELECT COUNT(rid) FROM {$wpdb->prefix}icl_core_status WHERE rid='{$request_id}' AND status < ".CMS_TARGET_LANGUAGE_DONE)){
        $iclq->cms_update_request_status($request_id, CMS_REQUEST_DONE, false);
    }
    return $ret;
}

function icl_poll_for_translations(){
    global $wpdb, $sitepress_settings, $sitepress, $wp_rewrite;
    if(!isset($wp_rewrite)){
        require_once ABSPATH . WPINC . '/rewrite.php'; 
        $wp_rewrite = new WP_Rewrite();
    }
    
    $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);
    $pending_requests = $iclq->cms_requests();
    foreach($pending_requests as $pr){
        
        $cms_request_xml = $iclq->cms_request_translations($pr['id']);
        if(isset($cms_request_xml['cms_target_languages']['cms_target_language'])){
            $target_languages = $cms_request_xml['cms_target_languages']['cms_target_language'];
            // HACK: If we only have one target language then the $target_languages
            // array no longer has an array of languages but returns just the target language
            if(!isset($target_languages[0])){
                $target = $target_languages;
                $target_languages = array(0 => $target);
            }
            foreach($target_languages as $target){
                if(isset($target['attr'])){
                    $status = $target['attr']['status'];
                    $language = apply_filters('icl_server_languages_map', $target['attr']['language'], true); //reverse filter
                    $lang_code = $sitepress->get_language_code($language);
                    $wpdb->query("UPDATE {$wpdb->prefix}icl_core_status SET status='{$status}' WHERE rid='{$pr['id']}' AND target='{$lang_code}'");
                    
                }
                
            }
        }
        
        // process translated languages
        $tr_details = $wpdb->get_col("SELECT target FROM {$wpdb->prefix}icl_core_status WHERE rid=".$pr['id']." AND status = ".CMS_TARGET_LANGUAGE_TRANSLATED);
        foreach($tr_details as $language){
            $language = $sitepress->get_language_details($language);
            icl_process_translated_document($pr['id'],$language['english_name']);
        }
    }    
}

function _icl_translation_error_handler($errno, $errstr, $errfile, $errline){
    
    switch($errno){
        case E_ERROR:
        case E_USER_ERROR:
            throw new Exception ($errstr . ' [code:e' . $errno . '] in '. $errfile . ':' . $errline);
        case E_WARNING:
        case E_USER_WARNING:
            return true;                
            //throw new Exception ($errstr . ' [code:w' . $errno . '] in '. $errfile . ':' . $errline);    
        default: 
            return true;
    }
    
}

function _icl_throw_exception_for_mysql_errors(){
    global $EZSQL_ERROR, $sitepress_settings;
    if($sitepress_settings['troubleshooting_options']['raise_mysql_errors']){
        if(!empty($EZSQL_ERROR)){
            foreach($EZSQL_ERROR as $k=>$v){
                $mysql_errors[] = $v['error_str'] . ' [' . $v['query'] . ']';
            }
            throw new Exception(join("\n", $mysql_errors));
        }    
    }
}

function icl_add_custom_xmlrpc_methods($methods){
    $icl_methods['icanlocalize.set_translation_status'] = 'setTranslationStatus';
    $icl_methods['icanlocalize.list_posts'] = '_icl_list_posts';
    $icl_methods['icanlocalize.translate_post'] = '_icl_remote_control_translate_post';
    $icl_methods['icanlocalize.test_xmlrpc'] = '_icl_test_xmlrpc';
    $icl_methods['icanlocalize.cancel_translation'] = '_icl_xmlrpc_cancel_translation';
    $icl_methods['icanlocalize.notify_comment_translation'] =  '_icl_xmlrpc_add_message_translation';    
    
    $methods = $methods + $icl_methods;    
    if(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST){
        preg_match('#<methodName>([^<]+)</methodName>#i', $GLOBALS['HTTP_RAW_POST_DATA'], $matches);
        $method = $matches[1];            
        if(in_array($method, array_keys($icl_methods))){  
            error_reporting(E_NONE);
            //ini_set('display_errors', '0');        
            $old_error_handler = set_error_handler("_icl_translation_error_handler",E_ERROR|E_USER_ERROR);
        }
    }
    return $methods;
}

/*
 * The XML-RPC method to notify about translation status changes
 *
 * 0 – Unknown error
 * 1 – success
 * 2 – Signature failed
 * 3 – website_id incorrect
 * 4 – cms_request_id not found
 */
function _icl_xmlrpc_cancel_translation($args) {
    global $sitepress_settings, $sitepress, $wpdb;        
    $signature = $args[0];
    $website_id = $args[1];
    $request_id = $args[2];
    $accesskey = $sitepress_settings['access_key'];
    $checksum = $accesskey . $website_id . $request_id;
    if (sha1 ( $checksum ) == $signature) {
        $wid = $sitepress_settings['site_id'];
        if ($website_id == $wid) {
    
            $cms_request_info = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}icl_core_status WHERE rid={$request_id}");
            
            if (empty($cms_request_info)){
                return 4; // cms_request not found
            }
      
            // cms_request have been found.
            // delete it
    
            $wpdb->query("DELETE FROM {$wpdb->prefix}icl_core_status WHERE rid={$request_id}");
            $wpdb->query("DELETE FROM {$wpdb->prefix}icl_content_status WHERE rid={$request_id}");
            return 1;
        } else {
            return 3; // Website id incorrect
        }
    } else {
        return 2; // Signature failed
    }
  
    return 0; // Should not have got here - unknown error.
}

function _icl_test_xmlrpc($args){
    return true;
}

/*
 * 0 – Unknown error
 * 1 – success
 * 2 – Signature failed
 * 3 – website_id incorrect
 * 4 – cms_request_id not found
 * 5 - icl translation not enabled
 * 6 - unknown error processing translation
 */

function setTranslationStatus($args){
        global $sitepress_settings, $sitepress, $wpdb;        
        try{
            
            $signature   = $args[0];
            $site_id     = $args[1];
            $request_id  = $args[2];
            $original_language    = $args[3];
            $language    = $args[4];
            $status      = $args[5];
            $message     = $args[6];  

            if ($site_id != $sitepress_settings['site_id']) {
                return 3;                                                             
            }
            
            //check signature
            $signature_chk = sha1($sitepress_settings['access_key'].$sitepress_settings['site_id'].$request_id.$language.$status.$message);
            if($signature_chk != $signature){
                return 2;
            }
            
            $lang_code = $sitepress->get_language_code(apply_filters('icl_server_languages_map', $language, true));//the 'reverse' language filter 
            $cms_request_info = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}icl_core_status WHERE rid={$request_id} AND target='{$lang_code}'");
            
            if (empty($cms_request_info)){
                _icl_throw_exception_for_mysql_errors();
                return 4;
            }
                    
            if (icl_process_translated_document($request_id, $language) === true){
                _icl_throw_exception_for_mysql_errors();
                return 1;
            } else {
                _icl_throw_exception_for_mysql_errors();                
                return 6;
            }
            
        }catch(Exception $e) {
            return $e->getMessage();
        }

} 

function icl_get_post_translation_status($post_id){
    global $wpdb;
    
    $sql = "
        SELECT  c.rid, r.target, r.status, n.md5<>c.md5 AS updated
        FROM 
            {$wpdb->prefix}icl_content_status c
            JOIN {$wpdb->prefix}icl_core_status r ON c.rid = r.rid
            LEFT JOIN {$wpdb->prefix}icl_node n ON c.nid = n.nid
        WHERE c.nid = {$post_id}
    ";
    $status = $wpdb->get_results($sql);
    return $status;
}

function icl_display_post_translation_status($post_id, &$post_translation_statuses, $short = false){
    global $wpdb, $sitepress;                    
    $post_type = $wpdb->get_var("SELECT post_type FROM {$wpdb->posts} WHERE ID={$post_id}");                                                                                           
    $trid = $sitepress->get_element_trid($post_id, 'post_' . $post_type);
    $translations = $sitepress->get_element_translations($trid, 'post_' . $post_type);
    
    foreach($translations as $t){
        if($t->original){
            $original_updated = $wpdb->get_var("SELECT c.md5<>n.md5 FROM {$wpdb->prefix}icl_content_status c JOIN {$wpdb->prefix}icl_node n ON c.nid=n.nid WHERE c.nid=".$t->element_id);    
            break;
        }
    }    
    
    $active_languages = $sitepress->get_active_languages();    
    $_tr_status = icl_get_post_translation_status($post_id);    
    foreach((array)$_tr_status as $st){
        $tr_status[$st->target] = $st->status;
    }
    
    $tr_info = $wpdb->get_row("
        SELECT lt.name, t.language_code, t.source_language_code, t.trid 
        FROM {$wpdb->prefix}icl_translations t LEFT JOIN {$wpdb->prefix}icl_languages_translations lt ON t.source_language_code=lt.language_code
        WHERE t.element_type='post_{$post_type}' AND t.element_id={$post_id} AND lt.display_language_code = '".$sitepress->get_default_language()."'"
        );    
    // is ICL translation ?
    $icl_translation = get_post_meta($post_id,'_icl_translation',true); 
    if($icl_translation && $tr_info->name){
        echo '<div style="text-align:center;clear:both;">'. sprintf(__('Translated from %s','sitepress'),$tr_info->name).'</div>';
        echo '<div style="text-align:center;clear:both;color:#888;">'. __('This translation is maintained by ICanLocalize. Edits that you do will be overwritten when the translator does an update.','sitepress').'</div>';        
    }
    
    foreach($active_languages as $lang){
        if(isset($translations[$lang['code']])){
            $id = $translations[$lang['code']]->element_id;

            if($translations[$lang['code']]->original && $original_updated && $id == $post_id){
                echo '
                    <div id="noupdate_but" style="display:none;">
                    <input type="button" class="button" value="'.__('Translations don\'t need updating', 'sitepress').'" title="'.__('The translations for this document are OK.', 'sitepress').'"/>
                    <span id="noupdate_but_wm" style="display:none">'.__('Translations for this document appear to be out-of-date. Are you sure they don\'t need to be updated?','sitepress').'</span>                
                    </div>';
            }
            
            
            if($original_updated && !$translations[$lang['code']]->original){
                if ($short) {
                    $post_translation_statuses[$lang['code']] = __('Needs update','sitepress');
                } else {
                    $post_translation_statuses[$lang['code']] = __('Translation needs update','sitepress');
                }
            }elseif($translations[$lang['code']]->original){
                if ($short) {
                    $post_translation_statuses[$lang['code']] = __('Original','sitepress');
                } else {
                    $post_translation_statuses[$lang['code']] = __('Original document','sitepress');
                }
            }else{
                switch($tr_status[$lang['code']]){
                    case CMS_REQUEST_WAITING_FOR_PROJECT_CREATION: 
                        if ($short) {
                            $post_translation_statuses[$lang['code']] = __('In progress','sitepress');
                        } else {
                            $post_translation_statuses[$lang['code']] = __('Translation in progress','sitepress');
                        }
                        break;
                    case CMS_TARGET_LANGUAGE_DONE: 
                        if ($short) {
                            $post_translation_statuses[$lang['code']] = __('Complete','sitepress');
                        } else {
                            $post_translation_statuses[$lang['code']] = __('Translation complete','sitepress');
                        }
                        break;
                    case CMS_REQUEST_FAILED: 
                        $post_translation_statuses[$lang['code']] = __('Request failed','sitepress');
                        break;
                    default: 
                        $post_translation_statuses[$lang['code']] = __('Not translated','sitepress');
                }
            }                        
        }else{
            $post_translation_statuses[$lang['code']] = __('Not translated','sitepress');
        }
    }
}

function icl_decode_translation_status_id($status){
    switch($status){
        case CMS_TARGET_LANGUAGE_CREATED: $st = __('Waiting for translator','sitepress');break;
        case CMS_TARGET_LANGUAGE_ASSIGNED: $st = __('In progress','sitepress');break; 
        case CMS_TARGET_LANGUAGE_TRANSLATED: $st = __('Translation received','sitepress');break;
        case CMS_TARGET_LANGUAGE_DONE: $st = __('Translation complete','sitepress');break;
        case CMS_REQUEST_FAILED: $st = __('Request failed','sitepress');break;
        default: $st = __('Not translated','sitepress');
    }
    
    return $st;
}

/*
 Decode any html encoding in shortcodes
 http://codex.wordpress.org/Shortcode_API
*/
 
function _icl_content_decode_shortcodes(&$translation) {
    $body = $translation['body'];
    
    global $shortcode_tags;
    if (isset($shortcode_tags)) {
        $tagnames = array_keys($shortcode_tags);
    $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        $regexp = '/\[('.$tagregexp.')\b(.*?)\]/s';
        
        if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $body = str_replace($match[0], '[' . $match[1] . html_entity_decode($match[2]) . ']', $body);
            }
        }
        
    }
    
    $translation['body'] = $body;
}

function _icl_content_fix_image_paths_in_body(&$translation) {
    $body = $translation['body'];
    $image_paths = _icl_content_get_image_paths($body);
    
    $source_path = post_permalink($translation['original_id']);
  
    foreach($image_paths as $path) {
  
        $src_path = resolve_url($source_path, $path[2]);
        if ($src_path != $path[2]) {
            $search = $path[1] . $path[2] . $path[1];
            $replace = $path[1] . $src_path . $path[1];
            $new_link = str_replace($search, $replace, $path[0]);
      
            $body = str_replace($path[0], $new_link, $body);
      
          
        }
    
    }
    $translation['body'] = $body;
}

/**
 * get the paths to images in the body of the content
 */

function _icl_content_get_image_paths($body) {

  $regexp_links = array(
                      "/<img\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                      "/&lt;script\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                      "/<embed\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                      );

  $links = array();

  foreach($regexp_links as $regexp) {
    if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $links[] = $match;
      }
    }
  }

  return $links;
}


/**
 * Resolve a URL relative to a base path. This happens to work with POSIX
 * filenames as well. This is based on RFC 2396 section 5.2.
 */
function resolve_url($base, $url) {
        if (!strlen($base)) return $url;
        // Step 2
        if (!strlen($url)) return $base;
        // Step 3
        if (preg_match('!^[a-z]+:!i', $url)) return $url;
        $base = parse_url($base);
        if ($url{0} == "#") {
                // Step 2 (fragment)
                $base['fragment'] = substr($url, 1);
                return unparse_url($base);
        }
        unset($base['fragment']);
        unset($base['query']);
        if (substr($url, 0, 2) == "//") {
                // Step 4
                return unparse_url(array(
                        'scheme'=>$base['scheme'],
                        'path'=>$url,
                ));
        } else if ($url{0} == "/") {
                // Step 5
                $base['path'] = $url;
        } else {
                // Step 6
                $path = explode('/', $base['path']);
                $url_path = explode('/', $url);
                // Step 6a: drop file from base
                array_pop($path);
                // Step 6b, 6c, 6e: append url while removing "." and ".." from
                // the directory portion
                $end = array_pop($url_path);
                foreach ($url_path as $segment) {
                        if ($segment == '.') {
                                // skip
                        } else if ($segment == '..' && $path && $path[sizeof($path)-1] != '..') {
                                array_pop($path);
                        } else {
                                $path[] = $segment;
                        }
                }
                // Step 6d, 6f: remove "." and ".." from file portion
                if ($end == '.') {
                        $path[] = '';
                } else if ($end == '..' && $path && $path[sizeof($path)-1] != '..') {
                        $path[sizeof($path)-1] = '';
                } else {
                        $path[] = $end;
                }
                // Step 6h
                $base['path'] = join('/', $path);

        }
        // Step 7
        return unparse_url($base);
}

function unparse_url($parsed)
    {
    if (! is_array($parsed)) return false;
    $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
    $uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
    $uri .= isset($parsed['host']) ? $parsed['host'] : '';
    $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
    if(isset($parsed['path']))
        {
        $uri .= (substr($parsed['path'],0,1) == '/')?$parsed['path']:'/'.$parsed['path'];
        }
    $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
    $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
    return $uri;
    }

function _icl_content_fix_relative_link_paths_in_body(&$translation) {
    $body = $translation['body'];
    $link_paths = _icl_content_get_link_paths($body);

    $source_path = post_permalink($translation['original_id']);

    foreach($link_paths as $path) {
      
        if ($path[2][0] != "#"){
            $src_path = resolve_url($source_path, $path[2]);
            if ($src_path != $path[2]) {
                $search = $path[1] . $path[2] . $path[1];
                $replace = $path[1] . $src_path . $path[1];
                $new_link = str_replace($search, $replace, $path[0]);
                
                $body = str_replace($path[0], $new_link, $body);
            }
        }      
    }
    $translation['body'] = $body;
}

function _icl_content_get_link_paths($body) {
  
    $regexp_links = array(
                        "/<a.*?href\s*=\s*([\"\']??)([^\"]*)[\"\']>(.*?)<\/a>/i",
                        );
    
    $links = array();
    
    foreach($regexp_links as $regexp) {
        if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
              $links[] = $match;
            }
        }
    }
    
    return $links;
}

function _icl_content_make_links_sticky($element_id, $element_type='post', $string_translation = true) {
    if($element_type=='post'){
        // only need to do it if sticky links is not enabled.
        // create the object
        if(!$sitepress_settings['modules']['absolute-links']['enabled']){
            include_once ICL_PLUGIN_PATH . '/modules/absolute-links/absolute-links-plugin.php';
            $icl_abs_links = new AbsoluteLinksPlugin();
            $icl_abs_links->process_post($element_id);
        }
    }elseif($element_type=='string'){                
        if(!class_exists('AbsoluteLinksPlugin')){
            include_once ICL_PLUGIN_PATH . '/modules/absolute-links/absolute-links-plugin.php';
        }
        $icl_abs_links = new AbsoluteLinksPlugin(true); // call just for strings
        $icl_abs_links->process_string($element_id, $string_translation);                                        
    }
}

function _icl_content_fix_links_to_translated_content($element_id, $target_lang_code, $element_type='post'){
    global $wpdb, $sitepress, $sitepress_settings, $wp_taxonomies;
    _icl_content_make_links_sticky($element_id, $element_type);
    
    
    if($element_type == 'post'){
        $post = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID={$element_id}");
        $body = $post->post_content;        
    }elseif($element_type=='string'){
        $body = $wpdb->get_var("SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE id=" . $element_id);
    }    
    $new_body = $body;

    $base_url_parts = parse_url(get_option('home'));
    
    $links = _icl_content_get_link_paths($body);
    $all_links_fixed = 1;
    
    foreach($links as $link) {
        $path = $link[2];
        $url_parts = parse_url($path);
        
        if((!isset($url_parts['host']) or $base_url_parts['host'] == $url_parts['host']) and
                (!isset($url_parts['scheme']) or $base_url_parts['scheme'] == $url_parts['scheme']) and
                isset($url_parts['query'])) {
            $query_parts = split('&', $url_parts['query']);
            foreach($query_parts as $query){
                
                // find p=id or cat=id or tag=id queries
                
                list($key, $value) = split('=', $query);
                $translations = NULL;
                $is_tax = false;
                if($key == 'p'){
                    $kind = 'post_' . $wpdb->get_var("SELECT post_type FROM {$wpdb->posts} WHERE ID='{$value}'");
                } else if($key == "page_id"){
                    $kind = 'post_page';
                } else if($key == 'cat' || $key == 'cat_ID'){
                    $kind = 'tax_category';
                } else if($key == 'tag'){
                    $is_tax = true;
                    $taxonomy = 'post_tag';
                    $kind = 'tax_' . $taxonomy;                    
                    $value = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->terms} t 
                        JOIN {$wpdb->term_taxonomy} x ON t.term_id = x.term_id WHERE x.taxonomy='{$taxonomy}' AND t.slug='{$value}'");
                } else {
                    $found = false;
                    foreach($wp_taxonomies as $ktax => $tax){
                        if($tax->query_var && $key == $tax->query_var){
                            $found = true;
                            $is_tax = true;
                            $kind = 'tax_' . $ktax;                            
                            $value = $wpdb->get_var("
                                SELECT term_taxonomy_id FROM {$wpdb->terms} t 
                                    JOIN {$wpdb->term_taxonomy} x ON t.term_id = x.term_id WHERE x.taxonomy='{$ktax}' AND t.slug='{$value}'");                            
                        }                        
                    }
                    if(!$found) continue;
                }

                $link_id = (int)$value;  
                
                if (!$link_id || $sitepress->get_language_for_element($link_id, $kind) == $target_lang_code) {
                    // link already points to the target language.
                    continue;
                }

                $trid = $sitepress->get_element_trid($link_id, $kind);
                if(!$trid){
                    continue;
                }
                if($trid !== NULL){
                    $translations = $sitepress->get_element_translations($trid, $kind);
                }
                if(isset($translations[$target_lang_code])){
                    
                    // use the new translated id in the link path.
                    
                    $translated_id = $translations[$target_lang_code]->element_id;
                    
                    if($is_tax){
                        $translated_id = $wpdb->get_var("SELECT slug FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} x ON t.term_id=x.term_id WHERE x.term_taxonomy_id=$translated_id");    
                    }

                    $replace = $key . '=' . $translated_id;
                    
                    $new_link = str_replace($query, $replace, $link[0]);
                    
                    // replace the link in the body.
                    
                    $new_body = str_replace($link[0], $new_link, $new_body);
                } else {
                    // translation not found for this.
                    $all_links_fixed = 0;
                }
            }
        }
        
        
    }
    
    if ($new_body != $body){

        // unless sticky links is on - we convert default links to permalinks
        /*
        if(!$sitepress_settings['modules']['absolute-links']['enabled']){
            // create the object
            include_once ICL_PLUGIN_PATH . '/modules/absolute-links/absolute-links-plugin.php';
            $icl_abs_links = new AbsoluteLinksPlugin();
            
            $new_body = $icl_abs_links->show_permalinks($new_body);
        }        
        */
        
        // save changes to the database.
        if($element_type == 'post'){        
            $wpdb->update($wpdb->posts, array('post_content'=>$new_body), array('ID'=>$element_id));
            
            // save the all links fixed status to the database.
            $wpdb->query("UPDATE {$wpdb->prefix}icl_node SET links_fixed='{$all_links_fixed}' WHERE nid={$element_id}");
            
        }elseif($element_type == 'string'){
            $wpdb->update($wpdb->prefix.'icl_string_translations', array('value'=>$new_body), array('id'=>$element_id));
        }
                
    }
    
}

$asian_languages = array('ja', 'ko', 'zh-hans', 'zh-hant', 'mn', 'ne', 'hi', 'pa', 'ta', 'th');

function icl_estimate_word_count($data, $lang_code) {
    global $asian_languages;
    
    $words = 0;
    if(isset($data->post_title)){
        if(in_array($lang_code, $asian_languages)){
            $words += strlen(strip_tags($data->post_title)) / 6;
        } else {
            $words += count(explode(' ',$data->post_title));
        }
    }
    if(isset($data->post_content)){
        if(in_array($lang_code, $asian_languages)){
            $words += strlen(strip_tags($data->post_content)) / 6;
        } else {
            $words += count(explode(' ',strip_tags($data->post_content)));
        }
    }
    
    return (int)$words;
}

function icl_estimate_custom_field_word_count($post_id, $lang_code) {
    global $asian_languages;

    include_once ICL_PLUGIN_PATH . '/inc/plugins-texts-functions.php';
    
    $words = 0;
    $custom_fields = icl_get_posts_translatable_fields();
    foreach($custom_fields as $id => $cf){
        if ($cf->translate) {
            $custom_fields_value = get_post_meta($post_id, $cf->attribute_name, true);
            if ($custom_fields_value != "") {
                if(in_array($lang_code, $asian_languages)){
                    $words += strlen(strip_tags($custom_fields_value)) / 6;
                } else {
                    $words += count(explode(' ',strip_tags($custom_fields_value)));
                }
            }
        }
    }
    
    return (int)$words;
}

function _icl_list_posts($args){
    global $wpdb, $sitepress, $sitepress_settings;
    try{
        
        $signature   = $args[0];
        $site_id     = $args[1];
            
        $from_date   = date('Y-m-d H:i:s',$args[2]);
        $to_date     = date('Y-m-d H:i:s',$args[3]);
        $lang        = $args[4];
        $tstatus     = $args[5];
        $status      = $args[6];
        $type        = $args[7];
        
        if ( !$sitepress_settings['remote_management']) {
            return array('err_code'=>1, 'err_str'=>__('remote translation management not enabled','sitepress'));
        }    
        if ( !$sitepress->get_icl_translation_enabled() ) {
            return array('err_code'=>3, 'err_str'=> __( 'Professional translation not enabled.','sitepress'));
        }

        //check signature
        $signature_chk = sha1($sitepress_settings['access_key'].$sitepress_settings['site_id'].$lang.$tstatus);
        if($signature_chk != $signature){
            return array('err_code'=>2, 'err_str'=>__('signature incorrect','sitepress'));
        }
        
        if ($site_id != $sitepress_settings['site_id']) {
            return array('err_code'=>4, 'err_str'=>__('website id is not correct','sitepress'));
        }
    
    
        $documents = icl_translation_get_documents($sitepress->get_language_code($lang), $tstatus, $status, $type, 100000, $from_date, $to_date);
        foreach($documents as $id=>$data){
            $_cats = (array)get_the_terms($id,'category');
            $cats = array();
            foreach($_cats as $cv){
                $cats[] = $cv->name;
            }
            $documents[$id]->categories = $cats;
            $documents[$id]->words = icl_estimate_word_count($data, $sitepress->get_language_code($lang));
            $documents[$id]->words += icl_estimate_custom_field_word_count($id, $sitepress->get_language_code($lang));
            unset($documents[$id]->post_content);
            unset($documents[$id]->post_title);
        }
    }catch(Exception $e){
        return array('err_code'=>$e->getCode(), 'err_str'=>$e->getMessage().'[' . $e->getFile() . ':' . $e->getLine() . ']');
    }
    return array('err_code'=>0, 'posts'=>$documents);

}

function _icl_remote_control_translate_post($args){
    global $wpdb, $sitepress, $sitepress_settings;
    $signature   = $args[0];
    $site_id     = $args[1];
    
    $post_id     = $args[2];
    $from_lang   = $args[3];
    $langs       = $args[4];

    if ( !$sitepress_settings['remote_management']) {
        return array('err_code'=>1, 'err_str'=>__('remote translation management not enabled','sitepress'));
    }    
    if ( !$sitepress->get_icl_translation_enabled() ){
        return array('err_code'=>3, 'err_str'=> __( 'Professional translation not enabled.','sitepress'));
    }

    //check signature
    $signature_chk = sha1($sitepress_settings['access_key'].$sitepress_settings['site_id'].$post_id.$from_lang.implode(',', $langs));
    if($signature_chk != $signature){
        return array('err_code'=>2, 'err_str'=>__('signature incorrect','sitepress'));
    }
    
    if ($site_id != $sitepress_settings['site_id']) {
        return array('err_code'=>4, 'err_str'=>__('website id is not correct','sitepress'));
    }

    // check post_id
    $post = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE ID={$post_id}");
    if(!$post){
        return array('err_code'=>5, 'err_str'=>__('post id not found','sitepress'));
    }
    $post_type = $wpdb->get_var("SELECT post_type FROM {$wpdb->posts} WHERE ID={$post_id}");
    
    $element = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}icl_translations WHERE element_id={$post_id} and element_type='post_{$post_type}'");
    if(!$element){
        return array('err_code'=>6, 'err_str'=>__('post id not managed in icl_translations','sitepress'));
    }

    $from_code = $sitepress->get_language_code($from_lang);
    if($element->language_code != $from_code){
        return array('err_code'=>7, 'err_str'=>sprintf(__('Source language is not correct. %s != %s', 'sitepress'), $from_code, $element->language_code));
    }
    
    $language_pairs = $sitepress_settings['language_pairs'];
    
    foreach($langs as $to_lang){
        if(!isset($language_pairs[$from_code][$sitepress->get_language_code($to_lang)])){
            return array('err_code'=>8, 'err_str'=>sprintf(__('Destination language %s is not correct','sitepress'), $to_lang));
        }
    }
    
    // everything is ok.
    
    
    
    try{
        $result = icl_translation_send_post($post_id, $langs, $post_type);
    }catch(Exception $e){
        return array('err_code'=>$e->getCode(), 'err_str'=>$e->getMessage().'[' . $e->getFile() . ':' . $e->getLine() . ']');
    }        
    
    if ($result != false){
        return array('err_code'=>0, 'rid'=>$result);
    } else {
        return array('err_code'=>9, 'err_str'=>_('failed to send for translation','sitepress'));
    }
    
    
}

function sh_post_submitbox_start(){
    global $post;
    if(!$post->ID){
        return;
    }
    $status = icl_get_post_translation_status($post->ID);    
    if(empty($status)){
        return;
    }
    
    $show_box = 'display:none';
    
    foreach($status as $k=>$v){
        if($v->status && !$v->updated){
            $show_box = '';
            break;
        }
    }
    
    
    echo '<p id="icl_minor_change_box" style="float:left;padding:0;margin:3px;'.$show_box.'">';
    echo '<label><input type="checkbox" name="icl_minor_edit" value="1" style="min-width:15px;" />&nbsp;';
    echo __('Minor edit - don\'t update translation','sitepress');        
    echo '</label>';
    echo '<br clear="all" />';
    echo '</p>';
}

function icl_server_languages_map($language_name, $server2plugin = false){    
    if(is_array($language_name)){
        return array_map('icl_server_languages_map', $language_name);
    }
    $map = array(
        'Norwegian Bokmål' => 'Norwegian',
        'Portuguese, Brazil' => 'Portuguese',
        'Portuguese, Portugal' => 'Portugal Portuguese'
    );
    if($server2plugin){
        $map = array_flip($map);
    }    
    if(isset($map[$language_name])){
        return $map[$language_name];
    }else{
        return $language_name;    
    }
}

// String translation using ICanLocalize server

/*
 $string_ids - an array of string ids to be sent for translation
 $target_languages - an array of languages to translate to
*/
function icl_translation_send_strings($string_ids, $target_languages) {
    // send to each language
    foreach($target_languages as $target){
        _icl_translation_send_strings($string_ids, $target);
    }
}

function _icl_translation_send_strings($string_ids, $target) {
    global $wpdb, $sitepress, $sitepress_settings;
    
    if(!$sitepress_settings['st']['strings_language']) $sitepress_settings['st']['strings_language'] = $sitepress->get_default_language();
    
    $target_code = $sitepress->get_language_code($target);
    
    // get all the untranslated strings
    $untranslated = array();
    foreach($string_ids as $st_id) {
        $status = $wpdb->get_var("SELECT status FROM {$wpdb->prefix}icl_string_translations WHERE string_id={$st_id} and language='{$target_code}'");
        if ($status != ICL_STRING_TRANSLATION_COMPLETE) {
            $untranslated[] = $st_id;
        }
    }
    
    if (sizeof($untranslated) >  0) {
        // Something to translate.
        $target_for_server = apply_filters('icl_server_languages_map', array($target)); //filter some language names to match the names on the server
        $data = array(
            'url'=>'', 
            'target_languages' => $target_for_server,
        );
        $string_values = array();
        foreach($untranslated as $st_id) {
            
            $string = $wpdb->get_row("SELECT context, name, value FROM {$wpdb->prefix}icl_strings WHERE id={$st_id}");
            $string_values[$st_id] = $string->value;
            $data['contents']['string-'.$st_id.'-context'] = array(
                    'translate'=>0,
                    'data'=>base64_encode(htmlspecialchars($string->context)),
                    'format'=>'base64',
            );
            $data['contents']['string-'.$st_id.'-name'] = array(
                    'translate'=>0,
                    'data'=>base64_encode(htmlspecialchars($string->name)),
                    'format'=>'base64',
            );
            $data['contents']['string-'.$st_id.'-value'] = array(
                    'translate'=>1,
                    'data'=>base64_encode(htmlspecialchars($string->value)),
                    'format'=>'base64',
            );
            
        }

        $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);
        
        $orig_lang = $sitepress->get_language_details($sitepress_settings['st']['strings_language']);
        $orig_lang_for_server = apply_filters('icl_server_languages_map', $orig_lang['english_name']);

        $timestamp = date('Y-m-d H:i:s');
        
        $xml = $iclq->build_cms_request_xml($data, $orig_lang_for_server);
        $res = $iclq->send_request($xml, "String translations", $target_for_server, $orig_lang_for_server, "");

        
        if($res > 0){
            foreach($string_values as $st_id => $value){
                $wpdb->insert($wpdb->prefix.'icl_string_status', array('rid'=>$res, 'string_translation_id'=>$st_id, 'timestamp'=>$timestamp, 'md5'=>md5($value))); //insert rid
            }
    
            $wpdb->insert($wpdb->prefix.'icl_core_status', array('rid'=>$res,
                                                                     'origin'=>$orig_lang['code'],
                                                                     'target'=>$target_code,
                                                                     'status'=>CMS_REQUEST_WAITING_FOR_PROJECT_CREATION));
        }
    }
}

function icl_translation_get_string_translation_status($string_id) {
    global $wpdb;
    $status = $wpdb->get_var("
            SELECT
                MIN(cs.status) 
            FROM
                {$wpdb->prefix}icl_core_status cs
            JOIN 
               {$wpdb->prefix}icl_string_status ss
            ON
               ss.rid = cs.rid
            WHERE
                ss.string_translation_id={$string_id}
            "   
            );
    
    if ($status === null){
        return "";
    }
    
    $status = icl_decode_translation_status_id($status);
    
    return $status;
        
}

function icl_translation_send_untranslated_strings($target_languages) {
    global $wpdb;
    $untranslated = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}icl_strings WHERE status <> " . ICL_STRING_TRANSLATION_COMPLETE);
    
    icl_translation_send_strings($untranslated, $target_languages);
    
}

function icl_is_string_translation($translation) {
    // determine if the $translation data is for string translation.
    
    foreach($translation as $key => $value) {
        if($key == 'body' or $key == 'title') {
            return false;
        }
        if (preg_match("/string-.*?-value/", $key)){
            return true;
        }
    }
    
    // if we get here assume it's not a string.
    return false;
    
}

function icl_translation_add_string_translation($trid, $translation, $lang, $rid){
    
    global $wpdb, $sitepress_settings, $sitepress;
    $lang_code = $wpdb->get_var("SELECT code FROM {$wpdb->prefix}icl_languages WHERE english_name='".$wpdb->escape($lang)."'");
    if(!$lang_code){        
        return false;
    }

    foreach($translation as $key => $value) {
        if (preg_match("/string-(.*?)-value/", $key, $match)){
            $string_id = $match[1];
            
            $md5_when_sent = $wpdb->get_var("SELECT md5 FROM {$wpdb->prefix}icl_string_status WHERE string_translation_id={$string_id} AND rid={$rid}");
            $current_string_value = $wpdb->get_var("SELECT value FROM {$wpdb->prefix}icl_strings WHERE id={$string_id}");
            if ($md5_when_sent == md5($current_string_value)) {
                $status = ICL_STRING_TRANSLATION_COMPLETE;
            } else {
                $status = ICL_STRING_TRANSLATION_NEEDS_UPDATE;
            }
            $value = str_replace ( '&#0A;', "\n", $value );
            icl_add_string_translation($string_id, $lang_code, html_entity_decode($value), $status);
        }
    }

    // update translation status
    $wpdb->update($wpdb->prefix.'icl_core_status', array('status'=>CMS_TARGET_LANGUAGE_DONE), array('rid'=>$rid, 'target'=>$sitepress->get_language_code($lang)));
    
    return true;
}


function _icl_xmlrpc_add_message_translation($args){
    global $wpdb, $sitepress, $sitepress_settings, $wpml_add_message_translation_callbacks;
    $signature      = $args[0];
    $site_id        = $args[1];
    $rid            = $args[2];
    $translation    = $args[3];
    
    $signature_check = md5($sitepress_settings['access_key'] . $sitepress_settings['site_id'] . $rid);
    if($signature != $signature_check){
        return 0; // array('err_code'=>1, 'err_str'=> __('Signature mismatch','sitepress'));
    }
    
    $res = $wpdb->get_row("SELECT to_language, object_id, object_type FROM {$wpdb->prefix}icl_message_status WHERE rid={$rid}");
    if(!$res){
        return 0;
    }
    
    $to_language = $res->to_language;
    $object_id   = $res->object_id;
    $object_type   = $res->object_type;
    
    try{
        if(is_array($wpml_add_message_translation_callbacks[$object_type])){
            foreach($wpml_add_message_translation_callbacks[$object_type] as $callback){
                if ( !is_null($callback) ) {
                    call_user_func($callback, $object_id, $to_language, $translation);    
                } 
            }
        }                            
        $wpdb->update($wpdb->prefix.'icl_message_status', array('status'=>MESSAGE_TRANSLATION_COMPLETE), array('rid'=>$rid));
    }catch(Exception $e){
        return $e->getMessage().'[' . $e->getFile() . ':' . $e->getLine() . ']';
    }
    return 1;
    
}

function update_icl_account(){
    global $sitepress, $wpdb;

    //if the account is configured - update language pairs
    if($sitepress->icl_account_configured()){
        $iclsettings = $sitepress->get_settings();
        
        $pay_per_use = $iclsettings['translator_choice'] == 1;

        // prepare language pairs
        
        $language_pairs = $iclsettings['language_pairs'];
        $lang_pairs = array();
        foreach($language_pairs as $k=>$v){
            $english_fr = $wpdb->get_var("SELECT english_name FROM {$wpdb->prefix}icl_languages WHERE code='{$k}' ");
            foreach($v as $k=>$v){
                $incr++;
                $english_to = $wpdb->get_var("SELECT english_name FROM {$wpdb->prefix}icl_languages WHERE code='{$k}' ");
                $lpairs['from_language'.$incr] = apply_filters('icl_server_languages_map', $english_fr); 
                $lpairs['to_language'.$incr] = apply_filters('icl_server_languages_map', $english_to);
                if ($pay_per_use) {
                    $lpairs['pay_per_use'.$incr] = 1;
                } else {
                    $lpairs['pay_per_use'.$incr] = 0;
                }
            }    
        }
        $data['site_id'] = $iclsettings['site_id'];                    
        $data['accesskey'] = $iclsettings['access_key'];
        $data['create_account'] = 0;
        $data['url'] = get_option('home');
        $data['title'] = get_option('blogname');
        $data['description'] = $iclsettings['icl_site_description'];
        $data['project_kind'] = $iclsettings['website_kind'];
        $data['pickup_type'] = $iclsettings['translation_pickup_method'];
        $data['interview_translators'] = $iclsettings['interview_translators'];

        $notifications = 0;
        if ($iclsettings['notify_complete']){
            $notifications += 1;
        }
        if ($iclsettings['alert_delay']){
            $notifications += 2;
        }
        $data['notifications'] = $notifications;
        
        $data = array_merge($data, $lpairs);
        
        require_once ICL_PLUGIN_PATH . '/lib/Snoopy.class.php';
        require_once ICL_PLUGIN_PATH . '/lib/xml2array.php';
        require_once ICL_PLUGIN_PATH . '/lib/icl_api.php';
        
        $icl_query = new ICanLocalizeQuery();
        
        return $icl_query->updateAccount($data);
    } else {
        return 0;
    }

        
}
?>