<?php

function yuvver_custom_cron_intervals( $intervals ) {

	$intervals['5min'] = array(
			'interval' => (60 * 5),
			'display' => __( '5 minutes', 'yuvver' )
	);

	$intervals['15min'] = array(
			'interval' => (60 * 15),
			'display' => __( '15 minutes', 'yuvver' )
	);

	$intervals['30min'] = array(
			'interval' => (60 * 30),
			'display' => __( '30 minutes', 'yuvver' )
	);

	return $intervals;

}

add_action( 'cron_schedules', 'yuvver_custom_cron_intervals' );

function yuvver_schedule_send_emails() {

	global $wpdb;
	global $yuvver_options;
	
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
	
	$table_name = $wpdb->prefix . "yuvver";
	
	$emails = $wpdb->get_results("SELECT * FROM $table_name LIMIT 50");
	
	$curName = get_bloginfo( 'name' );
	$curLang = substr(get_bloginfo( 'language' ), 0, 2);
	$curDir = is_rtl() ? 'rtl' : 'ltr';

	$body_start = '<!DOCTYPE html>
					<html dir="'.$curDir.'" xml:lang="'.$curLang.'" lang="'.$curLang.'">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
						<title>'.$curName.' - Email</title>
					</head>
					<body style="font-family: arial;padding: 0px;margin: 0px;" dir="'.$curDir.'">';
	
	$body_end = "</body></html>";
	
	foreach ($emails as $value)
	{
		$to = $value->email;
		$subject = $value->subject;
		$message = $body_start.$value->body.$body_end;
		
		if(wp_mail($to, $subject, $message, $headers))
		{
			$wpdb->delete($table_name, array( 'id' => $value->id ) );
		}

	}

}

add_action( 'yuvver_send_mails', 'yuvver_schedule_send_emails' );