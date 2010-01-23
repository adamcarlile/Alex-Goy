<?php

/**
 * tj_debug function.
 *
 * @access public
 * @param mixed $code
 * @param bool $output. (default: true)
 * @return void
 */
function tj( $code, $output = true ) {
	
	if ( $output ) : ?>
		
		<style>
			.tj_debug { word-wrap: break-word; white-space: pre; text-align: left; position: relative; background-color: rgba(0, 0, 0, 0.8); font-size: 11px; color: #a1a1a1; margin: 10px; padding: 10px; margin: 0 auto; width: 80%; overflow: auto; -moz-box-shadow:0 10px 40px rgba(0, 0, 0, 0.75); -webkit-box-shadow:0 10px 40px rgba(0, 0, 0, 0.75); -moz-border-radius: 5px; -webkit-border-radius: 5px; }
		</style>
		<br /><pre class="tj_debug">
	<?php endif;
	if ( is_null( $code ) || is_string($code) || is_int( $code ) || is_bool($code) || is_float( $code ) ) :
		if ( $output )
			var_dump( $code );
		else
			var_export( $code, true );
	else :
		if ( $output )
			print_r( $code );
		else
			print_r( $code, true ); 	
	endif;
	
	if ( $output )
		echo '</pre><br />';

}

/**
 * tj_alert function.
 *
 * @access public
 * @param mixed $code
 * @return void
 */
function tj_alert( $code ) {
	echo '<script type="text/javascript"> alert("';
	tj_debug( $code );
	echo '")</script>';
}

/**
 * tj_human_post_time function.
 *
 * @access public
 * @param string $timestamp. (default: 'current')
 * @return void
 */
function tj_human_post_time( $timestamp = 'current' ) {

	if ( empty( $timestamp ) ) return false;
	if ( $timestamp === 'current' ) $timestamp = time();

	if ( abs( time() - date( 'G', $timestamp ) ) < 86400 )
		return human_time_diff( date( 'G', $timestamp ) );

	else return date( 'Y/m/d g:i:s A', $timestamp );
}

/**
 * tj_parse_user function.
 *
 * @access public
 * @param mixed $user. (default: null)
 * @return void
 */
function tj_parse_user( $user = null ) {
	if ( is_object( $user ) && is_numeric( $user->ID ) ) return get_userdata( $user->ID );
	if ( is_object( $user ) && is_numeric( $user->user_id ) ) return get_userdata( $user->user_id );
	if ( is_array( $user ) && is_numeric( $user['ID'] ) ) return get_userdata( $user['ID'] );
	if ( is_numeric( $user ) ) return get_userdata( $user );
	if ( is_string( $user ) ) return get_userdatabylogin( $user );
	if ( is_null( $user ) ) :
		global $current_user;
		return get_userdata( $current_user->ID );
	endif;
}

/**
 * tj_parse_post function.
 *
 * @access public
 * @param mixed $post. (default: null)
 * @return void
 */
function tj_parse_post( $post = null ) {

	if ( is_object( $post ) || is_array( $post ) )
		return (object) $post;

	if ( is_numeric( $post ) )
		return get_post( $post );

	if ( is_null( $post ) ) :
		global $post;
		return $post;
	endif;

	if ( is_string( $post ) && get_page_by_title( $post ) )
		return get_page_by_title( $post );

	if ( is_string( $post ) && get_page_by_path( $post ) )
		return get_page_by_path( $post );

	if ( is_string( $post ) ) :
		global $wpdb;
		return get_post( $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '$post'" ) );
	endif;
}

/**
 * recursive_in_array function.
 *
 * @access public
 * @param mixed $needle
 * @param mixed $haystack
 * @return void
 */
function recursive_in_array($needle, $haystack) {
    foreach ($haystack as $stalk) {
        if ($needle == $stalk || (is_array($stalk) && recursive_in_array($needle, $stalk))) {
            return true;
        }
    }
    return false;
}

/**
 * tj_get_permalink function.
 *
 * @access public
 * @param mixed $post
 * @return void
 */
function tj_get_permalink( $post ) {

	$post = tj_parse_post( $post );

	return get_permalink( $post->ID );
}

/**
 * tj_shorten_string function.
 *
 * @access public
 * @param mixed $string
 * @param mixed $length
 * @return void
 */
function tj_shorten_string( $string, $length ) {
	
	if ( count_chars( $string ) > (int)$length )
		return substr( $string, 0, $length - 3 ) . '...';
	
	return $string;
}

/**
 * masort function.
 *
 * @access public
 * @param mixed &$data
 * @param mixed $sortby
 * @return void
 */
function masort(&$data, $sortby) {

   static $sort_funcs = array();

   if (empty($sort_funcs[$sortby])) {
       $code = "\$c=0;";
       foreach (split(',', $sortby) as $key) {
         if(is_numeric((int)$array[$key]))
           $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) ) return \$c;";
         else
           $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
       }
       $code .= 'return $c;';
       $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
   } else {
       $sort_func = $sort_funcs[$sortby];
   }
   $sort_func = $sort_funcs[$sortby];
   uasort($data, $sort_func);
}

