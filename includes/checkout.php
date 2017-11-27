<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Checkout {

	private static $instance;

	private function __construct() {
		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Checkout();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'edd_checkout_cart_item_title_after', array( $this, 'display_checkout_cart_details' ), 999999, 2 );
	}

	public function display_checkout_cart_details( $item, $key ) {
		$cart_details = EDD()->cart->get_contents_details();
		$item         = $cart_details[ $key ];
		ini_set( 'xdebug.var_display_max_depth', 5 );
		ini_set( 'xdebug.var_display_max_children', 256 );
		ini_set( 'xdebug.var_display_max_data', 1024 );

		?>
		<div>
			<a href="#" onClick="jQuery(this).next('.cart-item-details-debug').slideToggle(); return false;">View Item Details</a>
			<div class="cart-item-details-debug" style="display: none;">
				<?php echo edd_dev_tools()->print_pre( $item ); ?>
			</div>
		</div>
		<?php
	}

}

EDD_DT_Checkout::instance();
