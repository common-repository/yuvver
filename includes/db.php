<?php
function yuvver_activation() {
	
	wp_schedule_event( time(), '5min', 'yuvver_send_mails');
		
	global $wpdb;

	$table_name = $wpdb->prefix . "yuvver";

	if($wpdb->get_var('SHOW TABLES LIKE '. $table_name) != $table_name)
	{
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
				id BIGINT(22) UNSIGNED NOT NULL AUTO_INCREMENT,
				email VARCHAR(255) NOT NULL DEFAULT '',
				subject VARCHAR(255) NOT NULL DEFAULT '',
				body TEXT NOT NULL DEFAULT '',
				PRIMARY KEY (id)
		) $charset_collate ENGINE=InnoDB;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$_version = '1.1';
		
		if(get_option('yuvver_database_version'))
		{
			update_option('yuvver_database_version', $_version);
		}
		else
		{
			add_option('yuvver_database_version', $_version);
		}
	}
}

function yuvver_deactivation() {
	
	wp_clear_scheduled_hook('yuvver_send_mails');
	
}

