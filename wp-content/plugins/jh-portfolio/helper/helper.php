<?php
/*
Plugin Name: Edge Framwork
Plugin URI: http://edgedesigns.org/
Description: A set of helpful frameworks, functions and classes
Version: 0.4
Author: Tom Willmot, Joe Hoyle
Author URI: http://edgedesigns.org/
*/

if( !defined( 'HELPERPATH' ) ) : 

	define( 'HELPERPATH', dirname( __FILE__ ) . '/' );
	include_once( HELPERPATH . 'functions.php' );
	include_once( HELPERPATH . 'cwp-framework/cwp-framework.php' );
	include_once( HELPERPATH . 'media-uploader.extensions.php' );
	include_once( HELPERPATH . 'phpthumb.php' );
	if( TJ_ENABLE_ACCOUNTS !== false )
		include_once( HELPERPATH . 'tj-accounts/tj-accounts.php' );
	include_once( HELPERPATH . 'template-rewrite.php' );

endif;
?>