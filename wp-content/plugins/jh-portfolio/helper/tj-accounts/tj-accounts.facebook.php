<?php
global $current_user;

/**
 * Returns the url of the facebook avatar
 * 
 * @param object $user
 * @return string
 */
function tja_get_facebook_avatar( $user, $width, $height ) {
	//cache the url for 4 hours in the usermeta
	if( !tja_is_facebook_user( $user ) )
		return false;
	
	if( isset($user->facebook_avatar) && (int) $user->facebook_avatar_last_refresh > strtotime( '-4 hours' ) )	{
		return  $user->facebook_avatar;
	}
	
	if( $width == $height && $width < 100 ) {
		$result = fbc_api_client()->users_getInfo( $user->fbuid, 'pic_square' );
		if( !$result )
			return '';
		update_usermeta( $user->ID, 'facebook_avatar_50_square', $result[0]['pic_square'] );
		update_usermeta( $user->ID, 'facebook_avatar_last_refresh_50_square', time() );
	} elseif ( $width != $height && $width < 100 ) {
		$result = fbc_api_client()->users_getInfo( $user->fbuid, 'pic_small' );
		update_usermeta( $user->ID, 'facebook_avatar_50', $result[0]['pic_small'] );
		update_usermeta( $user->ID, 'facebook_avatar_last_refresh_50', time() );
	} elseif( $width < 300 ) {
		$result = fbc_api_client()->users_getInfo( $user->fbuid, 'pic' );
		update_usermeta( $user->ID, 'facebook_avatar', $result[0]['pic'] );
		update_usermeta( $user->ID, 'facebook_avatar_last_refresh', time() );
	}
		
	return $result[0]['pic_square'];
}

function tja_get_facebook_about_me( $user ) {
	if( !tja_is_facebook_user( $user ) )
		return false;
	
	$result = fbc_api_client()->users_getInfo( $user->fbuid, 'about_me' );
	return $result[0]['about_me'];
	
}

add_action( 'fbc_insert_user', 'tja_facebook_user_added' );
function tja_facebook_user_added( $user_id ) {
	//add their about me to bio
	$user = tja_parse_user( $user_id );
	update_usermeta( $user->ID, 'user_bio', tja_get_facebook_about_me( $user ) );
}
?>