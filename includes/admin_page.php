<?php

function yuvver_get_site_domain() {
	
	$domain = get_bloginfo('url');

	$d = explode('://', $domain);
	
	$domain = $d[0].'://'.current(explode('/', $d[1]));
	
	return $domain;
}

function yuvver_check_domain_verified() {
	
	global $yuvver_options;
	global $yuvver_check_varify_url;

	$response = array('status' => false);
	
	$vr_code = get_option('yuvver_verification_code');
	
	if($vr_code)
	{
		
		$args = array(
			'method' => 'POST',
			'body' => array(
				'vr_code' => $vr_code,
				'domain' => yuvver_get_site_domain(),
				'key' => $yuvver_options['yuvver_id']
			)
		);
	
		$resp = wp_remote_retrieve_body(wp_remote_post($yuvver_check_varify_url, $args ));
		
		$r = json_decode($resp, true);
		
		if(isset($r['status']))
		{
			if($r['status'] == 'ok')
			{
				$response['status'] = true;
				$response['need_key'] = false;
			}			
			elseif($r['status'] == 'need_key')
			{
				$response['status'] = true;
				$response['need_key'] = true;				
			}
		}		
	}
	
	return $response;
}

function yuvver_verify_domain() {

	global $yuvver_varify_url;
	
	$response = array('status' => false);
	
	if(isset($_POST['code']))
	{
		if(empty($_POST['code']))
		{
			$response['error_msg'] = __('Please fill in code verification', 'yuvver');
		}
		else
		{
			$args = array(
				'method' => 'POST',
				'body' => array(
					'domain' => yuvver_get_site_domain(),
					'code' => $_POST['code']
				)
			);
			
			$resp = wp_remote_retrieve_body(wp_remote_post($yuvver_varify_url, $args ));
			
			$r = json_decode($resp, true);
			
			if(isset($r['status']))
			{
				if($r['status'] == 'ok')
				{
					$response['ok_msg1'] = __('Congrats, your domain is verified.', 'yuvver');
					$response['ok_msg2'] = __('Final step, copy your API key', 'yuvver').' <strong>'.$r['key'].'</strong> '.__('into the field and press <strong>Save</strong>', 'yuvver');
					$response['status'] = true;
					
					if(get_option('yuvver_verification_code'))
					{						
						update_option('yuvver_verification_code', $_POST['code']);						
					}
					else
					{
						add_option('yuvver_verification_code', $_POST['code']);						
					}					
				}
				else 
				{
					$response['error_msg'] = __('Verify code did not match!', 'yuvver');					
				}
			}
			else
			{				
				$response['error_msg'] = __('Fatal error', 'yuvver');
			}			
		}
	}
	
	return $response;
	
}

function yuvver_options_page() {
	
	global $yuvver_options;
	
	$yuvver_options_api_key = $yuvver_options['yuvver_id'];
	
	if(isset($_POST['do_verify']) && $_POST['do_verify'] == 1)
	{
		$verified = yuvver_verify_domain();
	}
	else
	{
		$verified = yuvver_check_domain_verified();		
	}
	
	ob_start(); ?>
	
	<div class="wrap">
	
		<h2>Yuvver <?php echo _e('Settings', 'yuvver'); ?></h2>
		
		<?php if($verified['status']): ?>
			
			<?php if(isset($verified['ok_msg1'])): ?>
			
				<div class="updated notice is-dismissible">
					<p><strong><?php echo $verified['ok_msg1']; ?></strong></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'yuvver'); ?></span></button>
				</div>
				
			<?php endif; ?>
			<?php if(isset($verified['ok_msg2'])): ?>
			
				<div class="notice notice-warning is-dismissible">
					<p><?php echo $verified['ok_msg2']; ?></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'yuvver'); ?></span></button>
				</div>
				
			<?php endif; ?>

			<?php if(isset($verified['need_key']) && $verified['need_key'] == false): ?>
			
				<div class="updated notice">
					<p><strong><?php _e('Your application is activated and ready to use.', 'yuvver'); ?></strong></p>
				</div>
				
			<?php else: ?>
								
				<?php if(!isset($verified['ok_msg2'])): ?>
					
					<div class="notice notice-warning is-dismissible">
					<p><?php _e("If you don't know your API key,<br />Login into your application in <a href='http://yuvver.com' target='_blank'>http://yuvver.com</a> and you will see it there", 'yuvver'); ?></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'yuvver'); ?></span></button>
					</div>
					
				<?php endif; ?>
				
				<?php $yuvver_options_api_key = ''; ?>
				
				<h3><?php _e('API key', 'yuvver'); ?></h3>
				
				<form action="options.php" method="post">
				
					<?php settings_fields('yuvver_settings_group'); ?>
					
					<p>
					
						<label style="display: inline-block;padding: 5px 10px;" class="description" for="yuvver_settings[yuvver_id]"><?php _e('API key', 'yuvver'); ?></label>
						<input style="width: 30%;padding:10px;" id="yuvver_settings[yuvver_id]" name="yuvver_settings[yuvver_id]" type="text" value="<?php echo $yuvver_options_api_key; ?>" />				
					
					</p>
					<p class="submit">
					
						<input type="submit" class="button-primary" value="<?php _e('Save', 'yuvver'); ?>" />
					
					</p>
					
				</form>			
				
			<?php endif; ?>
			
		<?php else: ?>
		
			<?php if(empty($yuvver_options['yuvver_id'])): ?>
			
				<p><?php _e("If you don't have an account in Yuvver,<br />Go to <a href='http://yuvver.com/sign-up/' target='_blank'>http://yuvver.com/sign-up/</a> to create your Yuvver account", 'yuvver'); ?></p>
				
			<?php endif; ?>
		
			<h3><?php _e('Your domain is not verified', 'yuvver'); ?></h3>
			
			<p><?php _e("Login into your application in <a href='http://yuvver.com' target='_blank'>http://yuvver.com</a> and copy below your code verification", 'yuvver'); ?></p>
			
			<?php if(isset($verified['error_msg'])): ?>
			
				<div class="error notice is-dismissible">
					<p><strong><?php echo $verified['error_msg']; ?></strong></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'yuvver'); ?></span></button>
				</div>
				
			<?php endif; ?>
			
			<form action="" method="post">
						
				<p>				
					<input name="do_verify" type="hidden" value="1" />				
					<label style="display: inline-block;padding: 5px 10px;" class="description" for="yuvver_settings[yuvver_id]"><?php _e('Code verification', 'yuvver'); ?></label>
					<input style="width: 50%;padding:10px;" name="code" type="text" value="" />				
				
				</p>
				<p class="submit">
				
					<input type="submit" class="button-primary" value="<?php _e('Verify Domain', 'yuvver'); ?>" />
				
				</p>
				
			</form>			
		
		<?php endif; ?>
		
		<?php $email_queue = yuvver_count_email_queue(); ?>
	
		<?php if($email_queue > 0): ?>
			
			<h3><?php _e('Email in queue', 'yuvver'); ?></h3>
			
			<p>
			
				<?php _e("Hi, we detect you have", 'yuvver'); ?>
				
				<?php echo ' '.$email_queue.' '; ?>
				
				<?php if($email_queue == 1): ?>
				
					<?php _e("email in queue", 'yuvver'); ?>
					
				<?php else: ?>

					<?php _e("emails in queue", 'yuvver'); ?>
				
				<?php endif; ?>
				
				<br />
				
				<?php /*var_dump(date('j/n/Y H:i:s')); ?>
				
				<br />
				
				<?php _e("Next schedule to send emails", 'yuvver'); ?>				
				<?php echo ' - '.(date('j/n/Y H:i:s', wp_next_scheduled('yuvver_send_mails'))).' '; ?>				
				<?php _e("seconds", 'yuvver');*/ ?>				
			
			</p>
			
			<form action="" method="post">
				
				<input type="hidden" name="do_send_emails" value="1" />
						
				<p class="submit">
				
					<input type="submit" class="button-primary" value="<?php _e('Send now', 'yuvver'); ?>" />
				
				</p>
				
			</form>	
		
		<?php endif; ?>
		
	</div>
	
	<?php 
	
	echo ob_get_clean();
	
}

