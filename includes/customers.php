<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Customers {

	private static $instance;

	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Customers();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'edd_after_customer_edit_link', array( $this, 'switch_to_user' ), 10, 1 );
		add_filter( 'edd_customer_tabs', array( $this, 'register_tab' ), 999, 1 );
		add_filter( 'edd_customer_views', array( $this, 'register_view' ), 10, 1 );
		add_filter( 'edd_customers_table_top', array( $this, 'add_clear_verification_button' ) );
		add_action( 'edd_dt_verify_all', array( $this, 'process_verify_all' ) );
	}

	public function switch_to_user( $customer ) {
		if ( ! class_exists( 'user_switching' ) ) {
			return;
		}

		if ( $customer->user_id < 1 ) {
			return;
		}

		$user = new WP_User( $customer->user_id );

		$link = user_switching::maybe_switch_url( $user );

		echo '<a class="button secondary" href="' . $link . '">' . __( 'Switch to User', 'edd-dev-tools' ) . '</a>';
	}

	public function register_tab( $tabs ) {
		$tabs['meta'] = array( 'dashicon' => 'dashicons-networking', 'title' => _x( 'Meta', 'Customer Meta tab title', 'edd-dev-tools' ) );
		return $tabs;
	}

	public function register_view( $views ) {
		$views['meta'] = 'EDD_DT_Customers::display_meta_tab';
		return $views;
	}

	public static function display_meta_tab( $customer ) {
		global $wpdb;
		ini_set( 'xdebug.var_display_max_depth', 5 );
		ini_set( 'xdebug.var_display_max_children', 256 );
		ini_set( 'xdebug.var_display_max_data', 1024 );
		$meta_sql      = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}edd_customermeta WHERE edd_customer_id = $customer->id";
		$customer_meta = $wpdb->get_results( $meta_sql );
		?>

		<div id="edd-item-notes-wrapper">
			<div class="edd-item-notes-header">
				<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
			</div>
			<h3><?php _e( 'Customer Meta', 'edd-dev-tools' ); ?></h3>

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
						<?php if ( ! empty( $customer_meta ) ) : ?>
							<?php foreach ( $customer_meta as $meta ) : ?>
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

		<?php
	}

	public function add_clear_verification_button() {
		global $wpdb;
		$has_pending_users = $wpdb->get_results( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '_edd_pending_verification' LIMIT 1" );

		if ( ! empty( $has_pending_users ) ) {
			echo '<a class="button secondary" href="' . add_query_arg( array( 'edd_action' => 'dt_verify_all' ) ) . '">' . __( 'Verify All Users', 'edd-dev-tools' ) . '</a>';
		}
	}

	public function process_verify_all() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_edd_pending_verification' ) );
		wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-customers' ) );
		exit;
	}

}

EDD_DT_Customers::instance();
