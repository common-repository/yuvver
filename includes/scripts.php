<?php
function yuvver_init_page() {
	wp_register_script( 'yuvver', '', array( 'jquery' ) );
	wp_enqueue_script( 'yuvver' );
}
add_action( 'init', 'yuvver_init_page' );

function yuvver_add_js() {
	
	global $yuvver_base_url;
	global $yuvver_options;
	
	echo "<script type=\"text/javascript\">
			(function(d){
				var scr = d.createElement('script');
				scr.src = '".$yuvver_base_url."api/get_code/".$yuvver_options['yuvver_id']."';
				scr.type = 'text/javascript';
				scr.async = true;
				d.getElementsByTagName('head')[0].appendChild(scr);
			}(document));
						
			function yuvver_inviteYourFriends(postId) { 

				if(!$)
				{
					var $ = jQuery;	
				}
					
				if(typeof __yuvver_init === 'function')
				{
					__yuvver_init(function (emails, userData) { 
								
						$.ajax({
							url		 	: 	'".admin_url('admin-ajax.php')."',
							type    	: 	'POST',
							data		:	{
								action		: 'yuvver_send_invitations',
								userData  	: userData,
								emails  	: emails,
								postId		: postId
							},
							success  	: 	function(data) {
								
								//console.log(data);
									
								var d = $.parseJSON(data);
								
								console.log(d);
								
								if(d['status'])
								{
									switch (d['status']) 
									{
										case 'ok':
										{
											alert(d['view']);
										}
										break;
										case 'error':
										{
											alert(d['view']);
										}
										break;
									}
								}							
							}
						});
			
					}); 		
				}
				else
				{
					$.ajax({
						url		 	: 	'".admin_url('admin-ajax.php')."',
						type    	: 	'POST',
						data		:	{
							action		: 'yuvver_send_error'
						},
						success  	: 	function(data) {
							
							var d = $.parseJSON(data);
								
							console.log(d);
							
							alert(d['view']);	
															
						}
					});
					
				}
				
			};
						
		</script>";
}
add_action( 'wp_footer', 'yuvver_add_js' );

function yuvver_button( $atts, $content = null) {
	
	$a = shortcode_atts( array(
		'id' => 0,
		'class' => 'yuvver_button_style',
	), $atts );
	
	return '<button class="'.$a['class'].'" onClick="yuvver_inviteYourFriends('.$a['id'].')">'.$content.'</button>';
}
add_shortcode( 'yuvver', 'yuvver_button' );

function yuvver_insert_data($emails) {

	global $wpdb;

	$table_name = $wpdb->prefix . "yuvver";

	foreach ($emails as $email)
	{
		$values[] = '\''.implode('\',\'', $email).'\'';
	}

	$values = '('.implode('),(', $values).')';

	$wpdb->query("INSERT INTO $table_name (	email,
			subject,
			body) VALUES {$values}");

}

function yuvver_send_invitations() {
	
	//global $wpdb; // this is how you get access to the database
	
	$response['status'] = 'error';
	
	if(isset($_POST['postId']))
	{
		$subject = get_the_title($_POST['postId']);	
		$content = get_post_meta($_POST['postId'], 'yuvver_invitation', true);	
		
		if(isset($_POST['userData']))
		{
			$content = str_replace('{{client_name}}', urldecode($_POST['userData']['name']), $content);
		}
		
		if(isset($_POST['emails']) && is_array($_POST['emails']) && !empty($_POST['emails']))
		{
			$response['status'] = 'ok';
			
			$emails_db = array();
			
			foreach ($_POST['emails'] as $value)
			{
				$temp_subject = $subject;
				$temp_body = $content;
				
				if(isset($value['name']))
				{
					$temp_body = str_replace('{{friend_name}}', urldecode($value['name']), $temp_body);
				}
				
				if(isset($_POST['userData']))
				{
					$temp_subject = str_replace('{{client_name}}', urldecode($_POST['userData']['name']), $temp_subject);
				}
	
				$emails_db[] = array(
					'email' => $value['email'],
					'subject' => $temp_subject,
					'body' => $temp_body,
				);
				
			}
			
			if(!empty($emails_db))
			{
				yuvver_insert_data($emails_db);				
			}
						
			$response['view'] = __('Your friends will get your invitation shortly.', 'yuvver');
			
		}
		else
		{
			$response['view'] = __('Fatal Error - Emails not selected', 'yuvver');
		}
	}
	else
	{
		$response['view'] = __("Fatal Error - Don't find invitation ID", 'yuvver');		
	}
	
	echo json_encode($response);exit();
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action('wp_ajax_yuvver_send_invitations', 'yuvver_send_invitations' );
add_action("wp_ajax_nopriv_yuvver_send_invitations", "yuvver_send_invitations");

function yuvver_send_error() {
	
	//global $wpdb; // this is how you get access to the database
	
	$response['view'] = __('Fatal Error - Application is not activated', 'yuvver');	
	
	$admins = get_users(array('role' => ('administrator')));
	
	if(is_array($admins) && !empty($admins))
	{
		$multiple_recipients = array();
		
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
		
		foreach ($admins as $value)
		{
			$subj = get_bloginfo('url').' - Yuvver plugin error';
			
			$body = 'Hi '.$value->data->display_name.',<br />';
			$body .= '<br />';
			$body .= 'Someone try to send an invitation from your website.';
			$body .= '<br />';
			$body .= 'Please go to <a href="http://yuvver.com/">Yuvver.com</a> and check if your domain is verified';
			$body .= '<br />';
			$body .= 'OR go to your plugin settings and check if your API key is set';
			$body .= '<br />';
			$body .= '<br />';
			$body .= 'Thanks yuvver team';
			
			wp_mail( $value->data->display_name, $subj, $body, $headers);			
			
		}
		
	}
	
	
	echo json_encode($response);exit();
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action('wp_ajax_yuvver_send_error', 'yuvver_send_error' );
add_action("wp_ajax_nopriv_yuvver_send_error", "yuvver_send_error");