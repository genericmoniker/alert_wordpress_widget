<?php
/**
 * Plugin Name: Alert Snapshot Widget
 * Plugin URI: http://esmithy.net/
 * Description: A widget plugin to display a still snapshot from a Logitech Alert camera on your site.
 * Version: 0.1
 * Author: Eric Smith
 * Author URI: http://esmithy.net
 * License: GPL2
 * 
 * Copyright 2014  Eric Smith  (email : eric@esmithy.net)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace esmithy_net


/* Register the widget */
add_action('widgets_init', function() {
    register_widget('esmithy_net\Alert_Snapshot_Widget');
});


class Alert_Snapshot_Widget extends \WP_Widget {

    function __construct() {
        parent::__construct(
            'alert-snapshot-widget', 
            __('Alert Snapshot Widget', 'text_domain'),
            array('description' => __('A widget to show a Logitech Alert camera snapshot', 'text_domain'),)
        );
    }

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {
		$title = apply_filters('widget_title', $instance['title']);

		echo $args['before_widget'];
		if (!empty($title))
			echo $args['before_title'].$title.$args['after_title'];
			
		echo __('Hello, World!', 'text_domain');
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		if (isset($instance['title'])) {
			$title = $instance['title'];
		}
		else {
			$title = __('New title', 'text_domain');
		}
		// Switching from PHP to HTML markup, hence the end tag.
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<?php 
		// Back to PHP.
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

		return $instance;
	}
}

?>