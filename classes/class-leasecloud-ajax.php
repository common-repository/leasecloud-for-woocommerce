<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Ajax
 */
class LeaseCloud_Ajax {
	/**
	 * LeaseCloud_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_update_display_price', array( $this, 'update_display_price' ) );
		add_action( 'wp_ajax_nopriv_update_display_price', array( $this, 'update_display_price' ) );
		add_action( 'wp_ajax_update_selected_gateway', array( $this, 'update_selected_gateway' ) );
		add_action( 'wp_ajax_nopriv_update_selected_gateway', array( $this, 'update_selected_gateway' ) );
		add_action( 'wp_ajax_update_total_tax', array( $this, 'update_total_tax' ) );
		add_action( 'wp_ajax_nopriv_update_total_tax', array( $this, 'update_total_tax' ) );
	}

	/**
	 * Updates session that determines the displayed price.
	 */
	public function update_display_price() {
		$selected_length = $_POST['selected_length'];
		setcookie( 'leasecloud_display_tariff', $selected_length, 0, '/' );
		wp_send_json_success();
		wp_die();
	}

	/**
	 * Update session that checks if leasecloud is selected.
	 */
	public function update_selected_gateway() {
		$selected_gateway = $_POST['selected_gateway'];
		if ( 'leasecloud' === $selected_gateway ) {
			WC()->session->set( 'leasecloud_selected_gateway', $selected_gateway );
		} else {
			WC()->session->__unset( 'leasecloud_selected_gateway' );
		}

		wp_send_json_success();
		wp_die();
	}

	/**
	 * Update the total tax field.
	 */
	public function update_total_tax() {
		if ( WC()->session->get( 'leasecloud_selected_gateway' ) ) {
			$total_tax['total_tax'] = leasecloud_cart_taxes_total( $_POST['total_tax'] );
		} else {
			$total_tax['total_tax'] = $_POST['total_tax'];
		}
		wp_send_json_success( $total_tax );
		wp_die();
	}
}
new LeaseCloud_Ajax();
