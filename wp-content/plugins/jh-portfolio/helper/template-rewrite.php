<?php

function tj_add_page_rule( $regex, $files, $name, $logged_in = null, $redirect = false, $query_vars = array(), $query = array() ) {
	//check if it is a restricted page
	
	$base = parse_url( get_bloginfo( 'url' ) );

	$url = substr( $_SERVER['REQUEST_URI'], strlen( $base['path'] ) );
	
	if( !preg_match( '#' . $regex . '(\?[\s\S]*)?$' . '#', $url, $matches ) ) {
		return;
	}
	
	elseif( $logged_in === true && !is_user_logged_in() ) {
		wp_redirect( $redirect );
		exit;
	}
	elseif( $logged_in === false && is_user_logged_in() ) {
		wp_redirect( $redirect );
		exit;
	}
	$files = (array) $files;
	foreach( $files as $file ) : if( file_exists( $file ) ) {

		global $wp_query;

		if( $query ) { 

			foreach( $query as $q => $number ) {
				$query_gen[$q] = is_int($number) ? $matches[$number + 1] : $number;
			} 
			
			global $wpdb;
			$wp_query = new WP_Query($query_gen);
		}
				
		if( is_array($query_vars) ) {
			//set any query_vars
			foreach( $query_vars as $var => $count ) {
				if( is_int($count) )
					$wp_query->$var = $matches[$count + 1];
				else
					$wp_query->$var = $count;
			}
			$wp_query->is_home = '';
		}
		$wp_query->is_404 = '';
		
		include_once($file);
		exit;
	} endforeach;
	
}

?>