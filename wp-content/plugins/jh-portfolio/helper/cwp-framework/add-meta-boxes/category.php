<?phpfunction cwp_categories_meta_box($post) {global $cwp;?><?php if( !$cwp->args['subpages']['add']['category'] ) : ?>	<div id="categorydiv">		<ul id="category-tabs">			<li class="tabs"><a href="#categories-all" tabindex="3"><?php _e( 'All Categories' ); ?></a></li>			<li class="hide-if-no-js"><a href="#categories-pop" tabindex="3"><?php _e( 'Most Used' ); ?></a></li>		</ul>				<div id="categories-pop" class="tabs-panel" style="display: none;">			<ul id="categorychecklist-pop" class="categorychecklist form-no-clear" >		<?php $popular_ids = wp_popular_terms_checklist('category'); ?>			</ul>		</div>	</div><?php endif; ?><div id="categorydiv">	<div id="categories-all" class="tabs-panel">		<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">		<?php 		$child_of = $cwp->args['subpages']['add']['category'] ? $cwp->args['subpages']['add']['category'] : false;		wp_category_checklist($post->ID, $child_of, false, $popular_ids) ?>		</ul>	</div>		<?php if ( current_user_can('manage_categories') ) : ?>	<div id="category-adder" class="wp-hidden-children">		<h4><a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3"><?php _e( '+ Add New Category' ); ?></a></h4>		<p id="category-add" class="wp-hidden-child">		<label class="screen-reader-text" for="newcat"><?php _e( 'Add New Category' ); ?></label><input type="text" name="newcat" id="newcat" class="form-required form-input-tip" value="<?php esc_attr_e( 'New category name' ); ?>" tabindex="3" aria-required="true"/>		<label class="screen-reader-text" for="newcat_parent"><?php _e('Parent category'); ?>:</label><?php wp_dropdown_categories( array( 'hide_empty' => 0, 'name' => 'newcat_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent category'), 'tab_index' => 3 ) ); ?>		<input type="button" id="category-add-sumbit" class="add:categorychecklist:category-add button" value="<?php esc_attr_e( 'Add' ); ?>" tabindex="3" />	<?php	wp_nonce_field( 'add-category', '_ajax_nonce', false ); ?>		<span id="category-ajax-response"></span></p>	</div></div><?phpendif;}function cwp_categories_meta_box_submitted( $post ) {	foreach ( (array) $_POST['post_category'] as $key => $id) {		$_POST['post_category'][$key] = (int) $id;	}	if( $_POST['post_category'] )		wp_set_object_terms( $post->ID, $_POST['post_category'], 'category' );	elseif(  )}?>