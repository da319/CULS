<?php $sitepress->noscript_notice() ?>
<div class="wrap">
    <div id="icon-options-general" class="icon32<?php if(!$sitepress_settings['basic_menu']) echo ' icon32_adv'?>"><br /></div>    
    
    <h2><?php _e('Professional Translation - How it works', 'sitepress') ?></h2>    
    
    <?php include ICL_PLUGIN_PATH . '/menu/basic_advanced_switch.php' ?>    
    
    <img src="<?php echo ICL_PLUGIN_URL?>/res/img/web_logo_large.png" style="float: right; border: 1pt solid #C0C0C0; margin: 16px 10px 10px 10px;" alt="ICanLocalize" />   
        
    <p style="line-height:1.5"><?php echo __('<a href="http://www.icanlocalize.com">ICanLocalize</a> can provide professional translation for your site\'s contents.', 'sitepress'); ?></p>

    <h3><?php echo __('Your job:', 'sitepress'); ?></h3>
    
    
    <?php
        $steps = array(
            __('You select the content you want translated from the dashboard', 'sitepress'),
            __('Select the language you want it translated to', 'sitepress'),
        );
    ?>
    
    <ul>
        <?php foreach($steps as $number => $item): ?>
            <li><?php echo $number + 1 . '. ' . $item;?></li>
        <?php endforeach;?>
    </ul>
    
    <p>That's it - the rest happens automatically</p>
    <h3><?php echo __('How it works after that:', 'sitepress'); ?></h3>
    
    
    <?php
        $steps = array(
            __('The content you choose is sent to the ICanLocalize server', 'sitepress'),
            __('The translator chosen by you to do the work is automatically notified that there is something requiring translation', 'sitepress'),
            __('The translator downloads the content from the ICanLocalize server', 'sitepress'),
            __('The translator translates the content using a custom built editor', 'sitepress'),
            __('When the translation is ready the translator uploads to the ICanLocalize server', 'sitepress'),
            __('The ICanLocalize server sends the translation content to your site via XML-RPC', 'sitepress'),
            __('The WPML plugin publishes the content creating menus and automatically fixing links to translated content.', 'sitepress'),
        );
    ?>
    
    <ul>
        <?php foreach($steps as $number => $item): ?>
            <li><?php echo $number + 1 . '. ' . $item;?></li>
        <?php endforeach;?>
    </ul>
    

    <h3><?php echo __('What happens if you edit the original?', 'sitepress'); ?></h3>
    
    <?php do_action('icl_menu_footer'); ?>
    
</div>