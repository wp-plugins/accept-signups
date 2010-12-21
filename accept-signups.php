<?php
/**
* Plugin Name: Accept Signups
* Plugin URI: http://clearcrest.net
* Description: Accept signups by email. Logs email, IP and timestamp. All data available from admin panel. Intended for use with external subscription services or your own email client.
* Author: Kristoffer Hell (info@clearcrest.net)
* Version: 0.1
* Author URI: http://clearcrest.net
*/

/**
* Activate
*/
function acceptSignupsActivate() {
	# add options..
	update_option('accept-signups-message', 'Enter an email and press submit to signup to our newsletter');
	update_option('accept-signups-submit-text', 'Submit');
	update_option('accept-signups-email-field-size', '44');
	update_option('accept-signups-error-message', 'Please enter a valid email..');
	update_option('accept-signups-email-already-exists', 'email already exists');
	update_option('accept-signups-email-saved', 'email saved');
	# create table..
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl) {
		$sql = "CREATE TABLE " . $tbl . " (
					email VARCHAR(255) NOT NULL,
					ip VARCHAR(45) NOT NULL,
					timestamp TIMESTAMP NOT NULL,
					PRIMARY KEY  (email)
					);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
register_activation_hook( __FILE__, 'acceptSignupsActivate' );

/**
* Deactivate
*/
function acceptSignupsDeactivate() {
	# delete options..
	delete_option('accept-signups-message');
	delete_option('accept-signups-submit-text');
	delete_option('accept-signups-email-field-size');
	delete_option('accept-signups-error-message');
	delete_option('accept-signups-email-already-exists');
	delete_option('accept-signups-email-saved');
	# drop table..
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl'") != $tbl) {
		$sql = "DROP TABLE " . $tbl . ";";
		$wpdb->query($sql);
	}
}
register_deactivation_hook( __FILE__, 'acceptSignupsDeactivate' );

/**
* Initialize
*/
function setAcceptSignupsStyle() {
    wp_register_style($handle = 'accept-signups', $src=plugins_url('/css/style.css', __FILE__));
    wp_enqueue_style('accept-signups');
}
add_action('admin_print_styles', 'setAcceptSignupsStyle');

/** 
* Generate signup form (shortcode)
*/
function acceptSignups() {
	$ajax =  '<script type="text/javascript">
				function acceptSignupsHandleSubmit() {

					email = document.getElementById("acceptSignupsEmail").value;

					if (!acceptSignupsIsValidEmail(email)) {

						alert("' . getAcceptSignupsErrorMessage() . '");

					} else {
					
						var ASAjax;
			
						var getRequest = "' . plugins_url('/accept-signups_submit.php', __FILE__) . '";	
						
						getRequest += "?email=" + email;
						
						if (window.XMLHttpRequest) {
							// code for IE7+, Firefox, Chrome, Opera, Safari
							ASAjax = new XMLHttpRequest();
						} else {
							// code for IE6, IE5
							ASAjax = new ActiveXObject("Microsoft.XMLHTTP");
						}

						ASAjax.onreadystatechange=function() {
							if (ASAjax.readyState==4 && ASAjax.status==200) {
								document.getElementById("acceptSignups").innerHTML=ASAjax.responseText;
							}
						}
						
						ASAjax.open("GET",getRequest,true);
						ASAjax.send();
						
					}

					return false;
				}	

				function acceptSignupsIsValidEmail(e) {
					var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
					return reg.test(e);
				}

	</script>';
	$html = '<div id="acceptSignups"><p>' . getAcceptSignupsMessage() . '</p><p><form id="acceptSignupsForm" name="acceptSignupsForm" action="#" onsubmit="return acceptSignupsHandleSubmit();" method="POST"><p><input name="acceptSignupsEmail" id="acceptSignupsEmail" type="text" size="' . getAcceptSignupsEmailFieldSize() . '" /></p><p><input name="acceptSignupsSubmit" id="acceptSignupsSubmit" type="submit" value="' . getAcceptSignupsSubmitText() . '"/></p></form></p></div>';
	return $ajax . $html;
}
add_shortcode('accept_signups', 'acceptSignups'); 

function getAcceptSignupsMessage() {
	return get_option('accept-signups-message');
}

function getAcceptSignupsSubmitText() {
	return get_option('accept-signups-submit-text');
}

function getAcceptSignupsEmailFieldSize() {
	return get_option('accept-signups-email-field-size');
}

function getAcceptSignupsErrorMessage() {
	return 	get_option('accept-signups-error-message');
}

