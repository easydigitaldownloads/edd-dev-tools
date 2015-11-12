<?php
/*
Plugin Name: Easy Digital Downloads - Developer Tools
Description: A collection of tools to use during development on Easy Digital Downloads
Plugin URI: https://easydigitaldownlaods.com
Author: Chris Klosowski
Author URI: https://easydigitaldownloads.com
Version: 1.0
License: GPL2
Text Domain: edd-dev-tools
Contributors: cklosows
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Dev_Tools' ) ) {

class EDD_Dev_Tools {

	private static $instance;

	private function __construct() {
		$this->constants();
		$this->includes();
		$this->scripts();
		$this->hooks();
		$this->filters();
	}

	static public function instance() {

		if ( !self::$instance ) {
			self::$instance = new EDD_Dev_Tools();
		}

		return self::$instance;

	}

	private function constants() {

		// Plugin version
		if ( ! defined( 'EDD_DT_VERSION' ) ) {
			define( 'EDD_DT_VERSION', '1.0' );
		}

		// Plugin Folder Path
		if ( ! defined( 'EDD_DT_PLUGIN_DIR' ) ) {
			define( 'EDD_DT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'EDD_DT_PLUGIN_URL' ) ) {
			define( 'EDD_DT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'EDD_DT_PLUGIN_FILE' ) ) {
			define( 'EDD_DT_PLUGIN_FILE', __FILE__ );
		}

	}

	private function includes() {

		include_once EDD_DT_PLUGIN_DIR . 'includes/admin-bar.php';

	}

	private function scripts() {}

	private function hooks() {}

	private function filters() {}

	public function register_settings() {}


	function setting_section_callback() {}


} // End WP_CodeShare class

} // End Class Exists check

function edd_load_dev_tools() {
	return EDD_Dev_Tools::instance();
}
add_action( 'plugins_loaded', 'edd_load_dev_tools' );
