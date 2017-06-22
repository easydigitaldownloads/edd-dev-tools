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
		add_meta_box( 'edd_dt_recent_payments', __( 'Recent Payments (Dev Tools)', 'edd-dev-tools' ), array( $this, 'render_metabox' ), 'download', 'normal', 'core' );
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

}

EDD_DT_Downloads::instance();
