<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Get_WC_Cart
 */
class LeaseCloud_Get_WC_Cart {
	/**
	 * Returns the total of the WooCommerce Cart without tax.
	 *
	 * @return float
	 */
	public function get_cart_totals() {
		$cart_total = WC()->cart->cart_contents_total + WC()->cart->shipping_total;
		$total = $cart_total;
		return $total;
	}

	/**
	 * Returns the total of the WooCommerce Cart without tax and shipping.
	 *
	 * @return float
	 */
	public function get_cart_totals_ex_shipping() {
		$cart_total = WC()->cart->subtotal_ex_tax;
		$total = $cart_total;
		return $total;
	}
}
