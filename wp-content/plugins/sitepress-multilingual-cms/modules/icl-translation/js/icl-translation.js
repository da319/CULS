jQuery(document).ready(function(){
    iclUpdateTranslationEstimate();
    
    // th checkboxes
    jQuery('#icl-translation-dashboard th :checkbox').change(
        function(){
            if(jQuery(this).attr('checked')){
                jQuery('#icl-translation-dashboard :checkbox').attr('checked','checked');    
                if(jQuery('#icl-tr-opt :checkbox:checked').length > 0) icl_td_enable_send();
            }else{
                jQuery('#icl-translation-dashboard :checkbox').removeAttr('checked');  
                icl_td_disable_send();
            }   
            iclUpdateTranslationEstimate();
        }
    );
    
    jQuery('#icl-translation-dashboard td :checkbox').change(
        function(){
            if(!jQuery('#icl-translation-dashboard td :checkbox:checked').length){
                icl_td_disable_send();
            }else{
                if(jQuery('#icl-tr-opt :checkbox:checked').length){
                    icl_td_enable_send();    
                }                
                if(jQuery('#icl-translation-dashboard td :checkbox:checked').length == jQuery('#icl-translation-dashboard td :checkbox').length){
                    jQuery('#icl-translation-dashboard th :checkbox').attr('checked', 'checked');
                }else{
                    jQuery('#icl-translation-dashboard th :checkbox').removeAttr('checked');
                }
            }
            iclUpdateTranslationEstimate();
        }        
    );
    
    // lang options checkboxes
    jQuery('#icl-tr-opt :checkbox').change(
        function(){
            if(!jQuery('#icl-tr-opt :checkbox:checked').length){
                icl_td_disable_send()
            }else{
                if(jQuery('#icl-translation-dashboard :checkbox:checked').length > 0){
                    icl_td_enable_send();
                }   
            }
            iclUpdateTranslationEstimate();
        }
    )
      
    jQuery('#icl-tr-sel-doc').click(function(){        
        
        if(jQuery('#icl-translation-dashboard td :checkbox:checked').length==0) return false;
    
        target_languages = new Array();
        jQuery('#icl-tr-opt :checkbox').each(function(){
            if(jQuery(this).attr('checked')){
                target_languages.push(jQuery(this).val());
            }
        });
        jQuery('#icl_ajx_response_td').fadeIn();        
        jQuery('#icl-tr-sel-doc').attr('disabled','disabled');    
        var post_ids = new Array();
        var tmpback = new Array();
        jQuery('#icl-translation-dashboard :checkbox').each(function(){
            if(jQuery(this).attr('checked') && jQuery(this).val()!='on'){
                post_id = jQuery(this).val();
                tmpback[post_id] = jQuery('#icl-tr-status-'+post_id).html();
                post_ids.push(post_id);
            }            
        });
        post_types = new Array();
        jQuery('#icl-translation-dashboard .icl_td_post_type').each(function(){
            post_types.push(jQuery(this).attr('name')+'='+jQuery(this).val()); 
        });
        
        jQuery.ajax({
            type: "POST",
            url: icl_ajx_url,
            dataType: 'json',
            data: "icl_ajx_action=send_translation_request&post_ids="+post_ids+'&'+post_types.join('&')+'&target_languages='+target_languages.join('#'),
            success: function(msg){
                var all_ok = true;
                for(i in msg){
                    p = msg[i];    
                    if(p.status > 0){
                        jQuery('#icl-tr-status-'+p.post_id).html(jQuery('#icl_message_2').html());
                    }else{
                        jQuery('#icl-tr-status-'+p.post_id).html(tmpback[p.post_id]);
                        all_ok = false;
                    }
                    jQuery('#icl-tr-status-'+p.post_id).fadeIn();
                }
                jQuery('#icl-tr-sel-doc').removeAttr('disabled');    
                if (all_ok) {
                    message = 'icl_message_1';
                } else {
                    message = 'icl_message_error';
                }
                jQuery('#icl_ajx_response_td').html(jQuery('#'+message).html());
                location.href = location.href + "&message="+message;
            }
        });
    });
      
    jQuery('a.translation_details_but').click(toogleTranslationDetails);
    
    var cache = '&cache=1';
    if (location.href.indexOf("content-translation.php") != -1 || location.href.indexOf('string-translation.php') != -1) {
        cache = '';
    }
    
    jQuery.ajax({
        type: "POST",
        url: icl_ajx_url,
        dataType: 'json',
        data: "icl_ajx_action=get_translator_status"+cache,
        success: function(msg){
            if (cache == '') {
                var from_lang = jQuery('input[name="filter[lang]"]:checked').attr('value');
                for(i in msg){
                    if(msg[i]['from'] == from_lang) {
                        if(msg[i]['have_translators'] == 1) {
                            var to_lang = msg[i]['to'];
                            if(jQuery('#icl-tr-not-avail-to-'+to_lang).length > 0) {
                                jQuery('input[name="icl-tr-to-'+to_lang+'"]').removeAttr('disabled');
                                jQuery('#icl-tr-not-avail-to-'+to_lang).remove();
                            }
                            
                        }
                    }
                    
                }
            }

            
        }
    });

    if (typeof(icl_tb_init) != 'undefined') {
        icl_tb_init('a.icl_thickbox');
        icl_tb_set_size('a.icl_thickbox');
    }
    
    
    jQuery('.icl_tn_link').click(function(){
        jQuery('.icl_post_note:visible').slideUp();
        thisl = jQuery(this);
        spl = thisl.attr('id').split('_');
        doc_id = spl[3];
        if(jQuery('#icl_post_note_'+doc_id).css('display') != 'none'){
            jQuery('#icl_post_note_'+doc_id).slideUp();
        }else{
            jQuery('#icl_post_note_'+doc_id).slideDown();
            jQuery('#icl_post_note_'+doc_id+' textarea').focus();
        }
        return false;
    });
    
    jQuery('.icl_post_note textarea').keyup(function(){
        if(jQuery.trim(jQuery(this).val())){
            jQuery('.icl_tn_clear').removeAttr('disabled');
        }else{
            jQuery('.icl_tn_clear').attr('disabled', 'disabled');
        }  
    });
    jQuery('.icl_tn_clear').click(function(){
        jQuery(this).closest('table').prev().val('');
        jQuery(this).attr('disabled','disabled');
    })
    jQuery('.icl_tn_save').click(function(){
        thisa = jQuery(this);
        thisa.closest('table').find('input').attr('disabled','disabled');
        tn_post_id = thisa.closest('table').find('.icl_tn_post_id').val();
        jQuery.ajax({
                type: "POST",
                url: icl_ajx_url,        
                data: "icl_ajx_action=save_translator_note&note="+thisa.closest('table').prev().val()+'&post_id='+tn_post_id,
                success: function(msg){
                    thisa.closest('table').find('input').removeAttr('disabled');
                    thisa.closest('table').parent().slideUp();
                    icon_url = jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src');
                    if(thisa.closest('table').prev().val()){
                        jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src', icon_url.replace(/add_translation\.png$/, 'edit_translation.png'));
                    }else{
                        jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src', icon_url.replace(/edit_translation\.png$/, 'add_translation.png'));
                    }
                }
        });    
        
    })
    
});

