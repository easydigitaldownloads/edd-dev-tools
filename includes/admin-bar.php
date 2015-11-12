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
		add_action( 'admin_bar_menu', array( $this, 'empty_cart' ), 999 );

		// Capture actions from links added to the menu bar, if needed
		add_action( 'init', array( $this, 'process_empty_cart' ) );

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

	public function empty_cart( $wp_admin_bar ) {
		$args = array(
			'id'    => 'edd_empty_cart',
			'title' => 'Empty Cart',
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

}

EDD_DT_Admin_Bar::instance();