/**
 * multisort function.
 *
 * @access public
 * @param mixed $array
 * @return void
 */
function multisort( $array ) {

   for($i = 1; $i < func_num_args(); $i += 3)
   {
       $key = func_get_arg($i);
       if (is_string($key)) $key = '"'.$key.'"';

       $order = true;
       if($i + 1 < func_num_args())
           $order = func_get_arg($i + 1);

       $type = 0;
       if($i + 2 < func_num_args())
           $type = func_get_arg($i + 2);

       switch($type)
       {
           case 1: // Case insensitive natural.
               $t = 'strcasecmp($a[' . $key . '], $b[' . $key . '])';
               break;
           case 2: // Numeric.
               $t = '($a[' . $key . '] == $b[' . $key . ']) ? 0:(($a[' . $key . '] < $b[' . $key . ']) ? -1 : 1)';
               break;
           case 3: // Case sensitive string.
               $t = 'strcmp($a[' . $key . '], $b[' . $key . '])';
               break;
           case 4: // Case insensitive string.
               $t = 'strcasecmp($a[' . $key . '], $b[' . $key . '])';
               break;
           default: // Case sensitive natural.
               $t = 'strnatcmp($a[' . $key . '], $b[' . $key . '])';
               break;
       }
       echo $t;
       usort($array, create_function('$a, $b', '; return ' . ($order ? '' : '-') . '(' . $t . ');'));
   }
   return $array;
}

/**
 * is_odd function.
 *
 * @param int
 * @return bool
 */
function is_odd( $int ) {
	return  $int & 1;
}

/**
 * in_array_multi function.
 *
 * @param mixed $needle array value
 * @param array $haystack
 * @return array - found results
 */
function in_array_multi( $needle, $haystack ) {
	foreach( (array) $haystack as $key_1 => $stack ) {
		foreach( $stack as $key_2 => $string ) {
			if( strpos( (string)$string, $needle ) !== false ) {
				$results[] = $key_1;
			}
		}
	}
	return $results;
}

/**
 * multi_array_key_exists function.
 *
 * @param mixed $needle The key you want to check for
 * @param mixed $haystack The array you want to search
 * @return bool
 */
function multi_array_key_exists( $needle, $haystack ) {

	foreach ( $haystack as $key => $value ) :

		if ( $needle == $key )
			return true;

		if ( is_array( $value ) ) :
		 	if ( multi_array_key_exists( $needle, $value ) == true )
				return true;
		 	else
		 		continue;
		endif;

	endforeach;

	return false;
}

function tj_count( $count, $none, $one, $more = null ) {

	if ( $count > 1 )
		echo str_replace( '%', $count, $more );

	elseif( $count == 1 )
		echo $one;

	else
		echo $none;

}

// For tw_the_messages
if ( !session_id() )
	add_action( 'init', 'session_start' );

function tj_error_message( $message, $context = '' ) {

	$_SESSION['messages'][$context][] = array( 'message' => $message, 'type' => 'error' );

}

function tj_success_message ( $message, $context = '' ) {
	$_SESSION['messages'][$context][] = array( 'message' => $message, 'type' => 'success' );
}

function tj_get_messages( $context = null ) {
	if ( empty( $_SESSION['messages'] ) ) :
		unset( $_SESSION['messages'] );
		return false;
	endif;
	

	if ( $context ) :

		$messages = $_SESSION['messages'][$context];

		unset( $_SESSION['messages'][$context] );

		if ( empty( $_SESSION['messages'] ) )
			unset( $_SESSION['messages'] );

		return $messages;

	else :

		foreach( (array) $_SESSION['messages'] as $context ) :
			$messages = (array) $messages + (array) $context;
		endforeach;

		unset( $_SESSION['messages'] );

		if ( $messages )
			return $messages;

	endif;

	return false;
}

function tj_get_the_messages( $context = null, $classes = null ) {
	$messages = tj_get_messages( $context );

	if ( $messages )
		foreach( (array) $messages as $message )
			$output = '<div id="message" class="message ' . $message['type'] . ' ' . $classes . ' updated"><p>' .$message['message'] . '</p></div>';

	return $output;
}

