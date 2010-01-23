<?php
add_action( 'template_redirect', 'tja_template_redirect', 2 );
function tja_template_redirect() {
	global $wp_query;
	
	tj_add_page_rule( '^/login/', get_stylesheet_directory() . '/login.php', 'Login', false, get_bloginfo('url') );
	tj_add_page_rule( '^/login/lost-password/', get_stylesheet_directory() . '/login.lost-password.php', 'Lost Password', false, get_bloginfo('url') );
	tj_add_page_rule( '^/register/', get_stylesheet_directory() . '/register.php', 'Register', false, get_bloginfo('url') );
	tj_add_page_rule( '^/profile/', get_stylesheet_directory() . '/profile.php', 'My Profile', true, get_bloginfo('url') );
	tj_add_page_rule( '^/profile/promotions/', get_stylesheet_directory() . '/profile.promotions.php', 'My Promotions', true, get_bloginfo('url') );
	tj_add_page_rule( '^/profile/stuff(/page/[\d]*)?/?', get_stylesheet_directory() . '/profile.stuff.php', 'My Stuff', true, get_bloginfo('url') );
	tj_add_page_rule( '^/users/([^\/]*)(/page/[\d]*)?/?', get_stylesheet_directory() . '/author.php', 'Author', true, get_bloginfo('url'), array( 'user' ) );
}

add_action( 'template_redirect', 'tja_logout', 1 );
function tja_logout() {
	if ( $_GET['action'] == 'logout' ) :
		//lagout of facebook if the plugin is activated and the user is a facebook user
		if( function_exists('fbc_get_fbconnect_user') && fbc_get_fbconnect_user() ) {
			fbc_footer_register('FBConnect.logout();');
		}
		else {
			wp_logout();
			wp_redirect( remove_query_arg( 'action', $_SERVER['REQUEST_URI'] ) ); 
		}
	endif;
	
}
?>