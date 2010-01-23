<?php
/**
 * register_custom_media_button function.
 *
 * Wrapper function for easily added new add media buttons
 *
 * @param int $id
 * @param string $button_text (optional)
 * @param bool $hide_other_options (optional) hide the default send to editor button and the other
 * @param bool $mutliple (optional) if the uploader and js lets you add more than one image
 */
function tj_register_custom_media_button( $id, $button_text = null, $hide_other_options = true, $multiple = false, $width = 50, $height = 50, $crop = true ) {

	if ( empty( $id ) || !is_string( $id ) )
		return false;
	
	$id = sanitize_title( $id );
	
	if ( is_null( $button_text ) )
		$button_text = 'Use as ' . ucwords( preg_replace( '#(-|_)#', ' ', $id ) );

	$buttons = get_option( 'custom_media_buttons' );

	$button = array( 'id' => $id, 'button_text' => $button_text, 'hide_other_options' => (bool) $hide_other_options, 'multiple' => ( $multiple ? 'yes' : '' ), 'width' => $width, 'height' => $height, 'crop' => $crop );

	$buttons[$id] = $button;

	update_option( 'custom_media_buttons', $buttons );
	
	//include the js if it hasnt already
	global $has_included_custom_media_button_js;
	if( !$has_included_custom_media_button_js ) {
		tj_add_custom_media_button_js();
	}

}

function tj_add_custom_media_button_js() {
	global $has_included_custom_media_button_js;
	$has_included_custom_media_button_js = true;
	echo '<script type="text/javascript">';
	include( HELPERPATH . 'scripts/media-uploader.extensions.js' );
	echo '</script>';

}

/**
 * add_extra_media_buttons function.
 *
 * Adds the "Use as Post Thumbnail" button to the add media thickbox.
 *
 * @param array $form_fields
 * @param object $media
 * @return array $form_fields
 */