/**
* Admin
*/

add_action('admin_menu', 'acceptSignupsAdmin');

function acceptSignupsAdmin() {
  add_options_page('Accept Signups Options', 'Accept Signups', 'manage_options', 'accept-signup-options', 'acceptSignupsOptions');
}

function acceptSignupsOptions() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  $html = '';
  # Handle changed options..
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-message', $_POST["accept-signups-admin-options-message"]);
  }
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-submit-text', $_POST["accept-signups-admin-options-submit"]);
  }
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-email-field-size', $_POST["accept-signups-admin-options-field-size"]);
  }
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-error-message', $_POST["accept-signups-error-message"]);
  }
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-email-already-exists', $_POST["accept-signups-email-already-exists"]);
  }
  if (isset($_POST["accept-signups-admin-options-message"])){
	update_option('accept-signups-email-saved', $_POST["accept-signups-email-saved"]);
  }
  # Handle deleted signups..
  if (isset($_POST["accept-signups-delete-form-call"])){
	$msg = '';
	$t1 = '';
	foreach($_POST as $k=>$v) {
			if ($v == 'on') {
				$t1 = explode('?', $k);
				$d[] = acceptSignupsDecode($t1[1]);
			}
	}
	foreach($d as $k=>$v) {
			$msg .= deleteEmail($v);			
	}
//	echo $msg;
  }
  
  $html .= '<div id="accept-signups-admin">
				<h2>Accept signups</h3>
				<p>Accept signups by email. Logs email, IP and timestamp. All data available from admin panel. Intended for use with external subscription services or your own email client.</p>
				<h3>Usage</h3>
					<ul style="list-style:none; padding-left:12px;"><li>
						<p>To create a sign-up post or page, insert this code snippet on the page or post: <b>[accept_signups]</b>
						<p>If you deactivate the plugin, the database table with all the emails of users who have signed up will be deleted.</p>
						<p>Unless you also delete the <i>Accept Signups</i> plugin from the file system, you can still find the data in the <i>Accept Signups</i> plugins directory in the <i>accept-signups.xml</i> file or the <i>accept-signups.csv</i> file.</p>
					</li></ul>	
				<h3>Settings</h3>
					<p>Customize the text snippets seen by the user:</p>
					<div id="accept-signups-admin-options-div">
						<form name="accept-signups-admin-options-form" action="" method="POST">
							<input type="hidden" name="page" value="accept-signup-options">
							<table border="0" cellpadding="4" cellspacing="4">
								<tr><td>Message:</td><td> <input type="text" name="accept-signups-admin-options-message" size="100" value="' . get_option("accept-signups-message") . '" /></td></tr>
								<tr><td>Submit button text:</td><td> <input type="text" name="accept-signups-admin-options-submit" value="' . get_option('accept-signups-submit-text') . '" /></td></tr>
								<tr><td>Email field size:</td><td> <input type="text" name="accept-signups-admin-options-field-size" size="6" value="' . get_option('accept-signups-email-field-size') . '" /></td></tr>
								<tr><td>Error message:</td><td> <input type="text" name="accept-signups-error-message" size="100" value="' . get_option('accept-signups-error-message') . '" /></td></tr>
								<tr><td>Email already exists:</td><td> <input type="text" name="accept-signups-email-already-exists" size="100" value="' . get_option('accept-signups-email-already-exists') . '" /></td></tr>
								<tr><td>Email saved:</td><td> <input type="text" name="accept-signups-email-saved" size="100" value="' . get_option('accept-signups-email-saved') . '" /></td></tr>
								<tr><td>&nbsp;</td><td><input type="submit" value="update" /></td></tr>
							</table>
						</form>
					</div>	
				<h3>Signups</h3>' .  acceptSignupsGetSignups() . '</div>';
				acceptSignupsCreateXMLDoc();
				acceptSignupsCreateCSVDoc();
  echo $html;
}

function acceptSignupsCreateXMLDoc() {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$sql = 'select email, ip, timestamp from ' . $tbl . ' order by email;';
	$r = $wpdb->get_results($sql, ARRAY_A);
	$xml = '<accept-signups>';
	foreach($r as $k=>$v) {
		$xml .= '<signup email="' . $v["email"] . '" ip="' . $v["ip"] . '" timestamp="' . $v["timestamp"] . '" />';
	}
	$xml .= '</accept-signups>';
	file_put_contents(ABSPATH . 'wp-content/plugins/accept-signups/accept-signups.xml', $xml);
}

