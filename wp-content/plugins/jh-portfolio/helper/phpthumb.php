<?php

/**
 * Resizes a given image (local).
 * 
 * @param mixed absolute path to the image
 * @param int $width.
 * @param int $height.
 * @param bool $crop. (default: false)
 * @return (string) url to the image
 */
function tj_phpthumb_it( $url, $width = 0, $height = 0, $crop = false, $resize = true, $watermark_options = array(), $cache = true ) {
	
	include_once( 'phpthumb/src/ThumbLib.inc.php' );
	
	//sort out the watermark args
	if( $watermark_options['mask'] ) {
		$wm_defaults = array( 'padding' => 0, 'position' => 'cc', 'pre_resize' => false );
		$watermark_options = wp_parse_args( $watermark_options, $wm_defaults );
	}
	
	$width = (int) $width;
	$height = (int) $height;
	
	
	$filename = end( explode( '/', $url ) );

	$ext = '.' . end( explode( '.', $filename ) );
	
	$filename = str_ireplace( $ext, '', $filename );
	$filename = str_ireplace( '_' . $width . '_' . $height, '', $filename );
	
	$new_name = $filename . '_' . $width . '_' . $height . ( $crop ? '_crop' : '') . ($resize ? '_resize' : '') . ($watermark_options['mask'] ? '_watermarked_' . $watermark_options['position'] : '') . '.png';
	
	$uploads = wp_upload_dir();
	
	// Attempt to create the cache directory
	if ( !is_dir( $uploads['basedir'] . '/cache/' ) )
		@mkdir ($uploads['basedir'] . '/cache/');
		
	$cache_dir = $uploads['basedir'] . '/cache/';
		
	// Only create the resized version if one hasn't already been created - or $cached is set to false.
	if ( !file_exists( $cache_dir . $new_name )  || $cache === false) :
		
		$url = str_replace( get_bloginfo('url'), ABSPATH, $url );
	
		try {
		     $thumb = PhpThumbFactory::create( $url );
		}
		catch (Exception $e) {
			error_log( $e );
			return str_replace( ABSPATH, trailingslashit( get_bloginfo('url') ), $url );
		}
		
		// Convert all images to png before resizing
		if ( $ext == '.gif' ) :
			
			// Save the converted image
			$thumb->save( $cache_dir . $filename . '.png', 'png' );
			
			// Pass the new file back through the function so they are resized
			return tj_phpthumb_it( $cache_dir . $filename . '.png', $width, $height, $crop, $resize ); 
		
		endif;
		
		// Remove the old version converted version if it exists
		if ( file_exists( $cache_dir . $filename . '.png' ) )
			unlink( $cache_dir . $filename . '.png' );
		
		// Resize and save the image
		
		//watermarking (pre resizing)
		if( $watermark_options['mask'] && $watermark_options['pre_resize'] === true ) {
			$thumb->resize( 99999, 99999 );
			$thumb->createWatermark($watermark_options['mask'], $watermark_options['position'], $watermark_options['padding']);
		}
		
		if( $crop === true && $resize === true )
			$thumb->adaptiveResize( $width, $height );
		elseif( $crop === true && $resize === false ) {
			$thumb->cropFromCenter($width, $height);
		}
		else
			$thumb->resize( $width, $height );
		
		//watermarking (post resizing)
		if( $watermark_options['mask'] && $watermark_options['pre_resize'] === false ) {
			$thumb->createWatermark($watermark_options['mask'], $watermark_options['position'], $watermark_options['padding']);
		}
		
		$thumb->save( $cache_dir . $new_name );
		
	endif;
	
	return str_replace( ABSPATH, get_bloginfo('url') . '/', $cache_dir . $new_name );
}

function phpthumb_post_image( $null, $id, $args ) {

	$args = wp_parse_args( $args );
	extract( $args );
	
	$crop = (bool) $crop;
		
	if ( !isset( $resize ) )
		$resize = true;

	if ( isset( $width ) ) {
		return array( tj_phpthumb_it( get_attached_file( $id ), $width, $height, $crop, $resize ), false, false, false );
	}
		
	return false;
	
}
add_filter( 'image_downsize', 'phpthumb_post_image', 99, 3 );


?>