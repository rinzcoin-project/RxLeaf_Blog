<?php

/*
Plugin Name: ADS PRO – Marketing Agency Parser
Plugin URI: http://bsapro.scripteo.info
Description: Display Ad Spaces from Marketing Agency.
Author: Scripteo
Author URI: http://codecanyon.net/user/scripteo
Version: 1.0.41
License: GPL2
*/

// Require functions
require_once 'lib/functions.php';

class BuySellAdsMaPro
{
	private $plugin_id = 'bsa_pro_ma_plugin';
	private $plugin_version = '1.0.41';

	function __construct() {
		register_activation_hook(__FILE__, array($this, 'onActivate'));
		register_uninstall_hook(__FILE__, array('BuySellAdsMaPro', 'onUninstall'));
	}

	static function onUninstall() {
		$ver_opt = 'bsa_pro_ma_plugin'.'_version';
		delete_option($ver_opt);
	}

	function onActivate() {
		$ver_opt = 'bsa_pro_ma_plugin'.'_version';
		$installed_version = get_option($ver_opt, NULL);

		if($installed_version == NULL) {

		} else {

			switch(version_compare($installed_version, $this->plugin_version)) {

				case 0;
					// if the same
					//update_option($ver_opt, $this->plugin_version);
					break;

				case 1;
					// if newer
					//update_option($ver_opt, $this->plugin_version);
					break;

				case -1;
					// if older
					//update_option($ver_opt, $this->plugin_version);
					break;
			}
		}
	}
}

class BSA_PRO_MA_ShortCode_Init extends WP_Widget {
	public function __construct() {
		parent::__construct('bsa_shortcode_widget', 'ADS PRO - Shortcode', array(
			'description' => 'Here you can add shortcode from ADS PRO.',
		));
	}
	public function widget( $args, $instance ) {

		extract( $args );
		// these are the widget options
		$shortcode = $instance['shortcode'];

		// -- Start -- Content
		if ( $shortcode != '' ) {
			echo do_shortcode( $shortcode );
		} else {
			echo 'Shortcode field is empty!';
		}
		// -- END -- Content
	}
	public function form( $instance ) {
		// Check values
		if( $instance) {
			$shortcode = esc_attr($instance['shortcode']);
		} else {
			$shortcode = '';
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id('shortcode'); ?>"><?php _e('Shortcode', 'wp_widget_plugin'); ?></label><br>
			<input id="<?php echo $this->get_field_id('shortcode'); ?>" name="<?php echo $this->get_field_name('shortcode'); ?>" style="width:100%;" value="<?php echo $shortcode; ?>" />
		</p>

	<?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// Fields
		$instance['shortcode'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['shortcode']) ) );
		return $instance;
	}
}
add_action('widgets_init',
     create_function('', 'return register_widget("BSA_PRO_MA_ShortCode_Init");')
);

$BuySellAdsMaPlugin = new BuySellAdsMaPro();