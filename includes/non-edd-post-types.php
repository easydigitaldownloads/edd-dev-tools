<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Other_Post_Types {

	private static $instance;

	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Other_Post_Types();
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
	}

	public function register_meta_boxes() {
		add_meta_box( 'edd_dt_download_meta'  , __( 'Post Meta (Dev Tools)', 'edd-dev-tools' )  , array( $this, 'render_post_meta' ), 'post', 'normal', 'core' );
		add_meta_box( 'edd_dt_download_meta'  , __( 'Post Meta (Dev Tools)', 'edd-dev-tools' )  , array( $this, 'render_post_meta' ), 'page', 'normal', 'core' );
	}

	public function render_post_meta() {
		global $post, $wpdb;
		$meta_sql  = "SELECT meta_id, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = $post->ID";
		$post_meta = $wpdb->get_results( $meta_sql );
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
					<?php if ( ! empty( $post_meta ) ) : ?>
						<?php foreach ( $post_meta as $meta ) : ?>
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
						<tr><td colspan="3"><?php printf( __( 'No Post Meta Found', 'edd-dev-tools' ), edd_get_label_plural() ); ?></td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

}

EDD_DT_Other_Post_Types::instance();