function tj_add_extra_media_buttons( $form_fields, $media ) {

	$buttons = get_option( 'custom_media_buttons' );

	if ( $_GET['button'] ) :
		$button_id = $_GET['button'];

	else :
		preg_match( '/button=([A-z0-9_][^&]*)/', $_SERVER['HTTP_REFERER'], $matches );
		$button_id = $matches[1];

	endif;
	
	if ( !isset( $button_id ) || !$button_id )
		return $form_fields;
	
	if ( isset( $button_id ) && $button = $buttons[$button_id] )
		$buttons_html = '<input type="submit" class="button" name="' . $button['id'] . '[' . $media->ID . ']" value="' . esc_attr( $button['button_text'] ) . '" />';

	if ( !$button['hide_other_options'] ) :
		$send = '<input type="submit" class="button" name="send[' . $media->ID . ']" value="' . esc_attr( __( 'Insert into Post' ) ) . '" />';

	else : ?>
	
		<style type="text/css">
			.slidetoggle tr.post_title, .slidetoggle tr.image_alt, .slidetoggle tr.post_excerpt, .slidetoggle tr.post_content, .slidetoggle tr.url, .slidetoggle tr.align, .slidetoggle tr.image-size { display: none; }
		</style>
		
<?php endif;

	if ( $send )
		$send = "<input type='submit' class='button' name='send[$media->ID]' value='" . esc_attr__( 'Insert into Post' ) . "' />";
	
	if ( current_user_can('delete_post', $media->ID) ) {
		if ( !EMPTY_TRASH_DAYS )
			$delete = "<a href=\"" . wp_nonce_url("post.php?action=delete&amp;post=$media->ID", 'delete-post_' . $media->ID) . "\" id=\"del[$media->ID]\" class=\"delete\">" . __('Delete Permanently') . "</a>";
		else
			$delete = "<a href=\"" . wp_nonce_url("post.php?action=trash&amp;post=$media->ID", 'trash-post_' . $media->ID) . "\" id=\"del[$media->ID]\" class=\"delete\">" . __('Move to Trash') . "</a> <a href=\"" . wp_nonce_url("post.php?action=untrash&amp;post=$media->ID", 'untrash-post_' . $media->ID) . "\" id=\"undo[$media->ID]\" class=\"undo hidden\">" . __('Undo?') . "</a>";
	} else {
		$delete = '';
	}

	$thumbnail = '';
	if ( 'image' == $type && current_theme_supports( 'post-thumbnails' ) && get_post_image_id($_GET['post_id']) != $media->ID )
		$thumbnail = "<a class='wp-post-thumbnail' href='#' onclick='WPSetAsThumbnail(\"$media->ID\");return false;'>" . esc_html__( "Use as thumbnail" ) . "</a>";

	// Create the buttons array
	$form_fields['buttons'] = array( 'tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$send $thumbnail $buttons_html $delete</td></tr>\n" );

	return $form_fields;

}
add_filter( 'attachment_fields_to_edit', 'tj_add_extra_media_buttons', 99, 2 );


/**
 * catch_extra_media_buttons function.
 *
 * Catches when the "Use as Post Thumbnail" button is pressed, sets up the variables and calls the javascript
 *
 */
function tj_catch_extra_media_buttons() {

	// Check if a custom button was passed and if it was get its id by regex
	if ( strpos( $_POST['_wp_original_http_referer'], 'button=' ) ) :
	
		preg_match( '/button=([A-z0-9_][^&]*)/', $_POST['_wp_original_http_referer'], $matches );
		$button_id = $matches[1];
		
	elseif ( strpos( $_POST['_wp_http_referer'], 'button=' ) ) :
		
		preg_match( '/button=([A-z0-9_][^&]*)/', $_POST['_wp_http_referer'], $matches );
		$button_id = $matches[1];
		
	elseif ( $_REQUEST['button'] ) : 
		$button_id = $_REQUEST['button'];
	
	endif; 

	if ( isset( $button_id ) ) : 
		
		//is_single check
		$buttons = get_option( 'custom_media_buttons' );
		$button = $buttons[$button_id];
		
		$crop = $button['crop'] == true ? 1 : 0;
		$multiple = $button['multiple'];
		
		// If the custom button was pressed
		if ( is_array( $_POST[$button_id] ) ) :
			if( $multiple === 'yes' )
				echo '<div class="updated fade"><p>Image Added</p></div>';
				
			$attach_id = key( $_POST[$button_id] );
			$attach_thumb_url = wp_get_attachment_image_src( $attach_id, "width={$button['width']}&height={$button['height']}&crop=$crop" ); ?>
		
			<script type="text/javascript">
			    var win = window.dialogArguments || opener || parent || top;
			    win.save_custom_image( '<?php echo $button_id; ?>', <?php echo $attach_id; ?>, '<?php echo $attach_thumb_url[0]; ?>', '<?php echo $multiple ?>' );
			</script>
		
			<?php 
			//if is not multiple, close the box
			if( $multiple !== 'yes' )
				exit;
		endif;

	endif;

}
add_filter( 'admin_head', 'tj_catch_extra_media_buttons' );


/** add_button_to_upload_form function
 *	
 * Adds the button variable to the GET params of the media buttons thickbox link
 *
 */
function tj_add_button_to_upload_form() { ?>

	<script type="text/javascript">

		jQuery( document ).ready( function() {
			jQuery( '#image-form' ).attr( 'action', jQuery( '#image-form' ).attr( 'action' ) + '&button=<?php echo $_GET['button']; ?>');
			jQuery( '#filter' ).append( '<input type="hidden" name="button" value="<?php echo $_GET['button'] ?>" />' );
			jQuery( '#library-form' ).attr( 'action', jQuery( '#library-form' ).attr( 'action' ) + '&button=<?php echo $_GET['button']; ?>');
		} );

	</script>

<?php }
add_action( 'admin_head', 'tj_add_button_to_upload_form' ); 

function tj_add_image_html( $button_id, $post = null, $classes = null, $size = 'thumbnail' ) {

	if ( is_null( $post ) )
		global $post;
		
	if ( $post->term_id )
		$post->ID = $post->term_id;	
		
	$type = ( $post->term_id ) ? 'term' : 'post'; ?>
	
	<span id="<?php echo $button_id; ?>_container" class="<?php echo $classes; ?>">
	
	<?php if ( $image_id = get_metadata( $type, $post->ID, $button_id, true  ) ) : ?>
		
		<span class="image-wrapper" id="<?php echo $post->ID; ?>">
	    
			<?php echo wp_get_attachment_image( $image_id, $size ); ?>
	    
			<a class="delete_custom_image" rel="<?php echo $button_id; ?>:<?php echo $post->ID; ?>">Delete</a> |
	
		</span>
		
	<?php endif; ?>
	
	</span>

	<a class="add-image button thickbox" onclick="return false;" title="Add an Image" href="media-upload.php?post=<?php echo $post->ID; ?>&amp;button=<?php echo $button_id; ?>&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=197">
	    <img alt="Add an Image" src="<?php bloginfo( 'url' ); ?>/wp-admin/images/media-button-image.gif" /> Upload / Insert
	</a>

	<input type="hidden" name="<?php echo $button_id; ?>" id="<?php echo $button_id; ?>" value="<?php echo $image_id; ?>" />
			
<?php } ?>