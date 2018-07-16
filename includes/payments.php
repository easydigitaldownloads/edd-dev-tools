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
		global $wpdb;

		ini_set( 'xdebug.var_display_max_depth', 5 );
		ini_set( 'xdebug.var_display_max_children', 256 );
		ini_set( 'xdebug.var_display_max_data', 1024 );
		?>
		<div id="edd-payment-meta" class="postbox">
			<h3 class="hndle"><?php _e( 'Order Meta Items', 'edd-dev-tools' ); ?></h3>
			<div class="inside">
				<?php
				$meta_sql     = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}edd_ordermeta WHERE edd_order_id = $payment_id";
				$payment_meta = $wpdb->get_results( $meta_sql );
				?>
				<div>
					<table class="wp-list-table widefat striped downloads">
						<thead>
							<tr>
								<th><?php _e( 'ID', 'edd-dev-tools' ); ?></th>
								<th><?php _e( 'Key', 'edd-dev-tools' ); ?></th>
								<th><?php _e( 'Value', 'edd-dev-tools' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'ID', 'edd-dev-tools' ); ?></th>
								<th><?php _e( 'Key', 'edd-dev-tools' ); ?></th>
								<th><?php _e( 'Value', 'edd-dev-tools' ); ?></th>
							</tr>
						</tfoot>
						<tbody>
							<?php if ( ! empty( $payment_meta ) ) : ?>
								<?php foreach ( $payment_meta as $meta ) : ?>
									<tr>
										<td><?php echo $meta->meta_id; ?></td>
										<td><?php echo $meta->meta_key; ?></td>
										<td>

												<?php
												if ( is_serialized( $meta->meta_value ) ) {
													_e( 'Serialized Data', 'edd-dev-tools' );
													?>
													<span class="dashicons dashicons-visibility" title="<?php echo esc_attr( $meta->meta_value ); ?>"></span>
													<p>
														<?php edd_dev_tools()->print_pre( unserialize( $meta->meta_value ) ); ?>
													</p>
													<?php
												} else {
													?><code><?php echo $meta->meta_value; ?></code><?php
												}
												?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr><td colspan="3"><?php printf( __( 'No Order Meta Found', 'edd-dev-tools' ), edd_get_label_plural() ); ?></td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

}

EDD_DT_Payments::instance();
