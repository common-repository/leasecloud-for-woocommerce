<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );

// Add information to Order Detail table.
if ( isset($leasecloud_settings['leasecloud_only_leasing']) && 'yes' === $leasecloud_settings['leasecloud_only_leasing'] ) {
	add_filter( 'woocommerce_get_order_item_totals', 'leasecloud_add_to_order_details', 10, 2 );
	add_filter( 'woocommerce_order_formatted_line_subtotal', 'leasecloud_order_details_item_total', 10, 2 );
	add_filter( 'woocommerce_order_subtotal_to_display', 'leasecloud_subtotal_to_display', 10, 3 );
	add_filter( 'woocommerce_order_shipping_to_display', 'leasecloud_shipping_to_display', 10, 2 );
	add_filter( 'woocommerce_get_formatted_order_total', 'leasecloud_get_formatted_order_total', 10, 2 );
} else {
	add_filter( 'woocommerce_get_order_item_totals', 'leasecloud_add_to_order_details_not_lease_only', 10, 2 );
}

function leasecloud_add_to_order_details_not_lease_only( $rows, $order ) {
	if ( 'leasecloud' === $order->get_payment_method() ) {
		$order_id = $order->get_order_number();
		$payment  = get_post_meta( $order_id, '_leasecloud_monthly_payment' );
		$months   = get_post_meta( $order_id, '_leasecloud_payment_length' );
		// Add information to mail.
		$rows['montly_payment']  = array(
			'label' => __( 'Monthly payment', 'leasecloud-for-woocommerce' ) . ":",
			'value' => strip_tags( wc_price( ( $payment[0] / 100 ) ) ) . "/" . __( 'month', 'leasecloud-for-woocommerce' ),
		);
		$rows['leaseing_length'] = array(
			'label' => __( 'Leasing length', 'leasecloud-for-woocommerce' ) . ":",
			'value' => $months[0] . " " . __( 'Months', 'leasecloud-for-woocommerce' ),
		);
	}
	return $rows;
}

/**
 * Adds LeaseCloud information to the order details.
 *
 * @param array $rows The rows to be added to the order details.
 * @param array $order The WooCommerce order object.
 *
 * @return mixed
 */
function leasecloud_add_to_order_details( $rows, $order ) {
	if ( 'leasecloud' === $order->get_payment_method() ) {
		$order_id = $order->get_order_number();
		$months   = get_post_meta( $order_id, '_leasecloud_payment_length' );
		// Add information to mail.
		$rows['leaseing_length'] = array(
			'label' => __( 'Leasing length', 'leasecloud-for-woocommerce' ) . ":",
			'value' => $months[0] . " " . __( 'Months', 'leasecloud-for-woocommerce' ),
		);
	}
	return $rows;
}

function leasecloud_order_details_item_total( $subtotal, $item ) {
	$monthly_price = leasecloud_format_price( $item->get_subtotal(), false );

	return $monthly_price;
}

function leasecloud_subtotal_to_display( $subtotal, $compound, $item ) {
	if ( 'leasecloud' === $item->get_payment_method() ) {

		$total        = $item->get_subtotal();
		$monthly_cost = leasecloud_format_price( $total, false );

		return $monthly_cost;
	} else {
		return $subtotal;
	}
}

function leasecloud_shipping_to_display( $shipping, $item ) {
	if ( 'leasecloud' === $item->get_payment_method() ) {

		if ( $item->get_shipping_total() !== 0 ) {
			$total_shipping = $item->get_shipping_total();
			$monthly_cost   = leasecloud_format_price( $total_shipping, false );

			return $monthly_cost . ' &nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $item->get_shipping_method() ) . '</small>';
		}
	} else {
		return $shipping;
	}
}

function leasecloud_get_formatted_order_total( $formatted_total, $item ) {
	if ( 'leasecloud' === $item->get_payment_method() ) {

		$order_id     = $item->get_id();
		$monthly_cost = get_post_meta( $order_id, '_leasecloud_monthly_payment' );

		return strip_tags( wc_price( ( $monthly_cost[0] / 100 ) ) ) . __( '/month', 'leasecloud-for-woocommerce' );
	} else {
		return $formatted_total;
	}
}
