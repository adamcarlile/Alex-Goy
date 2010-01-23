<?php
add_action( 'init', 'tja_check_for_pages' );
function tja_check_for_pages() {
	tja_check_for_submit('register');
	tja_check_for_submit('login');
	tja_check_for_submit('lost_password');
	tja_check_for_submit('profile');
}

add_action( 'tja_register_form', 'tja_add_register_inputs' );
function tja_add_register_inputs() {
	tja_add_form_fields( 'register' );
	echo '<input type="hidden" name="referer" value="' . ($_REQUEST['referer'] ? $_REQUEST['referer'] : wp_get_referer()) . '" />' . "\n";
}

add_action( 'tja_login_form', 'tja_add_login_inputs' );
function tja_add_login_inputs() {
	tja_add_form_fields( 'login' );
	echo '<input type="hidden" name="referer" value="' . ($_REQUEST['referer'] ? $_REQUEST['referer'] : wp_get_referer()) . '" />' . "\n"; 
}

add_action( 'tja_lost_password_form', 'tja_add_lost_password_inputs' );
function tja_add_lost_password_inputs() {
	tja_add_form_fields( 'lost_password' );
	echo '<input type="hidden" name="referer" value="' . ($_REQUEST['referer'] ? $_REQUEST['referer'] : wp_get_referer()) . '" />' . "\n"; 
}

add_action( 'tja_profile_form', 'tja_add_profile_inputs' );
function tja_add_profile_inputs() {
	tja_add_form_fields( 'profile' );
}

function tja_add_form_fields( $page ) {
	echo '<input type="hidden" name="tja_' . $page . '_submitted" value="' . $page . '" />' . "\n";
	wp_nonce_field( 'tja_' . $page . '_submitted' );
}

/**
 * Checks POST data for a given page name
 * 
 * @param string $page name
 */
function tja_check_for_submit( $page ) {
	if( !$_POST['tja_' . $page . '_submitted'] )
		return;
	do_action( 'tja_' . $page . '_submitted' );
}

//avatar
add_filter( 'get_avatar', 'tja_replace_avatar', 10, 5 );
function tja_replace_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	$user = tja_parse_user( $id_or_email );
	if( !$user ) return $avatar;
	$src = tja_get_avatar( $user, $size, $size, true, false );

	if( !$src ) return $avatar;
	return "<img alt='{$alt}' src='{$src}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
}
?>