<?php

// Theme options shamelessly stolen from the Thematic Framework, which itself adapted from "A Theme Tip For WordPress Theme Authors"
// http://literalbarrage.org/blog/archives/2007/05/03/a-theme-tip-for-wordpress-theme-authors/

$themename = "Jenny";
$shortname = "p2h";
$version = "1.4";

//Header Customizations
function p2h_wp_head() { 
		
	// Custom CSS block in Theme Options Page
	$custom_css = get_option('p2h_custom_css');
	if($custom_css != '')
	{
	$output = '<style type="text/css">'."\n";
	$output .= $custom_css . "\n";
	$output .= '</style>'."\n";
	echo $output;
	}

	
	//If Set in Theme Options, Add Feed URL in Head
	if(get_option('p2h_feedurl') != '') {
	echo '<link rel="alternate" type="application/rss+xml" href="'. get_option('p2h_feedurl') .'" title="'. get_bloginfo('name') .' RSS Feed"/>'."\n";
	}

}
add_action('wp_head','p2h_wp_head');

	
//Header Customization -- Remove Auto Feed URL
if(get_option('p2h_feedurl') != '') {
	// Remove the links to feed
	remove_action( 'wp_head', 'feed_links', 2);
}

// Remove the links to the extra feeds such as category feeds
if(get_option('p2h_cleanfeedurls') !='' ) {
remove_action( 'wp_head', 'feed_links_extra', 3 ); 
}

//Automatically List StyleSheets in Folder
$alt_stylesheet_path = TEMPLATEPATH . '/styles/';
$alt_stylesheets = array();

if ( is_dir($alt_stylesheet_path) ) {
    if ($alt_stylesheet_dir = opendir($alt_stylesheet_path) ) { 
        while ( ($alt_stylesheet_file = readdir($alt_stylesheet_dir)) !== false ) {
            if((stristr($alt_stylesheet_file, ".css") !== false) && (stristr($alt_stylesheet_file, "default") == false)){
                $alt_stylesheets[] = $alt_stylesheet_file;
            }
        }    
    }
}
array_unshift($alt_stylesheets, "default.css"); 


