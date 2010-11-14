<?php
/*
Plugin Name: Confirm User Registration
Plugin URI: http://confirm-user-registration.horttcore.de/
Description: Admins have to confirm a user registration - a notification will be send when the account gets activated
Author: Ralf Hortt
Version: 1.2.2
Author URI: http://horttcore.de/
*/


add_action('wp_authenticate_user','cur_authenticate');
add_action('admin_menu', 'cur_adminmenu');

register_activation_hook( __FILE__, 'cur_activate' );


//======================================
// Description: Mangement page, to de/activate users
Function cur_management(){
global $wpdb; ?>
	<script type="text/javascript">
	function checkAll(form) {
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick',2))) {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
	</script>
	<div class="wrap">
		<?php
		if($_GET['p'] == 'block') {
			cur_block_overview();
		}
		elseif($_GET['p'] == 'options') {
			cur_options();
		}
		else {
			cur_auth_overview();
		}?>
	</div><?php
	
}


//======================================
// Description: Authentication overview
Function cur_auth_overview(){
global $wpdb;
	
	if ($_POST['auth']) {
		foreach($_POST['auth'] as $id) {
			update_usermeta($id, 'authentication', '1'); #activate
			cur_authentication_mail($id);
		}
	}
	
	$users = cur_pending_users();
	?>
	<h2><?php _e("User in waiting queue")?></h2>
	<?php cur_submenu(); ?>
	<form method="post" action="users.php?page=<?php echo $_GET['page'] ?>&amp;p=auth" id="pending">	
	<table class="widefat">
		<tr class="thead">
			<th class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('pending'));" /></th>
			<th scope="col"><?php _e("Username")?></th>
			<th scope="col"><?php _e("E-Mail")?></th>
		</tr>
		<?php
		if ($users) {
			foreach($users as $user) {
				?>
				<tr>
					<th class="check-column"><input type="checkbox" name="auth[]" id="<?php echo $user->display_name ?>" value="<?php echo $user->id; ?>" id="<?php echo $user->display_name; ?>" /></th>
					<td><label for="<?php echo $user->display_name ?>"><?php echo $user->display_name; ?></label></td>
					<td><?php echo $user->user_email ?></td>
				</tr>
				<?php
			}
		}
		?>
	</table>
	<p class="submit"><button class="button" type="submit"><?php _e("Activate")?></button></p>
	</form>
	<?php
}

//======================================
// Description: Block accounts
Function cur_block_overview(){
global $wpdb, $user_ID;

	if ($_POST['auth']) {
		foreach($_POST['auth'] as $id) {
			update_usermeta($id, 'authentication', '0'); 
		}
	}
	
	$users = cur_authed_users();
	?>
	<h2><?php _e("Block User Accounts")?></h2>
	<?php cur_submenu(); ?>
	<form method="post" action="users.php?page=<?php echo $_GET['page'] ?>&amp;p=block" id="block">	
	<table class="widefat">
		<tr class="thead">
			<th class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('block'));" /></th>
			<th scope="col" onclick="checkAll(document.getElementById('block'));"><?php _e("Username")?></th>
			<th scope="col"><?php _e("E-Mail")?></th>
		</tr>
		<?php
		if ($users) {
			foreach($users as $user) {
				?>
				<tr>
					<th class="check-column"><input <?php if ($user_ID == $user->id) echo 'disabled="disabled"'; ?> type="checkbox" name="auth[]" id="<?php echo $user->display_name ?>" value="<?php echo $user->id; ?>" id="<?php echo $user->display_name; ?>" /></th>
					<td><label for="<?php echo $user->display_name ?>"><?php echo $user->display_name; ?></label></td>
					<td><?php echo $user->user_email ?></td>
				</tr>
				<?php
			}
		}
		?>
	</table>
	<p class="submit"><button class="button" type="submit"><?php _e("Deactivate")?></button></p>
	</form>
	<?php
}

//======================================
// Description: Plugin options
Function cur_options(){
global $wpdb;
	?><h2><?php _e("Registration Options")?></h2><?php
	cur_submenu();
	
	if($_POST) {
		update_option('cur_error', $_POST['cur_error']);
		update_option('cur_subject', $_POST['cur_subject']);
		update_option('cur_message', $_POST['cur_message']);
		update_option('cur_from', $_POST['cur_from']);		
	}
	?>
	<form method="post">
		<table class="form-table">
		<tr>
			<th><label for="cur_error"><?php _e("Error Message")?></label></th>
			<td><input size="82" type="text" name="cur_error" value="<?php echo get_option('cur_error'); ?>" id="cur_error"></td>
		</tr>
		<tr>
			<th><label for="cur_from"><?php _e("Authentication E-mail")?></label></th>
			<td><input size="82" name="cur_from" id="cur_from" value="<?php echo get_option('cur_from'); ?>" /></td>
		</tr>
		<tr>
			<th><label for="cur_subject"><?php _e("Authentication Subject")?></label></th>
			<td><input size="82" name="cur_subject" id="cur_subject" value="<?php echo get_option('cur_subject'); ?>" /></td>
		</tr>
		<tr>
			<th><label for="cur_message"><?php _e("Authentication Message")?></label></th>
			<td><textarea name="cur_message" rows="8" cols="80" id="cur_message"><?php echo get_option('cur_message'); ?></textarea></td>
		</tr>
		</table>
		<p class="submit"><button class="button" type="submit"><?php _e("Save Changes")?></button></p>
	</form>
	<?php
}


