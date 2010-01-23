<?php

/**
 * TT New User
 * Creates a new user with args passed through an array or string of arguments. Passing arguments works the same
 * as functions such as query_posts(). Params are show as variable names which you must use when passing args
 * NOTE: wp_nonce_field( 'register' ) must be used on the register form
 *
 * @Param: username [string] - The desired username for the new user
 * @Param: email [string] - The desired email address for the new user
 * @Param: use_password [bool] [default: false] - Whether to specify a password on registration
 * @Param: password [string] - If use_password is true, the desired password for the new user
 * @Param: use_tos [bool] [default: true] - Whether the user needs to accept Terms of Service
 * @Param: tos [string] - If use_tos is true, the value to the accept Terms of Service checkbox
 * @Param: unique_email [bool] [default: false] - Set to true if only one username is allowed per email address
 * @Param: do_redirect [bool] [default: true] Whether to redirect the user after registration is complete
 * @Param: redirect [string] [default: User Profile Page] - The url to redirect the user to after successful login
 * @Param: send_email [bool] [default: true] Whether to send an email containing the username and password of the newly registered user
 * @Param: profile_info [array] [dafault: false] An array containing values to be used in wp_update_user() such as first_name, last_name
 * @Param: validate [bool] [default: true]
 * @param: require_verify_email [bool] [default: false] Sends the user an email with a Activate Account link to activate their account
 * @param: override_nonce [bool] [default: false] Bypasses the nonce check, not recommended in most situations
 * @return: The ID of the newly registered user [on error returns error string]
 * @author: Joe Hoyle
 * @version 1.0
 **/
function tja_new_user( $args ) {
	//Check the nonce field
	if( $args['override_nonce'] !== true )
		check_admin_referer('register');

	if(  is_user_logged_in() ) {
		tj_error_message( 'You are already logged in', 'register' );
		return new WP_Error( 'already-logged-in');
	}

	include_once( ABSPATH . '/wp-includes/registration.php' );

	$checks = array(
		'use_password' => false,
		'tos' => '',
	    'use_tos' => true,
	    'unique_email' => false,
	    'do_redirect' => true,
	    'redirect' => '',
	    'send_email' => false,
	);

	$defaults = array(
		'user_login' => '',
		'user_email' => '',
		'user_pass' => false,
		'role' => 'subscriber',
		'validate' => true,
	);

	$original_args = $args;

	$default_args = array_merge( $defaults, $checks );

	//Strip any tags then may have been put into the array
	strip_tags( (string) $args );

	$args = wp_parse_args( $args, $default_args );
	extract( $args, EXTR_SKIP );

	$validation = apply_filters( 'tja_registration_info', $args );

	unset( $args['user_pass2'] );
	unset( $user_pass2 );
	if ( $validation['status'] === 'error' && $validate == true ) {
		return $validation;
	}


	// Merge arrays overwritting defaults, remove any non-standard keys keys with empty values.
	$user_vars = array_filter( array_intersect_key( array_merge( $defaults, $args ), $defaults ) );

	//Check for require_verify_email, send email and store temp data
	if( $require_verify_email ) {
		$original_args['require_verify_email'] = false;
		$unverified_users = (array) get_option('unverified_users');

		$unverified_users[time()] = $original_args;

		update_option( 'unverified_users', $unverified_users );

		$message = "Please click the link below to activate your account for " . get_bloginfo() . "\n \n";
		$message .= '<a href="' . get_bloginfo('url') . '/login/?verify_email=' . $user_vars['user_email'] . '&key=' . time() . '">' . get_bloginfo('url') . '/login/?verify_email=' . $user_vars['user_email'] . '&key=' . time() . '</a>';

		$headers = 'From: ' . get_bloginfo() . ' <noreply@' . get_bloginfo( 'url' ) . '>' . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \r\n\r\n";

		wp_mail( $user_vars['user_email'], 'Please activate your account for ' . get_bloginfo(), $message, $headers );

		return tj_return_success( 'sent-email-activation', '<p class="message success">You have been sent an activation email, please follow the link in the email sent to ' . $user_vars['user_email'] . '</p>' );

	}

	$user_id = wp_insert_user( $user_vars );

	if ( $role ) :
		$user = new WP_User( $user_id );
		$user->set_role( $role );
	endif;

	// Get any remaining variable that were passed
	$meta_vars = array_diff_key( $original_args, $defaults, $checks );
	foreach ( (array) $meta_vars as $key => $value ) :
		update_usermeta( $user_id, $key, $value );
	endforeach;

	$user = get_userdata( $user_id );

	//Send Notifcation email if specified
	if ( $send_email == true )
		$email = tja_email_registration_success( $user, $user_pass );

	//If they chose a password, login them in
	if ( $use_password == 'true' && $user->ID > 0 ) :
		wp_login($user->user_login, $user_pass);
		wp_clearcookie();
		wp_setcookie($user->user_login, $user_pass, false);
	endif;

	//Redirect the user if is set
	if ( $redirect !== '' && $user->ID && $do_redirect == true ) wp_redirect( $redirect );

	return $user_id;

}

