<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Calculate_Tariffs
 */
class LeaseCloud_Calculate_Tariffs {
	/**
	 * Returns the tariff list for the checkout page.
	 *
	 * @return string
	 */
	public function get_checkout_page_tariffs() {
		$total = WC()->cart->get_total('not_view') - WC()->cart->get_total_tax();
		$tariffs = get_option( 'wc_leasecloud_tariffs' );
		$return = '<ul id="leasecloud-checkout-tariffs" style="margin: 0 0 1.41575em 0;">';
		foreach ( $tariffs as $key => $value ) {
			$cost = $this->calculate_tariff( $total, $value->months );
			$price_suffix_text = " " . __( 'for', 'leasecloud-for-woocommerce' ) . " " . $value->months . " " . __( 'months', 'leasecloud-for-woocommerce' );
			$price_suffix = apply_filters( 'leasecloud_monthly_checkout_price_suffix', $price_suffix_text, $value->months );
			$return = $return . '<li><label><input type="radio" name="leasecloud-payment-length" id="' . $value->months . '" value="' . $value->months . '" > '
				. wc_price( $cost ) . $price_suffix
			. '</label></li>';
		}
		$return = $return . '</ul>';
		return $return;
	}

	/**
	 * Gets the monthly cost based on the default tariff setting.
	 *
	 * @param int $price The price of the product/cart.
	 *
	 * @return int|null
	 */
	public function get_monthly_cost_from_default_tariff( $price ) {
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		if( isset( $leasecloud_settings['leasecloud_default_tariff'] ) ) {
			$tariff = $leasecloud_settings['leasecloud_default_tariff'];
		} else {
			$tariff = '36';
		}
		return $this->calculate_tariff( $price, $tariff );
	}

	/**
	 * Gets the monthly cost based on the selected tariff.
	 *
	 * @param int    $price The price of the product/cart.
	 * @param string $tariff The length of the contract.
	 *
	 * @return int|null
	 */
	public function get_monthly_cost_from_selected_tariff( $price, $tariff ) {
		$cost = $this->calculate_tariff( $price, $tariff );
		return $cost;
	}

	/**
	 * Calculates the tariff using the SDK
	 *
	 * @param int $value The total value of the product/cart.
	 * @param int $months The length of the contract.
	 *
	 * @return int|null
	 */
	private function calculate_tariff( $value, $months ) {
		$tariffs = get_option( 'wc_leasecloud_tariffs' );
		return LeaseCloud\Tariff::monthlyCost( $value, $months, $tariffs );
	}

	/**
	 * Gets the tariff from contract length.
	 *
	 * @param int $months Length of contract.
	 *
	 * @return float|null
	 */
    public static function months_tariff_exist( $months ) {
		$tariffs = get_option( 'wc_leasecloud_tariffs' );
		return LeaseCloud\Tariff::tariff( $months, $tariffs );
	}
}
