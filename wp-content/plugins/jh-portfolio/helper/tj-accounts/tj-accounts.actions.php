<?php

add_action( 'tja_login_submitted', 'tja_login_submitted' );
function tja_login_submitted() {
	//filter out anyone trying to brutefirce
	check_admin_referer( 'tja_login_submitted' );
	
	$return = tja_log_user_in( array( 'username' => $_POST['user_login'], 'password' => $_POST['user_pass'] ) );
	if( is_wp_error($return) ) {
		wp_redirect( get_bloginfo( 'login_url', 'display' ) . '?message=' . $return->get_error_code() );
		exit;
	}
	else {
		if( $_POST['redirect_to'] )
			$redirect = $_POST['redirect_to'];
		elseif( $_POST['referer'] ) //success
			$redirect = $_POST['referer'];			
		elseif( wp_get_referer() )
			$redirect = wp_get_referer();
		else
			$redirect = get_bloginfo('url');
			
		wp_redirect( $redirect );
		exit;
	}
	
}

add_action( 'tja_lost_password_submitted', 'tja_lost_password_submitted' );
function tja_lost_password_submitted() {
	//filter out anyone trying to brutefirce
	check_admin_referer( 'tja_lost_password_submitted' );
	
	$success = tja_lost_password( $_POST['user_email'] );

	if( $success['code'] !== 'success' ) {
		wp_redirect( get_bloginfo( 'lost_password_url', 'display' ) . '?message=' . $success['code'] );
		exit;
	} elseif( $success['code'] === 'success' ) {
		wp_redirect( get_bloginfo( 'lost_password_url', 'display' ) . '?message=' . $success['code'] );
		exit;
	}
}

add_action( 'tja_register_submitted', 'tja_register_submitted' );
function tja_register_submitted() {
	//filter out anyone trying to brutefirce
	check_admin_referer( 'tja_register_submitted' );
	
	$tj_return = tja_new_user( array(
		'user_login' 	=> $_POST['user_login'],
		'user_email'	=> $_POST['user_email'],
		'use_password' 	=> true,
		'user_pass'		=> $_POST['user_pass'],
		'user_pass2'	=> $_POST['user_pass_1'],
		'use_tos'		=> false,
		'unique_email'	=> true,
		'do_redirect'	=> false,
		'send_email'	=> true,
		'override_nonce'=> true
	));

	
	if( is_wp_error( $tj_return ) ) {
		wp_redirect( get_bloginfo( 'register_url', 'display' ) . '?message=' . $tj_return->get_error_code() );
		exit;
	}
	else {
		if( $_POST['redirect_to'] )
			$redirect = $_POST['redirect_to'];
		elseif( $_POST['referer'] )
			$redirect = $_POST['referer'];
		elseif( wp_get_referer() )
			$redirect = wp_get_referer();
		else
			$redirect = get_bloginfo('my_profile_url', 'display');
			
		wp_redirect( $redirect );
		exit;
	}
}

add_action( 'tja_profile_submitted', 'tja_profile_submitted' );
function tja_profile_submitted() {
	//filter out anyone trying to brutefirce
	check_admin_referer( 'tja_profile_submitted' );
	
	global $current_user;
	
	//check the user is logged in
	if( !$current_user )
		return;
	
	// loop through all data and only user user_* fields
	foreach( $_POST as $key => $value ) {
		if( strpos( $key, 'user_' ) !== 0 ) continue;
		$user_data[$key] = esc_html($value);
	}
	
	//password
	if( $user_data['user_pass'] && $user_data['user_pass2'] && ( $user_data['user_pass'] === $user_data['user_pass2'] ) )
		unset( $user_data['user_pass2'] );
	
	$user_data['ID'] = $current_user->ID;
	if( esc_html( $_POST['first_name'] ) )
		$user_data['first_name'] = esc_html( $_POST['first_name'] );
	if( esc_html( $_POST['last_name'] ) )
		$user_data['last_name'] = esc_html( $_POST['last_name'] );
	if( $current_user->user_login )
		$user_data['user_login'] = $current_user->user_login;
	
	if( $_POST['display_name'] ) {
		$name = trim($_POST['display_name']);
		$match = preg_match_all( '/([\S^\,]*)/', $_POST['display_name'], $matches );
		foreach( array_filter( (array) $matches[0] ) as $match ) {
			$name = trim(str_replace( $match, $user_data[$match], $name ));
		}
		$user_data['display_name'] = $name;
		$user_data['display_name_preference'] = esc_html( $_POST['display_name'] );
	}
	if( $_FILES['user_avatar']['name'] )
		$user_data['user_avatar'] = $_FILES['user_avatar'];
		
	$success = tja_update_user_info( $user_data );
	
	
	if( $_POST['redirect_to'] )
	    $redirect = $_POST['redirect_to'];
	elseif( $_POST['referer'] )
	    $redirect = $_POST['referer'];
	elseif( wp_get_referer() )
	    $redirect = wp_get_referer();
	else
	    $redirect = get_bloginfo('my_profile_url', 'display');

	wp_redirect( add_query_arg( 'message', $success['code'], $redirect ) );
	exit;
}


?>