/**
 * tja_validate_registration function.
 *
 * @access public
 * @param mixed $args
 * @return void
 */
function tja_validate_registration( $args ) {
	//Username
	if( ($user = get_user_by('login', $args['user_login'])) && $user->ID )
		return array( 'status' => 'error', 'text' => 'username-exists', 'message' => 'Sorry, the username: ' . $args['user_login'] . ' already exists.' );
	//Password
	if( $args['user_pass'] != $args['user_pass2'] )
		return array( 'status' => 'error', 'text' => 'password-mismatch', 'message' => 'The passwords you entered do not match.' );
	//Email
	if( !is_email( $args['user_email'] ) ) {
		tj_error_message( 'The email address you entered is not valid', 'register' );
		return new WP_Error( 'invalid-email' );
	}
	if( $args['unique_email'] == true && get_user_by_email( $args['user_email'] ) && $args['user_email'] ) {
		tj_error_message( 'The email address you entered is already in use', 'register' );
		return new WP_Error( 'email-in-use' );
	}
}
add_filter( 'tja_registration_info', 'tja_validate_registration' );

function tja_email_registration_success( $user, $user_pass ) {

	if( file_exists( $file = get_stylesheet_directory() . '/email.register.php' ) ) {
		ob_start();
		include( $file );
		$message = ob_get_contents();
		ob_end_clean();
	} elseif( file_exists( $file = 'tt-accounts.email.register.php' ) ) {
		ob_start();
		include( $file );
		$message = ob_get_contents();
		ob_end_clean();
	} else {
		return false;
	}
	add_filter( 'wp_mail_content_type', 'wp_mail_content_type_html' );
	return wp_mail( $user->user_email, apply_filters( 'tja_register_email_subject', 'New account registered for ' . get_bloginfo() ), $message, 'content-type=text/html' );

}

// This forces the inbuilt mail function to send html emails instead of plain text emails.
function wp_mail_content_type_html( $content_type ) {
	return 'text/html';
}


/**
 * Logs a user in
 *
 * @Param: username (string)
 * @Param: password (string)
 * @Param: password_hashed (bool) [default: false]
 * @Param: redirect_to (string) [optional]
 * @Param: remember (bool) [default: false]
 * @Param: allow_email_login (bool) [default: true]
 *
 * @Return: error array (message => string, number => (int) true on success
 * 			101: already logged in
 			102: no username
 			103: unrocognized username
 			104: incorrect password
 			105: success
**/
function tja_log_user_in( $args ) {

	if ( is_user_logged_in() ) :
		tj_error_message( 'You are already logged in', 'login' );
		return new WP_Error( 'already-logged-in' );

	endif;

	if ( empty( $args['username'] ) ) :
		return new WP_Error( 'no-username' );
	endif;

	$user = tja_parse_user( $args['username'] );

	$defaults = array(
		'remember' => false,
		'allow_email_login' => true
	);

	// Strip any tags then may have been put into the array
	foreach( $args as $i => $a ) {
		if( is_string( $a ) )
			$args[ $i ] = strip_tags( $a );
	}


	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	if ( !is_numeric( $user->ID ) ) :
		tj_error_message( 'The username you entered was not recognized', 'login' );
		return new WP_Error( 'unrecognized-username');
	endif;

	if ( $password_hashed != true ) :
		if ( !wp_check_password( $password, $user->user_pass ) ) :
			tj_error_message( 'The password you entered is incorrect', 'login' );
			return new WP_Error('incorrect-password');
		endif;
	else :
		if ( $password != $user->user_pass ) :
			tj_error_message( 'The password you entered is incorrect', 'login' );
			return new WP_Error('incorrect-password');
		endif;
	endif;

	wp_set_auth_cookie( $user->ID, $remember );

	do_action( 'tja_log_user_in', $user);

	if ( $redirect_to == 'referer' )
		$redirect_to = wp_get_referer();

	if ( $redirect_to )
		wp_redirect( apply_filters( 'tja_login_redirect', $redirect_to, $user ) );
	return true;
}

