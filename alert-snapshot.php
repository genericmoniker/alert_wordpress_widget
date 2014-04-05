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

namespace esmithy_net; # PHP>=5.3.0

# Uncomment for debugging:
#error_reporting(E_ALL);
#ini_set('display_errors', 'on');

/* Register the widget */
add_action('widgets_init', function() {
    register_widget('esmithy_net\Alert_Snapshot_Widget');
});


class Alert_Snapshot_Widget extends \WP_Widget {

    const IMAGE_FILENAME = '_alert_snapshot_.jpg';
    const ONE_MINUTE = 60;

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
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'].$title.$args['after_title'];

        echo '<!-- Alert widget begin -->';
        
        if (function_exists('curl_version')) {
            if ($this->cached_image_too_old()) {
                $this->download_image(
                    $instance['username'], 
                    $instance['password'],
                    $instance['mac']
                );
                echo '<!-- new image downloaded -->';
            }
            $image_url = get_site_url(get_current_blog_id(), self::IMAGE_FILENAME);
            echo '<img class="aligncenter" src="' . $image_url . '" />';
        } else {
            echo '<p>cURL support missing</p>';
        }
        
        echo '<!-- Alert widget end -->';

        echo $args['after_widget'];
    }
    
    function cached_image_too_old() {
        if (file_exists(self::IMAGE_FILENAME)) {
            return time()-filemtime(self::IMAGE_FILENAME) > self::ONE_MINUTE;
        }
        return true;
    }
    
    function local_filetime($filename) {
        $utc_date = gmdate("F d Y H:i:s", filemtime($filename)) . ' UTC';
        $time = strtotime($utc_date.' UTC');
        return date("F d Y H:i:s T", $time);
    }
    
    function download_image($username, $password, $mac) {
        $token = $this->authenticate($username, $password);
        if ($token != null) {
            $url = 'https://alert.logitech.com/Services/camera2.svc/' . $mac . '/snapshotviewable?_auth=' . $token;
            file_put_contents(self::IMAGE_FILENAME, file_get_contents($url));
        }
        else {
            echo 'Authentication failed';
        }
    }

	function authenticate($username, $password) {
        $url = 'https://alert.logitech.com/Services/membership.svc/authenticate';
        $data = '<AuthInfo><UserName>' . $this->escape_xml($username) .
                '</UserName><Password>' . $this->escape_xml($password) .
                '</Password></AuthInfo>';
        $content = $this->post_xml($url, $data);
        if (preg_match('#X-Authorization-Token:(.*)\n#', $content, $matches)) {
            return trim($matches[1]);
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
	
	function post_xml($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,"$data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);	
        $content = curl_exec($ch);
        echo '<!-- curl_error: ', curl_error($ch), '-->';
        curl_close($ch);
        return $content;
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