//======================================
// Description: Checks if the user is authenticated
Function cur_authenticate($user){
			
		$userid  = get_useridbylogin($user->user_login);
	
		if (!is_authenticated($userid)) {
			$user = new WP_Error();
			$error = get_option('cur_error');
			$user->add('error',$error);			
			return $user;
		}
		else{
			return $user;			
		}
}

//======================================
// Description: Returns a user id of a login name
// Require: User login name
Function get_useridbylogin($user_login){
global $wpdb;
	
	$sql = "SELECT ID FROM $wpdb->users WHERE user_login = '$user_login'";
	$userid = $wpdb->get_var($sql);
	
	return $userid;
}

//======================================
// Description: Conditional function if user is athenticated
// Require: valid user_ID
Function is_authenticated($userid){
	$auth = get_usermeta($userid,'authentication');
	
	if ($userid == 1) {
		return true;
	}
	elseif ($auth == 1) {
		return true;
	}
	else {
		return false;
	}
}

//======================================
// Description: Sending an E-mail to an approved user
// Require: User ID
Function cur_authentication_mail($user_id){
global $wpdb;
	$sql = "SELECT user_email FROM $wpdb->users WHERE ID = '$user_id'";
	$to = $wpdb->get_var($sql);
	$header = "FROM:".get_option('cur_from');
	$subject = get_option('cur_subject');
	$message = get_option('cur_message');
	
	if (!mail($to, $subject, $message,$header)) {
		_e("Message failed");
	}	
}


//======================================
// Description: Adding submenu entry
Function cur_adminmenu(){
	add_submenu_page('users.php','Activate User Accounts', 'Confirm User Registration', '10', __FILE__, 'cur_management');
}

//======================================
// Description: Authenticate every user that is registered already
Function cur_activate(){
global $wpdb;
	$sql="SELECT id FROM $wpdb->users";
	$col = $wpdb->get_col($sql);
	
	foreach ($col as $col) {
		update_usermeta($col, 'authentication', '1');
	}
	
	add_option('cur_error', '<strong>ERROR:</strong> Your account has to be confirmed by an administrator before you can login', '', '0');
	add_option('cur_subject', 'Account Confirmation: '.get_bloginfo('name'), '', '0');
	add_option('cur_message', "You account has been approved by an administrator!\nLogin @ ".get_bloginfo('url')."/wp-login.php\n\nThis message is auto generated\n",'','0');
	add_option('cur_administrator', get_bloginfo('admin_email')."\n",'','0');			
	add_option('cur_from', get_bloginfo('name').' <'.get_bloginfo('admin_email').">\n",'','0');				
	
}

//======================================
// Description: Plugin Submenu
Function cur_submenu(){
	?>
	<ul class="subsubsub">
		<li><a <?php if ($_GET['p'] != 'options' && $_GET['p'] != 'block') echo 'class="current"'; ?> href="users.php?page=<?php echo $_GET['page'] ?>&amp;p=auth"><?php _e("Authenticate User Accounts")?> (<?php echo count(cur_pending_users()); ?>)</a> | </li>
		<li><a <?php if ($_GET['p'] == 'block') echo 'class="current"'; ?> href="users.php?page=<?php echo $_GET['page'] ?>&amp;p=block"><?php _e("Block User Accounts")?> (<?php echo count(cur_authed_users()); ?>)</a> | </li>
		<li><a <?php if ($_GET['p'] == 'options') echo 'class="current"'; ?> href="users.php?page=<?php echo $_GET['page'] ?>&amp;p=options"><?php _e("Options")?></a></li>
	</ul>
	<?php
}

//====================================== UNDER CONSTRUCTION
// Description: Display a message when user is registered
Function cur_notify($errors){
	if	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] ) {
		$errors->add('registered', 'Your registration is pending approval by an administrator. You will receive an E-mail once it is approved', 'message');
	}
	return $errors;
}

//======================================
// Description: Returns the not authed users
// Return: object
Function cur_pending_users(){
global $wpdb;

	// GETTING NOT AUTHED USERS
	$sql = "SELECT id FROM $wpdb->users";
	$all = $wpdb->get_col($sql);
	
	$sql = "SELECT id FROM $wpdb->users INNER JOIN $wpdb->usermeta ON id = user_id WHERE (meta_key = 'authentication' AND meta_value = '1') ORDER BY display_name";
	$authed = $wpdb->get_col($sql);
	
	$pending = array();
	
	foreach($all as $id) {
		if (!in_array($id, $authed)) {
			$pending_id.= ','.$id;
		}
	}
		
	$pending_id = substr($pending_id, 1);
	
	if ($pending_id) {
		$sql = "SELECT id, display_name, user_email FROM $wpdb->users WHERE id IN ($pending_id) ORDER BY display_name";
		$users = $wpdb->get_results($sql);
	}
	return $users;
}

//======================================
// Description: Returns the authed users
// Return: object
Function cur_authed_users(){
global $wpdb;
	$sql = "SELECT id, display_name, user_email FROM $wpdb->users INNER JOIN $wpdb->usermeta ON id = user_id WHERE (meta_key = 'authentication' AND meta_value = '1') ORDER BY display_name";
	$users = $wpdb->get_results($sql);
	return $users;
}


?>