function tja_lost_password( $email ) {
	if( !get_user_by_email( $email ) ) {
		return new WP_Error('unrognized-email');
	}

	$_POST['user_email'] = $email;
	$_POST['user_login'] = $email;

	//grab the retrieve password function from wp-login.php
	ob_start();
	include_once( trailingslashit(ABSPATH) . 'wp-login.php' );
	ob_end_clean();

	add_filter( 'retrieve_password_message', 'tja_lost_password_email', 10, 2 );
	add_filter( 'wp_mail_content_type', 'wp_mail_content_type_html' );
	$errors = retrieve_password();

	if( !is_wp_error( $errors ) ) {
		return array( 'status' => 'success', 'text' => 'success' );
	}
	tj_error_message( 'There was an unknown error', 'lost-password' );

	return new WP_Error('unknown');
}

function tja_lost_password_email( $message, $key ) {

	$user = get_user_by_email(trim($_POST['user_login']));
	$reset_url = get_bloginfo( 'lost_password_url', 'display' ) . '?action=rp&key=' . $key . '&login=' . $user->user_login;


	if( file_exists( $file = get_stylesheet_directory() . '/email.lost-password.php' ) ) {
		ob_start();
		include( $file );
		$message = ob_get_contents();
		ob_end_clean();
	}

	return $message;

}

/**
 * Updates a users Information
 *
 * Can take a variety of arguments all in the form of a userInfo array.
 *
 * For starters you can pass any of the default wordpress user fields, you can also pass
 * an avatar to upload or an image url to use as an avatar.
 * You can also pass any amount of additonal fields which will be added to the
 * 'profile_info' usermeta.
 * Note this function does not do any stripping or sanitizing, all that should be done before the data gets here.
 *
 * @PARAM: (array) of user information
 * @RETURN: (string) success/error message
 * @AUTHOR: Tom Willmot
 * @VERSION: 1.0
 **/
function tja_update_user_info( $info ) {

	// If an email was passed, check that it is valid
	if ( !preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $info['user_email'] ) && is_string( $info['user_email'] ) && strpos( $info['user_email'], 'apps+' ) !== 0 ) return '<p class="message error">Please enter a valid email address</p>';

	// If an ID wasn't passed then use the current user
	if ( !$info['ID'] ) :
		global $current_user;
		$info['ID'] = $current_user->ID;
	endif;

	if ( !$info['ID'] ) return false;

	// prepare the array for wp_update_user
	$userdata['ID'] = $info['ID'];
	if ( $info['user_email'] ) $userdata['user_email'] = $info['user_email'];
	if ( $info['display_name'] )$userdata['display_name'] = $info['display_name'];
	if ( $info['first_name'] )$userdata['first_name'] = $info['first_name'];
	if ( $info['last_name'] )$userdata['last_name'] = $info['last_name'];
	if ( $info['description'] )$userdata['description'] = $info['description'];
	if ( $info['user_pass'] ) $userdata['user_pass'] = $info['user_pass'];

	require_once( ABSPATH . 'wp-includes/registration.php' );
	$user_id = wp_update_user( $userdata );


	// User avatar
	if( $info['user_avatar'] ) {
		require_once(ABSPATH . 'wp-admin/includes/admin.php');

		$file = wp_handle_upload( $info['user_avatar'], array( 'test_form' => false ) );
		$info['user_avatar_path'] = $file['file'];
		unset( $info['user_avatar'] );
	}

	// Remove everything we have already used
	foreach ($info as $key => $inf) { if(is_string($inf) && $inf == '') $info[$key] = ' '; }
	$meta_info = array_diff( $info, $userdata );



	// Anything left gets added to usermeta as a seperate user-meta field
	if ( !empty( $meta_info ) ) :

		foreach( (array) $meta_info as $key => $value ) :
			update_usermeta( $info['ID'], $key, $value );
		endforeach;

	endif;
	return $user_id;
}

