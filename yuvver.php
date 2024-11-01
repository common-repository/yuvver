<?php
/*
 Plugin Name: Yuvver
 Plugin URI:  http://yuvver.com
 Description: Yuvver allows your clients share your Website, Products or Affiliate Program with their friends by E-mail
 Author:      Tsachi Ben Ezra
 Author URI:  http://yuvver.com
 Version:     1.4.1
 Text Domain: yuvver
 Domain Path: /languages
 */


/******************************
 * global variables
 ******************************/

$yuvver_base_plugin_url = plugin_dir_url(__FILE__);
$yuvver_base_url = 'http://yuvver.com/';
$yuvver_varify_url = $yuvver_base_url.'temp_user/str_verification/';
$yuvver_check_varify_url = $yuvver_base_url.'temp_user/check_verification/';
$yuvver_options = get_option('yuvver_settings');

if(!defined('ALTERNATE_WP_CRON'))
{
	define('ALTERNATE_WP_CRON', true);
}

/******************************
 * localization
 ******************************/

function yuvver_load_textdomain() {
	
	load_plugin_textdomain( 'yuvver', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	
	if(isset($_POST['do_send_emails']) && $_POST['do_send_emails'] == 1) {
	
		yuvver_schedule_send_emails();
	
	}
	
}

add_action( 'init', 'yuvver_load_textdomain' );


/******************************
 * includes
 ******************************/
include ('includes/schedule_exec.php');
include ('includes/db.php');
include ('includes/scripts.php');
include ('includes/admin_page.php');
include ('includes/yuvver_post_type.php');

register_activation_hook(__FILE__, 'yuvver_activation');
register_deactivation_hook(__FILE__, 'yuvver_deactivation');
