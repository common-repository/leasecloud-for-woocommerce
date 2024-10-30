<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
if ( 'yes' === $leasecloud_settings['enabled'] ) {
	// Single product page.
	if ( 'standard' !== $leasecloud_settings['leasecloud_display_price_single'] ) {
		add_action( 'woocommerce_single_product_summary', 'leasecloud_add_price_display_single_product', 10, 2 );
	}
	// Shop category page.
	if ( 'standard' !== $leasecloud_settings['leasecloud_display_price_shop'] ) {
		add_action( 'woocommerce_after_shop_loop_item_title', 'leasecloud_add_price_display_single_product', 15, 2 );
	}
	// Cart.
	if ( 'yes' === $leasecloud_settings['leasecloud_only_leasing'] ) {
		add_filter( 'woocommerce_cart_item_price', 'leasecloud_cart_change_item_price', 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', 'leasecloud_cart_change_item_total_price', 10, 3 );
		add_filter( 'woocommerce_cart_subtotal', 'leasecloud_cart_subtotal', 10, 1 );
		add_filter( 'woocommerce_cart_shipping_method_full_label', 'leasecloud_cart_shipping', 10, 2 );
		add_filter( 'woocommerce_cart_totals_order_total_html', 'leasecloud_cart_order_total', 10, 1 );
		add_action( 'woocommerce_before_mini_cart', 'leasecloud_add_display_price_mini_cart' );
		add_action( 'woocommerce_after_shop_loop_item_title', 'leasecloud_add_price_display_single_product', 15, 2 );
		add_action( 'woocommerce_single_product_summary', 'leasecloud_add_price_display_single_product', 10, 2 );

		// Remove default displays.
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	} else {
		add_action( 'woocommerce_cart_totals_before_order_total', 'leasecloud_add_display_price_cart' );
	}
	// Single order page.
	add_action( 'woocommerce_admin_order_totals_after_total', 'leasecloud_add_display_price_admin' );
}

/**
 * Adds the monthly price to single product display.
 */
function leasecloud_add_price_display_single_product() {
	// Calculate price per month.
	global $product;
	$price          = $product->get_price();
	$tax_rate       = WC_Tax::get_rates( $product->get_tax_class() );
	$monthly_amount = '<p class="price">' . leasecloud_format_price( $price, false, false, $tax_rate ) . '</p>';
	echo $monthly_amount;
}

/**
 * Adds the monthly price to the cart page.
 */
function leasecloud_add_display_price_cart() {
	$get_cart_total = new LeaseCloud_Get_WC_Cart();
	$price          = $get_cart_total->get_cart_totals();
	$monthly_cost   = '<strong>' . leasecloud_format_price( $price, false ) . '</strong>';
	echo '<tr class="leasecloud-monthly-payment"><th>' . __( 'Monthly payment', 'leasecloud-for-woocommerce' ) . '</th><td data-title=' . __( 'Monthly payment', 'leasecloud-for-woocommerce' ) . '>' . $monthly_cost . '</td></tr>';
	// Change items price.
	add_filter(
		'woocommerce_cart_item_price', function() {
			$cart = WC()->cart->get_cart();
			foreach ( $cart as $cart_item_key => $cart_item ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$price    = wc_get_price_to_display( $_product );
				return leasecloud_format_price( $price );
			}
		}
	);
}

/**
 * Adds the monthly price to the edit order page.
 *
 * @param int $order_id WooCommerce order id.
 */
function leasecloud_add_display_price_admin( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( 'leasecloud' === $order->get_payment_method() ) {
		$cost = get_post_meta( $order_id, '_leasecloud_monthly_payment' );
		// Get correct format of the cost.
		$cost         = $cost[0] / 100;
		$monthly_cost = '<strong>' . wc_price( $cost ) . __( '/month', 'leasecloud-for-woocommerce' ) . '</strong>';
		echo '<tr><td class="label leasecloud-monthly-payment">' . __( 'Monthly payment', 'lesecloud-for-woocommerce' ) . '</td><td width="1%"></td><td class="total leasecloud-monthly-payment">' . $monthly_cost . '</td></tr>';
	}
}

/**
 * Add the monthly price to the mini cart
 */
function leasecloud_add_display_price_mini_cart() {
	global $monthly_amount;
	$cart = WC()->cart->get_cart();
	foreach ( $cart as $cart_item_key => $cart_item ) {
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$price    = wc_get_price_excluding_tax( $_product );

		$monthly_amount = leasecloud_format_price( $price, false );
		// Change each items price.
		add_filter(
			'woocommerce_cart_item_price', function() {
				global $monthly_amount;
				return $monthly_amount;
			}
		);
	}
	// Change subtotal price.
	add_filter(
		'woocommerce_cart_subtotal', function() {
			$get_cart_total = new LeaseCloud_Get_WC_Cart();
			$price          = $get_cart_total->get_cart_totals_ex_shipping();

			return leasecloud_format_price( $price, false );
		}
	);
}

/**
 * Change the price on cart page to monthly price.
 *
 * @return string
 */
function leasecloud_cart_change_item_price( $price, $cart_item, $cart_item_key ) {
	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item );
	$price    = wc_get_price_excluding_tax( $_product );
	return leasecloud_format_price( $price, false );
}

