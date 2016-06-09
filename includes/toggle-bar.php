<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Toggle_Bar {

	private static $instance;

	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->main_id = 'edd-toggle';
		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Toggle_Bar();
		}

		return self::$instance;
	}

	private function hooks() {
		// Add items to the admin bar
		add_action( 'admin_bar_menu', array( $this, 'main_item' ), 999 );

		// Capture actions from links added to the menu bar, if needed
		add_action( 'init', array( $this, 'process_toggle' ) );

	}

	public function main_item( $wp_admin_bar ) {
		if ( is_network_admin() ) {
			return;
		}

		$this->admin_bar = $wp_admin_bar;

		$args = array(
			'id'    => $this->main_id,
			'title' => sprintf( '<span class="ab-icon"></span> <span class="ab-label">%s</span>', __( 'Settings' ) ),
			'href'  => admin_url( 'edit.php?post_type=download&page=edd-settings' ),
			'meta'  => array(
				'class' => 'has-many',
			),
		);
		$this->admin_bar->add_node( $args );

		$this->header_locations = array();

		$settings         = edd_get_registered_settings();
		$tabs             = array_keys( $settings );
		$header_locations = array();

		// Hang on tight, it's about to get ugly
		foreach ( $tabs as $tab_key => $tab ) {
			foreach ( $settings[ $tab ] as $section_key => $section ) {
				foreach ( $section as $setting_id => $setting ) {
					if ( isset( $setting['type'] ) && 'header' == $setting['type'] ) {
						$this->header_locations[ $setting['id'] ] = array (
							'tab'     => $tab,
							'section' => $section_key,
						);
					}
				}
			}
		}

		$this->add_sub_items();
	}

	public function add_sub_items( $settings = false ) {

		if ( false === $settings ) {
			$settings         = edd_get_registered_settings();
		}

		$header          = '';
		$headers         = array();
		$to_add          = array();

		foreach ( $settings as $key => $setting ) {

			$type = isset( $setting['type'] ) ? $setting['type'] : false;

			// This item isn't a setting but a parent array, check it's sub arrays
			if ( false === $type ) {
				$this->add_sub_items( $setting );
			}

			$allowed_types = array( 'checkbox', 'header' );
			if ( false === $type || ! in_array( $type, $allowed_types ) ) {
				continue;
			}

			if ( $type === 'header' ) {
				$headers[]    = $setting;
				$header       = $setting['id'];
			} else {

				if ( empty( $header ) ) {
					$header = $key;
					$name   = str_replace( '_', ' ', $header );
					$name   = str_replace( '-', ' ', $name );
					$headers[] = array( 'id' => $header, 'name' => ucwords( $name ) );
				}

				$to_add[ $header ][] = $setting;

			}
		}

		if ( ! empty( $headers ) ) {

			// Itterate through the headers we found
			foreach ( $headers as $key => $header ) {

				// If this header has subitems, add it and it's settings
				if ( ! empty( $to_add[ $header['id'] ] ) ) {
					$location = ! empty( $this->header_locations[ $header['id'] ] ) ? $this->header_locations[ $header['id'] ] : false;

					$this->add_heading( $header, $location );

					foreach ( $to_add[ $header['id'] ] as $item ) {
						$this->add_sub_item( $item );
					}

				}

			}
		}
	}

	public function add_heading( $item, $location = false ) {
		$args = array(
			'id'     => 'edd-toggle_' . $item['id'] . '_header',
			'title'  => strip_tags( $item['name'] ),
			'parent' => 'edd-toggle',
			'meta'   => array(
				'class' => 'header',
				'title' => strip_tags( $item['name'] ),
			),
		);

		if ( ! empty( $location ) ) {
			$base_url = admin_url( 'edit.php?post_type=download&page=edd-settings' );
			$query_args = array(
				'tab'     => $location['tab'],
				'section' => $location['section'],
			);

			$args['href'] = add_query_arg( $query_args, $base_url );
		}

		$this->admin_bar->add_node( $args );
	}

	public function add_sub_item( $item ) {
		$current_settings    = get_option( 'edd_settings' );

		$is_active = ! empty( $current_settings[ $item['id'] ] ) ? true : false;

		$args = array(
			'id'     => 'edd-toggle_' . $item['id'],
			'title'  => strip_tags( $item['name'] ),
			'href'   => $this->get_toggle_url( $item['id'] ),
			'parent' => 'edd-toggle',
			'meta'   => array(
				'class' => $is_active ? 'is-active' : '',
				'title' => strip_tags( $item['name'] ),
			),
		);

		$this->admin_bar->add_node( $args );
	}

	public function process_toggle() {
		if ( isset( $_GET['edd_toggle_setting'] ) ) {

			$option_id        = $_GET['edd_toggle_setting'];
			$current_settings = get_option( 'edd_settings' );

			if ( isset( $current_settings[ $option_id ] ) ) {
				unset( $current_settings[ $option_id ] );
			} else {
				$current_settings[ $option_id ] = '1';
			}

			update_option( 'edd_settings', $current_settings );

			wp_redirect( remove_query_arg( 'edd_toggle_setting' ) );
			exit;
		}
	}

	private function get_current_url() {
		global $wp;

		if ( empty( $this->current_url ) ) {
			$url = is_admin() ? add_query_arg( array() ) : home_url( add_query_arg( array(), $wp->request ) );
			$url = remove_query_arg( array( '_wpnonce', 'redirect_to' ), $url );
			$this->current_url = $url;
		}

		return $this->current_url;
	}

	private function get_toggle_url( $setting_id ) {
		return add_query_arg( array( 'edd_toggle_setting' => $setting_id ), $this->get_current_url() );
	}

}

EDD_DT_Toggle_Bar::instance();
