<?php
function yuvver_post_save( $post_id )
{
	$is_autosave = wp_is_post_autosave($post_id);
	$is_revision = wp_is_post_revision($post_id);
	$is_valid_nonce = ( isset( $_POST['meta_box_nonce'] ) && wp_verify_nonce( $_POST['meta_box_nonce'], 'yuvver_meta_box_nonce' ) );
	
	if($is_autosave || $is_revision || !$is_valid_nonce) {
		
		return;
	
	}

	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// Make sure your data is set before trying to save it
	if( isset( $_POST['yuvver_invitation'] ) )
	{
		update_post_meta( $post_id, 'yuvver_invitation', $_POST['yuvver_invitation']);
	}
}

add_action( 'save_post', 'yuvver_post_save' );

function yuvver_create_post_type() {

	global $yuvver_base_plugin_url;
	
	register_post_type( 'yuvver_invitations',
		array(
			'labels' => array(
				'name' => __( 'Yuvver', 'yuvver'),														
				'all_items' => __( 'Show all invitations', 'yuvver'),								
				'add_new' => __( 'Add new invitation', 'yuvver'),								
				'add_new_item' => __( 'Email subject', 'yuvver'),								
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'yuvver_invitations', 'with_front' => true),
		 	'show_ui' => true, 	
		 	'show_ui_nav_menu' => false, 	
		 	'menu_icon' => $yuvver_base_plugin_url.'img/menu_icon.png',				
		 	//'menu_icon' => 'dashicons-share',					
			'supports' => array('title')
		)

	);
	flush_rewrite_rules();

}
add_action( 'init', 'yuvver_create_post_type' );

function is_edit_page($new_edit = null) {
	
	global $pagenow;
	
	//make sure we are on the backend
	if (!is_admin()) 
	{ 
		return false;
	}

	if($new_edit == "edit")
	{
		return in_array( $pagenow, array( 'post.php'  ) );
	}
	elseif($new_edit == "new") //check for new post page
	{
		return in_array( $pagenow, array( 'post-new.php' ) );
	}
	else //check for either new or edit
	{
		return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
	}
	
}
function yuvver_add_meta_boxes() {
	
	add_meta_box( 'yuvver_meta', __('Invitation', 'yuvver'), 'yuvver_meta_callback', 'yuvver_invitations', 'normal', 'high' );
	
}
function yuvver_meta_callback($post) {
	
	wp_nonce_field( 'yuvver_meta_box_nonce', 'meta_box_nonce' );
	
	$values = get_post_custom( $post->ID );

	?>
	
	<div class="wrap">
		
		<?php if(is_edit_page('edit')) { ?>
		
			<h2><?php _e( 'Shortcode button', 'yuvver')?></h2>
			
			<p>
	
				<input dir="ltr" style="width: 100%;padding:10px;" type="text" readonly="readonly" value="[yuvver id='<?php echo $post->ID; ?>' class='yuvver_button_style']Change text here[/yuvver]" />	
				
				<label style="display: block;padding: 5px 10px;"><?php _e( 'Copy this shortcode into your post/page editor', 'yuvver')?></label>
				
				<br />
				<br />
				
			</p>
		
		<?php } ?>
		
		
		<h2><?php _e('Write your invitation', 'yuvver'); ?></h2>
		<p>
			<lable style="display: block;padding: 5px 10px;">
			
				<?php _e( 'Use the string', 'yuvver')?> <strong dir="ltr">{{client_name}}</strong> <?php _e( 'in the editor or in the subject to print your client name', 'yuvver')?>
			
			</lable>

			<lable style="display: block;padding: 5px 10px;">
			
				<?php _e( 'Use the string', 'yuvver')?> <strong dir="ltr">{{friend_name}}</strong> <?php _e( "in the editor to print your client's friend name", 'yuvver')?>
			
			</lable>
		</p>
		
		<p>
		
			<?php 
				
				$content = (isset($values['yuvver_invitation']) ? ($values['yuvver_invitation'][0]) : '');
				
				if($content == '')
				{
					$content = __("<h2 dir='ltr'>***Example***</h2><p dir='ltr'>Hi {{friend_name}},<br />Your friend {{client_name}} wanted to share with you this link to the best website ever.<br /></p><p dir='ltr'>{{base_url}}</p>", 'yuvver');
					
					$content = str_replace('{{base_url}}', '<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>', $content);
					/*
					$content = '<h2 dir="ltr">***Example***</h2>';
					$content .= '<p dir="ltr">';
						$content .= 'Hi {{friend_name}},<br />';
						$content .= 'Your friend {{client_name}} share with you link to the best website ever.<br />';
					$content .= '</p>';				
					$content .= '<p dir="ltr">';
						$content .= '<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>';
					$content .= '</p>';		
					*/		
				}
								
				wp_editor( $content, 'yuvver_invitation', array()); 
			
			?>
			
		</p>
	
	</div>
	
	<?php 
}
add_action( 'add_meta_boxes', 'yuvver_add_meta_boxes' );
