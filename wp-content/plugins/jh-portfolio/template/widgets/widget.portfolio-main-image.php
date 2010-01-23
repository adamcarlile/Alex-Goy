<?php
### Class: JH Portfolio Selector
 class WP_Widget_JH_Portfolio_Main_Image extends WP_Widget {
	// Constructor
	function WP_Widget_JH_Portfolio_Main_Image() {
		$widget_ops = array( 'description' => __( 'Shows the main image', 'table_rss_news' ) );
		$this->WP_Widget( 'jh_portfolio_main_image', __( 'JHP Main Image' ), $widget_ops );
	}
 
	// Display Widget
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		extract( $instance );
		$height = (int) $height;
		echo $before_widget;
		?>
		<div id="jh-portfolio-main-image">
			<?php if( $image = jhp_get_main_image( null, $width, $height, ($height ? true : false ) ) ) : ?>
			    <img id="main-image" src="<?php echo $image ?>" />
			<?php endif; ?>
		</div>	
		<?php
		echo $after_widget;
	
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['width'] = (int) strip_tags( $new_instance['width'] );
		$instance['height'] = (int) strip_tags( $new_instance['height'] );
				
		return $instance;
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'width' => 200, 'height' => 150 ) );

		$width = esc_attr( $instance['width'] );
		$height = esc_attr( $instance['height'] );		
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>">
				<?php _e('Main Image Width:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('height'); ?>">
				<?php _e('Main Image Height:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" />
			</label>
		</p>
		
		
		
	<?php
	
	}

}
 
 
### Function: Init Table News Widget
add_action('widgets_init', 'widget_jh_portfolio_main_image');
function widget_jh_portfolio_main_image() {
	register_widget( 'WP_Widget_JH_Portfolio_Main_Image' );
}
?>