function icl_td_enable_send(){
    jQuery('#icl-tr-sel-doc').removeAttr('disabled');    
}
function icl_td_disable_send(){
    jQuery('#icl-tr-sel-doc').attr('disabled','disabled');    
}

function iclUpdateTranslationEstimate(){
    //determine words 
    var words = 0;    
    var langs = jQuery('#icl-tr-opt :checkbox:checked').length;
    jQuery('#icl-translation-dashboard td :checkbox:checked').each(function(){
        words += parseInt(jQuery('#icl-cw-'+jQuery(this).val()).html());    
    });
    jQuery('#icl-estimated-words-count').html(words * langs);
    
    // estimate cost    
    var estimate = 0;
    jQuery('#icl-tr-opt :checkbox:checked').each(
        function(){
            lang = jQuery(this).attr('name').replace(/^icl-tr-to-/,'');
            rate = jQuery('#icl_tr_rate_'+lang).val();
            estimate += words * rate;
        }
    )
    if(estimate < 1){
        precision = Math.floor(estimate).toString().length + 1;    
    }else{
        precision = Math.floor(estimate).toString().length + 2;
    }
    jQuery('#icl-estimated-quote').html(estimate.toPrecision(precision));
    
}

var icl_tr_details_selected_rid = 0;
function toogleTranslationDetails(){    
    jQuery('.icl-tr-details:visible').slideUp();
    var rid = jQuery(this).attr('href').replace(/#translation-details-/,'');        
    if(rid == icl_tr_details_selected_rid){
        icl_tr_details_selected_rid = 0;
        return;
    } 
    icl_tr_details_selected_rid = rid;
    var tr = jQuery(this).parent().parent();
    var last_col = tr.find('td:eq(4)');
    last_col.append(icl_ajxloaderimg);    
    tr.find('td span.icl-tr-details').load(location.href.replace(/#(.*)$/,''), {
        icl_ajx_req:'get_translation_details',
        rid:rid
    }, function(){        
        last_col.find('img').fadeOut('fast',function(){jQuery(this).remove()});
        jQuery(this).slideDown();
        icl_tb_init('a.icl_thickbox');
        icl_tb_set_size('a.icl_thickbox');
        
    });    
}

function icl_refresh_translator_not_available_links() {
    
    // the links can be in
    // 1) translation dashboard
    // 2) string translation
    // 3) Pro translation
    
    var from_lang = jQuery('input[name="filter[lang]"]:checked').attr('value');
    if(from_lang == undefined){
        from_lang = jQuery('input[name="icl-tr-from"]').attr('value');
    }
    from_lang = '&from_lang=' + from_lang;
    
    cache = '';
    count = 0;
    jQuery('.icl-tr-not-avail-to').each(function(){
        count += 1;
    });
    
    jQuery('.icl-tr-not-avail-to').each(function(){
        if(jQuery(this).html().indexOf('/explain') == -1 &&
                jQuery(this).html().indexOf('/support/show') == -1) {
            return;
        }

        id = this.id;

        if (id.indexOf('icl_lng_from_status_') != -1) {
            langs = id.substring(20).split('_');
            from_lang = langs[0];
            from_lang = '&from_lang=' + from_lang;
            to_lang = langs[1];
            to_lang = '&to_lang=' + to_lang;
        } else {
            to_lang = id.substring(20);
            to_lang = '&to_lang=' + to_lang;
        }
        jQuery.ajax({
            type: "POST",
            url: icl_ajx_url,
            data: "icl_ajx_action=get_language_status_text"+cache+from_lang+to_lang+'&id='+id,
            success: function(msg){
                spl = msg.split('|');
                if(spl[0]=='1'){
                    item_id = spl[1];
                    jQuery('#' + item_id).html(spl[2]);
                    count -= 1;
                    if (count == 0) {
                        icl_tb_init('a.icl_thickbox');
                        icl_tb_set_size('a.icl_thickbox');
                    }
                }
            }
        });
        
        //cache = '&cache=1';
    });

    
}


function iclConfigureAccount(){
    formdata = jQuery(this).serialize();
    var thisf = jQuery(this);
    iclShowProgressBar(thisf, function(){        
        thisf.contents().find("input").removeAttr('disabled');
        jQuery('.icl_progress').html('ERROR: Connection timeout!').css('color','red');    
        window.stop();
    });
    thisf.contents().find("input").attr('disabled', 'disabled');
    jQuery.post(thisf.attr('action'), formdata, 
        function(msg){           
            matches = msg.replace(/\r?\n/ig,'').match(/<body([^>]*)>(.*)<\/body>/i);
            jQuery('.icl_progress').stop();
            jQuery('body').html(matches[2]);
        }
    )
    return false;
}

var _icl_progress_text_save = false;
var _icl_progress_width = false;
function iclShowProgressBar(form, callback){    
    
    progress = form.find('.icl_progress');
    if(_icl_progress_text_save){        
        progress.html(_icl_progress_text_save).css('color','white').css('width',_icl_progress_width);
        progress.css('width');
    }
    if(jQuery('.icl_progress').html() != 'ERROR: Connection timeout!'){
        _icl_progress_text_save = progress.html();
        _icl_progress_width = progress.css('width');
    }
    progress.fadeIn();
    progress.animate({        
        width:'99.5%'
    }, 25000, callback); 
}

        
        