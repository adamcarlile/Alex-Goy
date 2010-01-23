<?php
### Class: JH Portfolio Selector
 class WP_Widget_JH_Portfolio_Brief extends WP_Widget {
	// Constructor
	function WP_Widget_JH_Portfolio_Brief() {
		$widget_ops = array( 'description' => __( 'Shows the portfolio entry\'s brief', 'table_rss_news' ) );
		$this->WP_Widget( 'jh_portfolio_brief', __( 'JHP Entry Brief' ), $widget_ops );
	}
 
	// Display Widget
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
				
		echo $before_widget;
		global $jh_portfolio;
		
		?>
		<!-- Brief -->
		<?php if( $brief = jhp_get_brief() ) : ?>
			<div id="jh-portfolio-brief">
				<h4>The Brief</h4>
				<p><?php echo $brief ?></p>
			</div>
		<?php endif; ?>
		<?php
		echo $after_widget;
	
	}
}
 
 
### Function: Init Table News Widget
add_action('widgets_init', 'widget_jh_portfolio_brief');
function widget_jh_portfolio_brief() {
	register_widget( 'WP_Widget_JH_Portfolio_Brief' );
}
?>