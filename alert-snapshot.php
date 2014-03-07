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
		
		// "camera2.svc/" + camera.mac + "/snapshotviewable"
			
		echo __('Hello, World!', 'text_domain');
		
		echo $args['after_widget'];
	}
	
	function authenticate($username, $password) {
    $url = 'https://alert.logitech.com/services/membership.svc/authenticate';
    $data = '<AuthInfo><UserName>' . escape_xml($username) . 
            '</UserName><Password>' . escape_xml($password) . 
            '</Password></AuthInfo>';
    $options = array(
        'http' => array(
            'header'  => "Content-type: text/xml\r\n"
        ),
    );
    $response = http_parse_message(http_post_data($url, $data, $options));
    if ($response['responseCode'] == 200) {
        return $response['headers']['X-Authorization-Token']; 
    }
    return null;
	}
	
	function escape_xml($value) {
	    return strtr(
        $value, 
        array(
            "<" => "&lt;",
            ">" => "&gt;",
            '"' => "&quot;",
            "'" => "&apos;",
            "&" => "&amp;",
        )
    );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		if (isset($instance['username'])) {
			$username = $instance['username'];
		}
		if (isset($instance['password'])) {
			$password = $instance['password'];
		}
		if (isset($instance['mac'])) {
			$mac = $instance['mac'];
		}
		// Switching from PHP to HTML markup, hence the end tag.
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Username:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('password'); ?>"><?php _e('Password:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('password'); ?>" name="<?php echo $this->get_field_name('password'); ?>" type="password" value="<?php echo esc_attr($password); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('mac'); ?>"><?php _e('Camera MAC Address:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('mac'); ?>" name="<?php echo $this->get_field_name('mac'); ?>" type="text" value="<?php echo esc_attr($mac); ?>">
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
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['username'] = (!empty($new_instance['username'])) ? strip_tags($new_instance['username']) : '';
		$instance['password'] = (!empty($new_instance['password'])) ? strip_tags($new_instance['password']) : '';
		$instance['mac'] = (!empty($new_instance['mac'])) ? strip_tags($new_instance['mac']) : '';

		return $instance;
	}
}

?>