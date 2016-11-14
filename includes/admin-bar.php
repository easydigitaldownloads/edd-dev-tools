<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_DT_Admin_Bar {

	private static $instance;

	private function __construct() {
		$this->hooks();
	}

	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new EDD_DT_Admin_Bar();
		}

		return self::$instance;
	}

	private function hooks() {
		// Add items to the admin bar
		add_action( 'admin_bar_menu', array( $this, 'blog_id' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'git_branch' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'empty_cart' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'delete_licenses' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'clear_jilt' ), 999 );

		// Capture actions from links added to the menu bar, if needed
		add_action( 'init', array( $this, 'process_empty_cart' ) );
		add_action( 'init', array( $this, 'process_delete_licenses' ) );
		add_action( 'init', array( $this, 'process_clear_jilt' ) );
	}

	public function blog_id( $wp_admin_bar ) {

		if ( ! is_multisite() ) {
			return;
		}

		$args = array(
			'id'    => 'blog_id',
			'title' => 'Blog #' . get_current_blog_id(),
		);
		$wp_admin_bar->add_node( $args );
	}

	public function git_branch( $wp_admin_bar ) {

		$git_info = @file( EDD_PLUGIN_DIR . '/.git/HEAD', FILE_USE_INCLUDE_PATH );
		if ( ! $git_info ) {
			return;
		}

		$first_line    = $git_info[ 0 ];
		$branch_string = explode( '/', $first_line, 3 );
		$branch        = $branch_string[ 2 ];

		$args = array(
			'id'    => 'edd_branch',
			'title' => $branch,
		);
		$wp_admin_bar->add_node( $args );
	}

	public function empty_cart( $wp_admin_bar ) {
		$title = __( 'Empty Cart', 'edd-dev-tools' );
		$count = count( edd_get_cart_contents() );

		if ( ! empty( $count ) ) {
			$title .= '(' . $count . ')';
		}

		$args  = array(
			'id'    => 'edd_empty_cart',
			'title' => $title,
			'href'  => add_query_arg( 'empty_cart', true ),
		);
		$wp_admin_bar->add_node( $args );
	}

	public function process_empty_cart() {
		if ( isset( $_GET['empty_cart'] ) ) {
			if ( $_GET['empty_cart'] == '1' ) {
				edd_empty_cart();

				wp_redirect( remove_query_arg( 'empty_cart' ) );
				exit;
			}
		}
	}

	public function delete_licenses( $wp_admin_bar ) {
		if ( function_exists( 'edd_software_licensing' ) ) {
			$title = __( 'Delete Licenses', 'edd-dev-tools' );

			$args  = array(
				'id'    => 'edd_delete_licenses',
				'title' => $title,
				'href'  => add_query_arg( 'delete_licenses', true ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	public function process_delete_licenses() {
		global $wpdb;
		if ( isset( $_GET['delete_licenses'] ) ) {
			if ( $_GET['delete_licenses'] == '1' ) {
				$post_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_license'" );
				if ( ! empty( $post_ids ) ) {
					$post_ids = implode( ',', $post_ids );
					$wpdb->query( "DELETE FROM $wpdb->posts WHERE ID IN ({$post_ids})" );
					$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$post_ids})" );
				}

				wp_redirect( remove_query_arg( 'delete_licenses' ) );
				exit;
			}
		}
	}

	public function clear_jilt( $wp_admin_bar ) {
		if ( function_exists( 'edd_jilt' ) ) {
			$title = __( 'Clear Jilt', 'edd-dev-tools' );

			$args  = array(
				'id'    => 'edd_clear_jilt',
				'title' => $title,
				'href'  => add_query_arg( 'clear_jilt', true ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	public function process_clear_jilt() {
		if ( isset( $_GET['clear_jilt'] ) ) {
			if ( $_GET['clear_jilt'] == '1' ) {
				delete_user_meta( get_current_user_id(), '_edd_jilt_cart_token' );
				delete_user_meta( get_current_user_id(), '_edd_jilt_order_id' );
				delete_user_meta( get_current_user_id(), '_edd_jilt_pending_recovery' );
				EDD()->session->set( 'edd_jilt_order_id', '' );
				EDD()->session->set( 'edd_jilt_cart_token', '' );
				EDD()->session->set( '_edd_jilt_pending_recovery', '' );

				wp_redirect( remove_query_arg( 'clear_jilt' ) );
				exit;
			}
		}
	}
}

EDD_DT_Admin_Bar::instance();
