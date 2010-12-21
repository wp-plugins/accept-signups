<?php
require_once('../../../wp-config.php');
require_once('../../../wp-includes/wp-db.php');

if (true) {
	if (isset($_GET['email'])) {
		global $wpdb;
		if (strlen($_GET['email']) > 4) {
			if (hasEmail($_GET['email'])) {
				echo get_option('accept-signups-email-already-exists');
			} else {
				saveEmail($_GET['email']);
				echo get_option('accept-signups-email-saved');
			}
		}
	}
} else {
	phpinfo();
}

function saveEmail($e) {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$q = "insert into " .$tbl . " (email, ip, timestamp) value ('$e', '". $_SERVER['REMOTE_ADDR'] . "', now());";
	return $wpdb->query($wpdb->prepare($q));
} 

function hasEmail($e) {
	global $wpdb;
	$tbl = '`' . DB_NAME . '`.`' . $wpdb->prefix . 'accept-signups`';
	$q = "select count(*) from " .$tbl . " where email='" . $e . "'";
	if ($wpdb->get_var($wpdb->prepare($q)) > 0) {
		return true;
	}
	return false;
}

?>
