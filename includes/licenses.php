<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Licenses {

	private static $instance;

	private function __construct() {
		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Licenses();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'edd_sl_license_key_details', array( $this, 'admin_link_license_keys_template' ), 999999, 2 );
	}

	public function admin_link_license_keys_template( $license_id ) {
		?>
		<br />
		<a target="_blank" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-licenses&view=overview&license=' . $license_id ); ?>">View In Admin</a>
		<?php
	}

}

EDD_DT_Licenses::instance();
