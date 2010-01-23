<?php
/*
Plugin Name: TT Accounts
Description: Adds Custom Register, Custom Login and FaceBook Connect to WordPress
Version: 0.1
Author: Joe Hoyle
Author URI: http://www.joehoyle.co.uk
*/

include_once( 'tj-accounts.functions.php' );
include_once( 'tj-accounts.template-redirect.php' );
include_once( 'tj-accounts.hooks.php' );
include_once( 'tj-accounts.actions.php' );
include_once( 'tj-accounts.bloginfo.php' );

add_action( 'init', 'tja_init' );
function tja_init() {
	//inlucde facebook if the plugin is activated
	if( function_exists( 'fbc_get_fbconnect_user' ) ) {
		include_once( 'tj-accounts.facebook.php' );  
	}
}
?>