function tj_the_messages( $context = null, $classes = null ) {

	echo tj_get_the_messages( $context, $classes );

}

function tj_unsanitize_title( $title ) {
	return ucwords( str_replace( '_', ' ', $title ) );
}

function get_post_meta_by( $field = 'post_id', $value ) {

	return get_metadata_by( $field, $value, 'post' );

}

function get_term_meta_by( $field = 'term_id', $value ) {

	return get_metadata_by( $field, $value, 'term' );

}

function get_metadata_by( $field = 'post', $value, $type ) {

	global $wpdb;

	if ( $field === 'object_id' || $field === $type . '_id' ) {
		$get_type_custom = 'get_' . $type . '_custom';
		return $get_type_custom( $value );

	}

	$table = $wpdb->prefix . $type . 'meta';

	if ( $field === 'key' )
		return $wpdb->get_results( "SELECT DISTINCT meta_value FROM $table WHERE $table.meta_key = '$value'" );

	if ( $field === 'value' )
		return $wpdb->get_results( "SELECT DISTINCT meta_value FROM $table WHERE $table.meta_value = '$value'" );
}

/**
 * Get array of a terms children (across taxonomy)
 * 
 * @param object $parent term object
 * @return array
 */
function tj_get_term_children( $parent ) {

	if ( !is_numeric( $parent ) )
		return false;

	global $wpdb;
	$terms = $wpdb->get_results( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.parent = $parent ORDER BY t.name ASC" );

	return $terms;
}


// term meta functions
//

function add_term_meta_table() {
	global $wpdb;
	
	if ( !current_theme_supports( 'term-meta' ) )
		return false;

	$wpdb->tables[] = 'termmeta';
	$wpdb->termmeta = $wpdb->prefix . 'termmeta';

}

/**
 * Creates the termmeta table if it deos not exist
 *
 */
function create_term_meta_table() {
	global $wpdb;
	// check if the table is already exists

	if ( get_option( $wpdb->prefix . 'termmeta_table_exists' ) )
		return;

	$wpdb->query( "
		CREATE TABLE $wpdb->prefix . 'termmeta' (
		'meta_id' bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		'term_id' bigint(20) unsigned NOT NULL DEFAULT '0',
		'meta_key' varchar(255) DEFAULT NULL,
		'meta_value' longtext,
		PRIMARY KEY ('meta_id'),
		KEY 'term_id' ('term_id'),
		KEY 'meta_key' ('meta_key')
		ENGINE=InnoDB AUTO_INCREMENT=4742 DEFAULT CHARSET=utf8" );

	get_option( $wpdb->prefix . 'termmeta_table_exists', 'yes' );

	return true;
}


add_action( 'init', 'add_term_meta_table' );

/**
 * Add meta data field to a term.
 *
 * @param int $term_id term ID.
 * @param string $key Metadata name.
 * @param mixed $value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function add_term_meta($term_id, $meta_key, $meta_value, $unique = false) {
    return add_metadata('term', $term_id, $meta_key, $meta_value, $unique);
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $term_id term ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 * @return bool False for failure. True for success.
 */
function delete_term_meta($term_id, $meta_key, $meta_value = '') {
    return delete_metadata('term', $term_id, $meta_key, $meta_value);
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int $term_id term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function get_term_meta($term_id, $key, $single = false) {
    return get_metadata('term', $term_id, $key, $single);
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate betjeen meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param int $term_id term ID.
 * @param string $key Metadata key.
 * @param mixed $value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function update_term_meta($term_id, $meta_key, $meta_value, $prev_value = '') {
    return update_metadata('term', $term_id, $meta_key, $meta_value, $prev_value);
}

/**
 * Retrieve term meta fields, based on post ID.
 *
 * The term meta fields are retrieved from the cache, so the function is
 * optimized to be called more than once. It also applies to the functions, that
 * use this function.
 *
 * @param int $term_id term ID
 * @return array
 */
 function get_term_custom($term_id = 0) {

    $term_id = (int) $term_id;

    if ( ! wp_cache_get($term_id, 'term_meta') )
        update_termmeta_cache($term_id);

    return wp_cache_get($term_id, 'term_meta');
}

/**
* Updates metadata cache for list of term_ids.
*
* Performs SQL query to retrieve the metadata for the term_idss and updates the
* metadata cache for the terms. Therefore, the functions, which call this
* function, do not need to perform SQL queries on their own.
*
* @param array $term_ids List of term_idss.
* @return bool|array Returns false if there is nothing to update or an array of metadata.
*/
function update_termmeta_cache($term_ids) {
    return update_meta_cache('term', $term_ids);
}

?>