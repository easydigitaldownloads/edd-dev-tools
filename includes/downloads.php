<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Downloads {

	private static $instance;

	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Downloads();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
	}

	public function register_meta_boxes() {
		add_meta_box( 'edd_dt_recent_payments', __( 'Recent Payments (Dev Tools)', 'edd-dev-tools' ), array( $this, 'render_metabox' )      , 'download', 'normal', 'core' );
		add_meta_box( 'edd_dt_download_meta'  , __( 'Download Meta (Dev Tools)', 'edd-dev-tools' )  , array( $this, 'render_download_meta' ), 'download', 'normal', 'core' );
	}

	public function render_metabox() {
		global $post;
		$args = array(
			'download' => $post->ID,
			'number'   => 5,
		);
		$payments_query = new EDD_Payments_Query( $args );
		$payments       = $payments_query->get_payments();
		?>
		<div>
			<table class="wp-list-table widefat striped downloads">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'edd-dev-tools' ); ?></th>
						<th><?php _e( 'Amount', 'edd-dev-tools' ); ?></th>
						<th><?php _e( 'Date', 'edd-dev-tools' ); ?></th>
						<th><?php _e( 'Status', 'edd-dev-tools' ); ?></th>
						<th><?php _e( 'Actions', 'edd-dev-tools' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $payments ) ) : ?>
						<?php foreach ( $payments as $payment ) : ?>
							<tr>
								<td><?php echo $payment->ID; ?></td>
								<td><?php echo edd_sanitize_amount( edd_format_amount( $payment->total ) ); ?></td>
								<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ); ?></td>
								<td><?php echo $payment->status_nicename; ?></td>
								<td>
									<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID ); ?>">
										<?php _e( 'View Details', 'edd-dev-tools' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr><td colspan="5"><?php _e( 'No Payments Found', 'edd-dev-tools' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_download_meta() {
		global $post, $wpdb;
		$meta_sql      = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = $post->ID";
		$download_meta = $wpdb->get_results( $meta_sql );
		?>
		<div class="inside">
			<div style=>
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
					<?php if ( ! empty( $download_meta ) ) : ?>
						<?php foreach ( $download_meta as $meta ) : ?>
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
											<?php edd_dev_tools()->print_pre( unserialize( $meta->meta_value ), false, true ); ?>
										</p>
										<?php
									} else {
										?><code style="white-space: normal"><?php echo $meta->meta_value; ?></code><?php
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
		<?php
	}

}

EDD_DT_Downloads::instance();
