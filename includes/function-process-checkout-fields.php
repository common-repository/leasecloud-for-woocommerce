<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check required fields when payment has been placed.
add_action( 'woocommerce_checkout_process', 'leasecloud_process_checkout_fields' );

/**
 * Validates the added checkout fields.
 */
function leasecloud_process_checkout_fields() {
	// Check if Leasecloud is the selected payment method.
	if ( $_POST['payment_method'] === 'leasecloud' ) {
		// Check if org nr is not set or not correct format and add an error.
		if ( ! $_POST['leasecloud_org_nr'] ) {
			wc_add_notice( __( 'Please enter an organisation number before you complete the order', 'leasecloud-for-woocommerce' ), 'error' );
		}
		// Check if lease plan is not selected.
		if ( ! $_POST['leasecloud-payment-length'] ) {
			wc_add_notice( __( 'Please select a lease plan', 'leasecloud-for-woocommerce' ), 'error' );
		}
	}
}
