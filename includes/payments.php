<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Payments {

	private static $instance;

	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Payments();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'edd_view_order_details_main_after', array( $this, 'payment_post_meta' ), 99, 1 );
	}

	public function payment_post_meta( $payment_id ) {
	ini_set( 'xdebug.var_display_max_depth', 5 );
	ini_set( 'xdebug.var_display_max_children', 256 );
	ini_set( 'xdebug.var_display_max_data', 1024 );
?>
<div id="edd-payment-meta" class="postbox">
	<h3 class="hndle"><?php _e( 'Payment Postmeta Items', 'edd-dev-tools' ); ?></h3>
	<div class="inside">
		<div style="overlfow:auto">
		<?php $post_meta = get_metadata( 'post', $payment_id ); ?>
			<pre style="overflow: auto;word-wrap: break-word;">
			<?php
			foreach ( $post_meta as $key => $value ) {
				if ( is_serialized( $value[0] ) ) {
					echo $key . '=> ';
					var_dump( unserialize( $value[0] ) );
				} else {
					echo $key . ' => ' . $value[0] . "\n";
				}
			}
			?>
			</pre>
		</div>
	</div>
</div>
<?php
	}

}

EDD_DT_Payments::instance();
