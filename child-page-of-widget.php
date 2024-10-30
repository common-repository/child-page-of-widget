<?php
/**
Plugin Name: Child Page Of Widget
Plugin URI: http://wordpress.org/plugins/child-page-of-widget‏/
Description: A widget that shows a specified number of child pages of a specified parent.
Author: Ruud Evers
Author URI: http://ruudevers.net
Version: 1.4
License: GPLv2
Created: March 13, 2014
*/

/*
 * Built using the example widget provided bu Justin Tadlock: http://justintadlock.com/archives/2009/05/26/the-complete-guide-to-creating-widgets-in-wordpress-28
 * 
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'cpo_load_widgets' );

// any languages files
function cpo_textdomain() {
	load_plugin_textdomain( 'cpo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'cpo_textdomain');  

/**
 * Register our widget.
 * 'CPO_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function cpo_load_widgets() {
	register_widget( 'CPO_Widget' );
}

/**
 * Limit title length when listing the child pages
 */
function trim_title() {
	$title = get_the_title();
	$limit = "30";
	$pad="...";

	if(strlen($title) <= $limit) {
		echo $title;
	} else {
		$title = substr($title, 0, $limit) . $pad;
		echo $title;
	}
}

/**
 * Child Page Of Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class CPO_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function CPO_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'cpo', 'description' => __('Display a specified number of child pages of a specified parent.', 'cpo') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'cpo-widget' );

		/* Create the widget. */
		parent::__construct( 'cpo-widget', __('Child Page Of Widget', 'cpo'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$page = $instance['page'];
		$number = $instance['no-of-pages'];
		
		if ( $number ) {
			$count = $number;
		}
		else {
			$count = 99;
		}
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( function_exists('icl_object_id') ) {
			$page_id = icl_object_id($page, 'page', true);
		} else {
			$page_id = $page;
		}		
		echo '<h3 class="widget-title">' . get_the_title($page_id) . '</h3>';

		/* If show page was selected, display the user's page. */
		if ( $page ) {
			echo '<ul>';
			$the_query = new WP_Query( array('post_type'=> 'page', 'post_parent'=> $page, 'posts_per_page' => $count, 'orderby' => 'menu_order', 'order' => 'ASC') );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					?><li><a href="<?php the_permalink(); ?>"><?php trim_title(); ?></a></li><?php
				}
			} else {
				echo __( 'No pages found.', 'cpo' );
			}
			echo '</ul>';
			if ( ($the_query->found_posts > $count) && (function_exists('icl_link_to_element')) ) {
				icl_link_to_element($page,'page',__('more...', 'cpo' ));
			}
			else if ($the_query->found_posts > $count) {
				echo '<a href="' . get_permalink($page) . '">' . __('more...', 'cpo' ) . '</a>';
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		}
			
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['no-of-pages'] = strip_tags( $new_instance['no-of-pages'] );

		/* No need to strip tags for page and show_page. */
		$instance['page'] = $new_instance['page'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- page: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e('Page:', 'cpo'); ?></label>
			<?php wp_dropdown_pages(array(
				'id' => $this->get_field_id('page'),
				'name' => $this->get_field_name('page'),
				'selected' => $instance['page'],
				'sort_order' => 'DESC',
			)); ?>
		</p>
		
		<!-- number of pages: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'no-of-pages' ); ?>"><?php _e('Maximum number of child pages in list:', 'cpo'); ?></label>
			<input id="<?php echo $this->get_field_id( 'no-of-pages' ); ?>" name="<?php echo $this->get_field_name( 'no-of-pages' ); ?>" value="<?php echo $instance['no-of-pages']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

?>