function yuvver_count_email_queue() {
	
	global $wpdb;
	
	$table_name = $wpdb->prefix . "yuvver";
	
	$num = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
	
	return $num;
	
}

function yuvver_add_options_links() {
	
	add_submenu_page('edit.php?post_type=yuvver_invitations' , __('Settings', 'yuvver'), __('Settings', 'yuvver'), 'manage_options', 'yuvver-options', 'yuvver_options_page');
	
}
add_action('admin_menu', 'yuvver_add_options_links');

function yuvver_register_settings() {
	
	register_setting('yuvver_settings_group', 'yuvver_settings');
	
}
add_action('admin_init', 'yuvver_register_settings');

function yuvver_add_links_to_admin_bar() {

	$count = yuvver_count_email_queue();
	
	if($count > 0)
	{
		global $wp_admin_bar;
		
		$title = __('Yuvver - you have', 'yuvver');
		
		$title .= ' '.$count.' ';
		
		if($count == 1)
		{
			$title .= __('mail in queue', 'yuvver');			
		}
		else 
		{
			$title .= __('mails in queue', 'yuvver');
		}
		
		$wp_admin_bar->add_menu(array(
			'id' => 'yuvver_admin_bar',	
			'title' => $title,
			'href' => admin_url('edit.php?post_type=yuvver_invitations&page=yuvver-options')
		));		
	}
	
}

add_action('wp_before_admin_bar_render', 'yuvver_add_links_to_admin_bar');

function yuvver_add_dashboard_widget_html( $post, $callback_args ) {

	ob_start(); ?>
		
		<div class="wrap">
			
			<div class="inside">
			
				<div class="main">
				
					<p>
			
						<?php _e("Hi, we detect you have", 'yuvver'); ?>
						
						<?php echo ' '.$callback_args['args']['mails'].' '; ?>
						
						<?php if($callback_args['args']['mails'] == 1): ?>
						
							<?php _e("email in queue", 'yuvver'); ?>
							
						<?php else: ?>
		
							<?php _e("emails in queue", 'yuvver'); ?>
						
						<?php endif; ?>		
					
					</p>
					
					<form action="" method="post">
						
						<input type="hidden" name="do_send_emails" value="1" />
								
						<p class="submit">
						
							<input type="submit" class="button-primary" value="<?php _e('Send now', 'yuvver'); ?>" />
						
						</p>
						
					</form>	
				
				</div>
				
			</div>
			
		</div>
		
	<?php 
	
	echo ob_get_clean();

}

function yuvver_add_dashboard_widget() {

	$count = yuvver_count_email_queue();
	
	if($count > 0)
	{
		wp_add_dashboard_widget( 'yuvver_admin_dashboard', __('Yuvver', 'yuvver'), 'yuvver_add_dashboard_widget_html', '', array('mails' => $count));
	}
	
}

add_action('wp_dashboard_setup', 'yuvver_add_dashboard_widget');