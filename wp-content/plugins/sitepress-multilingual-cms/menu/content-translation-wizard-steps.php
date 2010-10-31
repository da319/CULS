<?php 
    if(!$sitepress_settings['content_translation_setup_complete']): /* setup wizard */ 
        if(!$sitepress_settings['content_translation_languages_setup']){
            $sw_width = 10;
        }elseif($sitepress_settings['content_translation_setup_wizard_step'] == 2){
            $sw_width = 45;
        }else{          
            $sw_width = 80;
        }
        ?>
        <div id="icl_setup_wizard_wrap">
            <h3><?php _e('Before you can start using Professional translation, it needs to be set up', 'sitepress') ?></h3>
            <br style="clear:both;" />
            <div id="icl_setup_wizard">
                <div class="icl_setup_wizard_step"><strong><?php _e('1. Translation Languages', 'sitepress')?></strong></div>
                <div class="icl_setup_wizard_step"><strong><?php _e('2. Description', 'sitepress')?></strong></div>
                <div class="icl_setup_wizard_step"><strong><?php _e('3. ICanLocalize account setup', 'sitepress')?></strong></div>            
            </div>        
            <br clear="all" />
            <div id="icl_setup_wizard_progress"><div id="icl_setup_wizard_progress_bar" style="width:<?php echo $sw_width ?>%">&nbsp;</div></div>
        </div>
        <br />
<?php endif; /* setup wizard */ ?>
        
