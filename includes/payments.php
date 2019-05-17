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
		$this->filters();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Payments();
		}

		return self::$instance;
	}

	private function hooks() {
		if ( version_compare( EDD_VERSION, '3.0.0-beta1', '<' ) ) {
			add_action( 'edd_view_order_details_main_after', array( $this, 'legacy_payment_post_meta' ), 99, 1 );
		} else {
			add_action( 'edd_view_order_details_main_after', array( $this, 'payment_post_meta' ), 99, 1 );
			add_action( 'edd_order_item_title_and_actions', array( $this, 'order_item_meta' ), 10, 2 );
		}
	}

	private function filters() {
		add_filter( 'edd_order_item_row_actions', array( $this, 'order_actions' ), 10, 2 );
	}

	public function order_actions( $actions, $item ) {
		$actions['view-item-meta'] = '<a onClick="jQuery(this).parent().parent().parent().parent().find(\'.order-item-meta\').slideToggle(); return false;" class="view-order-item-meta" href="#">' . __( 'View Item Meta', 'edd-dev-tools' ) . '</a>';

		return $actions;
	}

	public function order_item_meta( $order_title, $order_item ) {
		ob_start();
		?>
		<div class="order-item-meta" style="display:none">
		<?php
		global $wpdb;

		ini_set( 'xdebug.var_display_max_depth', 5 );
		ini_set( 'xdebug.var_display_max_children', 256 );
		ini_set( 'xdebug.var_display_max_data', 1024 );
		?>
		<div class="postbox">
			<h3 class="hndle"><?php _e( 'Order Item Meta Items', 'edd-dev-tools' ); ?></h3>
			<div class="inside">
				<?php
				$meta_sql  = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}edd_order_itemmeta WHERE edd_order_item_id = $order_item->id";
				$item_meta = $wpdb->get_results( $meta_sql );
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
							<?php if ( ! empty( $item_meta ) ) : ?>
								<?php foreach ( $item_meta as $meta ) : ?>
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
		</div>
		<?php

		return $order_title . ob_get_clean();
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

	public function legacy_payment_post_meta( $payment_id ) {
		global $wpdb;

		ini_set( 'xdebug.var_display_max_depth', 5 );
		ini_set( 'xdebug.var_display_max_children', 256 );
		ini_set( 'xdebug.var_display_max_data', 1024 );
		?>
		<div id="edd-payment-meta" class="postbox">
			<h3 class="hndle"><?php _e( 'Payment Postmeta Items', 'edd-dev-tools' ); ?></h3>
			<div class="inside">
				<?php
				$meta_sql     = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = $payment_id";
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
							<tr><td colspan="3"><?php printf( __( 'No Customer Meta Found', 'edd-dev-tools' ), edd_get_label_plural() ); ?></td></tr>
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