/**
 * Add monthly price to product lines on cart page.
 *
 * @param string $subtotal The subtotal HTML string.
 * @param array  $cart_item The cart item object.
 *
 * @return string
 */
function leasecloud_cart_change_item_total_price( $subtotal, $cart_item ) {
	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item );
	$quantity = $cart_item['quantity'];
	$price    = wc_get_price_excluding_tax( $_product ) * $quantity;
	// $price            = wc_get_price_to_display( $_product );
	return leasecloud_format_price( $price, false );
}

/**
 * Returns the formated cart subtotal.
 *
 * @param string $cart_subtotal the standard HTML code for the cart subtotal
 *
 * @return string
 */
function leasecloud_cart_subtotal( $cart_subtotal ) {
	$price = WC()->cart->subtotal_ex_tax;
	return leasecloud_format_price( $price, false );
}

/**
 * Returns the formated shipping options.
 *
 * @param string $label  the old Label.
 * @param array  $method the shipping method.
 *
 * @return string
 */
function leasecloud_cart_shipping( $label, $method ) {
	$label = $method->label;
	$price = $method->cost;

	return $label . ': ' . leasecloud_format_price( $price );
}

/**
 * Returns the cart total taxes.
 *
 * @param string $total_taxes the standard HTML code of cart total taxes.
 *
 * @return string
 */
function leasecloud_cart_taxes_total( $total_taxes ) {
	$price = WC()->cart->tax_total;
	return leasecloud_format_price( $price, false );
}

/**
 * Returns the cart order total HTML code.
 *
 * @param string $value the standard HTML code of cart order total.
 *
 * @return string
 */
function leasecloud_cart_order_total( $value ) {
	$value = WC()->cart->total;
	return leasecloud_format_price( $value, false, true );
}

/**
 * Calculates and formats the price for price per month.
 *
 * @param string $price the html code of the price.
 *
 * @return string
 */
/**
 * @todo Research tax_rate and maybe send from other function calls?
 */
function leasecloud_format_price( $price, $needs_formating = true, $vat = false, $tax_rate = [] ) {
	if ( $needs_formating === true ) {
		// Remove any tags and format the price for calculation.
		$price = wp_strip_all_tags( $price );
		// Remove unicode codes.
		$price = preg_replace( '/&.*?;/', '', $price );
		// Remove thousand and decimal separators.
		$price = preg_replace( '/[,. ]/', '', $price );
		// Format correctly.
		if ( wc_get_price_decimals() > 0 ) {
			$price = round( floatval( $price / 100 ), 0 );
		}
	}
	if ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
		$tax_rate = reset( $tax_rate );
		$rate     = ( floatval( $tax_rate['rate'] ) / 100 ) + 1;
		$price    = floatval( $price );
		$price    = ( $price / $rate );
	}
	$calculate_tariffs = new LeaseCloud_Calculate_Tariffs();
	if ( ! isset( $_COOKIE['leasecloud_display_tariff'] ) ) {
		$monthly_amount = $calculate_tariffs->get_monthly_cost_from_default_tariff( $price );
	} else {
		$selected_tariff = $_COOKIE['leasecloud_display_tariff'];
		$monthly_amount  = $calculate_tariffs->get_monthly_cost_from_selected_tariff( $price, $selected_tariff );
	}
	$monthly_amount = wc_price( $monthly_amount ) . __( '/month', 'leasecloud-for-woocommerce' );

	// If we are on the checkout page and leasecloud is not the selected payment gateway, return normal price.
	if ( is_checkout() && 'leasecloud' !== WC()->session->__get( 'leasecloud_selected_gateway' ) ) {
		return wc_price( $price );
	}
	return '<span class="leasecloud-formated-price">' . $monthly_amount . '</span>';
}