// Create theme options
global $options;
$options = array (

array( "name" => $themename." Options",
	"type" => "title"),

array( "name" => __('General','jenny'),
	"type" => "section"),
	
array( "type" => "open"),

array( "name" => "Colour Scheme",
	"desc" => "Select a colour scheme for the theme",
	"id" => $shortname."_alt_stylesheet",
	"type" => "select",
	"options" => $alt_stylesheets,
	"std" => "default.css"),

array( "name" => __('Custom Feed URL','jenny'),
	"desc" => __('You can use your own feed URL (<strong>with http://</strong>). Paste your Feedburner URL here to let readers see it in your website.','jenny'),
	"id" => $shortname."_feedurl",
	"type" => "text",
	"std" => get_bloginfo('rss2_url')),
	
array( "name" => __('Delete Extra Feeds','jenny'),
	"desc" => __('WordPress adds feeds for categories, tags, etc., by default. Check this box to remove them and reduce the clutter.','jenny'),
	"id" => $shortname."_cleanfeedurls",
	"type" => "checkbox",
	"std" => ""),

array( "name" => __('Twitter ID','jenny'),
	"desc" => __('Your Twitter user name, please. It will be shown in the navigation bar. Leaving it blank will keep the Twitter icon supressed.','jenny'),
	"id" => $shortname."_twitterid",
	"type" => "text",
	"std" => ""),

array( "name" => __('Facebook Page','jenny'),
	"desc" => __('Link to your Facebook page, <strong>with http://</strong>. It will be shown in the navigation bar. Leaving it blank will keep the Facebook icon supressed.','jenny'),
	"id" => $shortname."_facebookid",
	"type" => "text",
	"std" => ""),

array( "name" => __('Custom Styles','jenny'),
	"desc" => __('Want to add any custom CSS code? Put in here, and the rest is taken care of. This overrides any other stylesheets. eg: a.button{color:green}','jenny'),
	"id" => $shortname."_custom_css",
	"type" => "textarea",
	"std" => ""),		

array( "name" => __('Analytics/Tracking Code','jenny'),
	"desc" => __('You can paste your Google Analytics or other website tracking code in this box. This will be automatically added to the footer.','jenny'),
	"id" => $shortname."_analytics_code",
	"type" => "textarea",
	"std" => ""),	

array( "type" => "close"),

//ADVERTISEMENTS --- POST ADS 
array( "name" => "Advertisements -- Post Pages",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Ad Code - Above Posts",
					"desc" => "Enter your Adsense code (or other ad network code) here. This ad will be displayed only on <strong>Post Pages</strong> at the beginning of the posts, below the title. It is very basic and effective option for putting ads on your blog. If you want more functionality, get a specialized Ad plugin from <a href='http://wordpress.org/extend/plugins/'>WordPress</a>.",
					"id" => $shortname."_posttop_adcode",
					"std" => "",
					"type" => "textarea"),

array( "name" => "Ad Code - Below Posts",
					"desc" => "Enter your Adsense code (or other ad network code) here. This ad will be displayed only on <strong>Post Pages</strong> at the end of the posts. Please make sure that you don't activate more ads than what is allowed by your ad network. Adsense allows up to 3 on one page.",
					"id" => $shortname."_postend_adcode",
					"std" => "",
					"type" => "textarea"),

array( "type" => "close"),

);

		
function p2h_add_admin() {

    global $themename, $shortname, $options;

	if ( isset ( $_GET['page'] ) && ( $_GET['page'] == basename(__FILE__) ) ) {

		if ( isset ($_REQUEST['action']) && ( 'save' == $_REQUEST['action'] ) ){

			foreach ( $options as $value ) {
				if ( array_key_exists('id', $value) ) {
					if ( isset( $_REQUEST[ $value['id'] ] ) ) { 
						update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
					} 
					else {
						delete_option( $value['id'] );
					}
				}
			}
			$_REQUEST['saved'] = 1;
		} 
		else if ( isset ($_REQUEST['action']) && ( 'reset' == $_REQUEST['action'] ) ) {
			foreach ($options as $value) {
				if ( array_key_exists('id', $value) ) {
					delete_option( $value['id'] );
				}
			}
			$_REQUEST['reset'] = 1;
		}
	}

add_menu_page($themename, $themename, 'administrator', basename(__FILE__), 'p2h_admin');
add_submenu_page(basename(__FILE__), $themename . ' Theme Options', 'Theme Options', 'administrator',  basename(__FILE__),'p2h_admin'); // Default
}

function p2h_add_init() {

$file_dir=get_bloginfo('template_directory');
wp_enqueue_style("p2hCss", $file_dir."/functions/theme-options.css", false, "1.0", "all");
wp_enqueue_script("p2hScript", $file_dir."/functions/theme-options.js", false, "1.0");

}