/**
 * tja_parse_user function.
 *
 * @access public
 * @param mixed $user. (default: null)
 * @return void
 */
function tja_parse_user( $user = null ) {
	if ( is_object( $user ) && is_numeric( $user->ID ) ) return get_userdata( $user->ID );
	if ( is_object( $user ) && is_numeric( $user->user_id ) ) return get_userdata( $user->user_id );
	if ( is_array( $user ) && is_numeric( $user['ID'] ) ) return get_userdata( $user['ID'] );
	if ( is_numeric( $user ) ) return get_userdata( $user );
	if ( is_string( $user ) ) {
		if( strpos( $user, "@" ) > 0 && $user = get_user_by_email( $user ) )
			return $user;
		return get_userdatabylogin( $user );
	}
	if ( is_null( $user ) ) :
		global $current_user;
		return get_userdata( $current_user->ID );
	endif;
}

function tja_login_message() {
	if( !$_GET['message'] )
		return;

	echo '<p class="message error">' . tja_get_message( (int) $_GET['message'] ) . '</p>' . "\n";
}

function tja_register_message() {
	if( !$_GET['message'] )
		return;

	echo '<p class="message error">' . tja_get_message( (int) $_GET['message'] ) . '</p>' . "\n";
}

function tja_get_message( $code = null ) {
	if( $code === null ) $code = (int) $_GET['message'];
	$codes = tja_message_codes();
	return $codes[$code];
}

function tja_get_the_message() {
	if( !$_GET['message'] )
		return;

	echo '<p class="message error">' . tja_get_message( (int) $_GET['message'] ) . '</p>' . "\n";
}

function tja_message_codes() {
	$codes = array();
	$codes[101] = 'You are already logged in.';
	$codes[102] = 'Please enter a username.';
	$codes[103] = 'The username you entered has not been recognised.';
	$codes[104] = 'The password you entered is incorrect.';
	$codes[105] = 'Successfully logged in';

	$codes[200] = 'Successfully registered';
	$codes[201] = 'You are already logged in.';
	$codes[202] = 'Sorry, that username already exists.';
	$codes[203] = 'The passwords you entered do not match.';
	$codes[204] = 'The email address you entered is not valid';
	$codes[205] = 'The email address you entered is already in use.';
	$codes[206] = 'You have been sent an activation email, please follow the link in the email.';

	$codes[300] = 'You have been emailed a link to reset yoru password, please check your email.';
	$codes[301] = 'The email address you entered was not recognized';
	$codes[302] = 'There was a problem, please contact the site administrator';

	$codes[400] = 'Successfully updated your profile.';

	return apply_filters( 'tja_message_codes', $codes );
}


//url functions
function tja_get_user_url( $authordata = null ) {
	if( !$authordata ) global $authordata;
	$authordata = tja_parse_user( $authordata );
	return get_bloginfo('url') . '/users/' . $authordata->user_login . '/';
}


//get user functions
function tja_get_avatar( $user, $width, $height, $crop = true, $try_normal = true ) {
	if( tja_is_facebook_user( $user ) ) {
		return tja_get_facebook_avatar( $user, $width, $height );
	}
	elseif( $avatar = tja_get_avatar_upload( $user, $width, $height, $crop ) ) {
		return $avatar;
	}
	elseif( $try_normal === true ) {
		preg_match( '/src=\'([^\']*)/', get_avatar( $user->user_email, $width ), $matches );
		return $matches[1];
	}
}

function tja_get_avatar_upload( $user, $w, $h, $c ) {
	if( $user->user_avatar_path )
		return tj_phpthumb_it( $user->user_avatar_path, $w, $h, $c );
}

/**
 * Checks if a given user is a facebook user
 *
 * @param object $user
 * @return bool
 */
function tja_is_facebook_user( $user ) {
	return (bool) $user->fbuid;
}

?>