function acceptSignupsCreateCSVDoc() {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$sql = 'select email, ip, timestamp from ' . $tbl . ' order by email;';
	$r = $wpdb->get_results($sql, ARRAY_A);
	$csv = '';
	foreach($r as $k=>$v) {
		$csv .= $v["email"] . ',' . $v["ip"] . ',' . $v["timestamp"] . "\n";
	}
	file_put_contents(ABSPATH . 'wp-content/plugins/accept-signups/accept-signups.csv', $csv);
}

function acceptSignupsCreateCopyPasteList() {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$sql = 'select email, ip, timestamp from ' . $tbl . ' order by email;';
	$r = $wpdb->get_results($sql, ARRAY_A);
	$list = '';
	foreach($r as $k=>$v) {
		$list .= $v["email"] . ',';
	}
	return substr($list, 0, strlen($list)-1);
}

function acceptSignupsGetSignups() {
	global $wpdb;
	$html = '<form name="accept-signups-delete-form" action="" method="POST"><input type="hidden" name="accept-signups-delete-form-call">
	<table border="1" cellpadding="5" cellspacing="5"><tr><td valign="top"><table id="acceptSignupsTable" border="0" cellpadding="7" cellspacing="0"> 
		<p><!-- HEADERS --></p>
		<script type="text/javascript"> 
		
			jQuery(document).ready(function(){
				jQuery("#acceptSignupsDeleteToggle").click(function(event){
				
					if (jQuery("input[id=acceptSignupsDeleteCB]").attr("checked")) {
						jQuery("input[id=acceptSignupsDeleteCB]").attr("checked", false);
					} else {
						jQuery("input[id=acceptSignupsDeleteCB]").attr("checked", true);
					}
				});
			});			
		
		</script>	
		<tr> 
			<td valign="top" align="center" class="acceptSignupsHeaderCell">&nbsp;email&nbsp;</td> 
			<td valign="top" align="center" class="acceptSignupsHeaderCell">&nbsp;ip&nbsp;</td> 
			<td valign="top" align="center" class="acceptSignupsHeaderCell">&nbsp;timestamp&nbsp;</td> 
			<td valign="top" align="center" class="acceptSignupsHeaderCell" id="acceptSignupsDeleteToggle">&nbsp;delete?&nbsp;</td> 
		</tr> 
		<p><!-- DATA --></p>';
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$sql = 'select email, ip, timestamp from ' . $tbl . ' order by timestamp desc;';
	$r = $wpdb->get_results($sql, ARRAY_A);

	$email = '';
	foreach($r as $k=>$v) {
		$html .= '<tr id="acceptSignupsRow">';
		foreach ($v as $k1=>$v1) {
			if (strpos($v1, '@')) {
				$email = $v1;
			}
			$html .= '<td valign="top" align="center" class="acceptSignupsCell">&nbsp;&nbsp;' . $v1 . '&nbsp;&nbsp;</td>'; 
		}
		$html .= '<td valign="top" align="center" class="acceptSignupsCell" valign="bottom"><input type="checkbox" name="acceptSignupsDeleteCB?' . acceptSignupsEncode($email) . '" id="acceptSignupsDeleteCB"></td>'; 
		$html .= '</tr>';
	}

	$html .= '<tr><td colspan="4" class="acceptSignupsBottomCell" align="right"><input type="submit" value="delete"></td></tr>';
	$html .=  '</table>
	</form>
	</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign="top"><p>Right-click to save..</p>
	<a href="' . $src=plugins_url('/accept-signups.xml', __FILE__). '" target="_blank"><img src="' . $src=plugins_url('/img/doc_xml_icon.png', __FILE__). '" border="0" /></a>
	&nbsp;&nbsp;<a href="' . $src=plugins_url('/accept-signups.csv', __FILE__). '" target="_blank"><img src="' . $src=plugins_url('/img/CSV-icon.png', __FILE__). '" border="0" /></a>';	
	
	$html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;</td></td><td valign="top"><p>Ready to copy-and-paste into email..</p><textarea rows="11" cols="22" id="acceptSignupsTextarea">' . acceptSignupsCreateCopyPasteList() . '</textarea></td></tr></table>';	
		
	return $html;
}
 
 function acceptSignupsEncode($s) {
	return urlencode(str_replace('.','#',$s));
 }

 function acceptSignupsDecode($s) {
	return str_replace('#','.',urldecode($s));
 }

function deleteEmail($e) {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$q = "delete from " .$tbl . " where email = '" . $e . "';";
	return $wpdb->query($q);
} 

 
?>