function p2h_admin() {

    global $themename, $shortname, $version, $options;
	$i=0;

	if ( isset ($_REQUEST['saved']) && ($_REQUEST['saved'] ) )echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
	if ( isset ($_REQUEST['reset']) && ($_REQUEST['reset'] ) ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
    
?>

<div class="wrap ">
<div class="rm_wrap">
<h2><?php echo $themename; ?> Settings</h2>
 
<div class="rm_opts">

<form method="post">

<?php foreach ($options as $value) { 

switch ( $value['type'] ) {
case "open":
?>
 
<?php 
break;
case "close":
?>
	</div>
	</div>
	<br />

 
<?php break;
case "title":
?>
	<p>Use the following menus to easily customize <?php echo $themename;?> WordPress theme.</p> 

<?php 
break;
case 'text':
?>

	<div class="rm_input rm_text">
		<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
		<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo stripslashes(get_option( $value['id'])  ); } else { echo $value['std']; } ?>" />
		<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
	</div>

<?php
break;
case 'textarea':
?>
	<div class="rm_input rm_textarea">
		<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
		<textarea name="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( get_option( $value['id'] ) != "") { echo stripslashes(get_option( $value['id']) ); } else { echo $value['std']; } ?></textarea>
		<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
	 </div>

<?php 
break;
case 'select':
?>
	<div class="rm_input rm_select">
		<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
		<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
		<?php foreach ($value['options'] as $option) { ?>
				<option <?php if (get_option( $value['id'] ) == $option) { echo 'selected="selected"'; } ?>><?php echo $option; ?></option><?php } ?>
		</select>
		<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
	</div>

<?php
break;
case "radio":
?>
	<div class="rm_input rm_select">
		<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
		  <?php foreach ($value['options'] as $key=>$option) { 
			$radio_setting = get_option($value['id']);
			if($radio_setting != ''){
				if ($key == get_option($value['id']) ) {
					$checked = "checked=\"checked\"";
					} else {
						$checked = "";
					}
			}else{
				if($key == $value['std']){
					$checked = "checked=\"checked\"";
				}else{
					$checked = "";
				}
			}?>
			<input type="radio" name="<?php echo $value['id']; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?> /><?php echo $option; ?><br />
			<?php } ?>
		<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
	</div>

<?php
break;
case "checkbox":
?>
	<div class="rm_input rm_checkbox">
		<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
		<?php if(get_option($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = "";} ?>
		<input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />
		<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
	 </div>


<?php break; 
case "section":
$i++;
?>
	<div class="rm_section">
	<div class="rm_title"><h3><img src="<?php bloginfo('template_directory')?>/functions/images/trans.png" class="inactive" alt="""><?php echo $value['name']; ?></h3>
	<span class="submit"><input name="save<?php echo $i; ?>" type="submit" value="Save changes" /></span>
	<div class="clearfix"></div>
	</div>
	<div class="rm_options">

<?php
break;
}
}
?>

<input type="hidden" name="action" value="save" />
</form>


<form method="post">
<p class="submit">
<input name="reset" type="submit" value="Reset" />
<input type="hidden" name="action" value="reset" />
</p>
</form>
<br/>
</div>
</div>

<div class="sidebox">
	<h2>Support Jenny!</h2>
	<p>You are using <strong><a href="http://www.speckygeek.com/jenny-free-wordpress-theme/">Jenny <?php echo $version; ?></a></strong>, a free theme by <a href="http://www.speckygeek.com">Specky Geek</a>, a technology blog.</p>
	<p>It has taken a lot of effort to make it as good as a premium theme. If you find it useful, why not reward me for my hard work! Be generous and send me as many dollars as you can.</p>

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="6WPPE2PW6ERVC">
	<table>
	<tr><td><input type="hidden" name="on0" value="Reward for Jenny WP Theme">Reward for Jenny WP Theme</td></tr><tr><td><select name="os0">
		<option value="Five Dollars">Five Dollars $5.00</option>
		<option value="Ten Dollars">Ten Dollars $10.00</option>
		<option value="Fifteen Dollars">Fifteen Dollars $15.00</option>
		<option value="Twenty Dollars">Twenty Dollars $20.00</option>
		<option value="Twenty Five Dollars">Twenty Five Dollars $25.00</option>
		<option value="Thirty Five Dollars">Thirty Five Dollars $35.00</option>
		<option value="Fifty Dollars">Fifty Dollars $50.00</option>
		<option value="Hundred Dollars">Hundred Dollars $100.00</option>
	</select> </td></tr>
	</table>
	<input type="hidden" name="currency_code" value="USD">
	<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_paynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
	<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
	</form>

	<hr />
	<ul>
	<li><a href="http://www.speckygeek.com/jenny-free-wordpress-theme/">Help/Updates for Jenny</a></li>
	<li><a href="http://www.speckygeek.com/wordpress-themes/">Free WordPress Themes</a></li>
	<li><a href="http://www.speckygeek.com/contact-us/">Contact Specky Geek</a></li>
	</ul>
</div>

	</div>
<?php
}
add_action('admin_init', 'p2h_add_init');
add_action('admin_menu' , 'p2h_add_admin'); 
?>
