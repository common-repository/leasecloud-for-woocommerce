<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * LeaseCloud_Helper_Create_Order_Data class.
 *
 * Creates order_data used to create order with LeaseCloud
 */
class LeaseCloud_Helper_Create_Order_Data {
	/**
	 * WooCommerce order ID.
	 *
	 * @var int $order_id
	 */
	private $order_id;
	/**
	 * From WooCommerce checkout form
	 *
	 * @var array $posted_data
	 */
	private $posted_data;
	/**
	 * WooCommerce order object.
	 *
	 * @var array $order
	 */
	private $order;

	/**
	 * LeaseCloud_Helper_Create_Order_Data constructor.
	 *
	 * @param int   $order_id WooCommerce order ID.
	 * @param array $posted_data from WooCommerce checkout form.
	 * @param array $order WooCommerce order object.
	 */
	public function __construct( $order_id, $posted_data, $order ) {
		$this->order_id     = $order_id;
		$this->posted_data  = $posted_data;
		$this->order        = $order;
	}

	/**
	 * Creates the order_data array
	 *
	 * @return array
	 */
	public function create_order_data() {
		$vat = intval( round( $this->order->get_total_tax(), 2 ) * 100 );
		$shipping_amount = intval( round( $this->order->get_shipping_total(), 2 ) * 100 );
		$shipping_vat = intval( round( $this->order->get_shipping_tax(), 2 ) * 100 );
		$order_total = intval( round( ( $this->order->get_total( 'leasecloud' ) - $this->order->get_total_tax( 'leasecloud' ) ), 2 ) * 100 );
		$total_amount = $order_total;

		$order_data = array(
			'orderId' => (string) $this->order_id,
			'firstName' => $this->posted_data['billing_first_name'],
			'lastName' => $this->posted_data['billing_last_name'],
			'company' => $this->posted_data['billing_company'],
			'orgNumber' => $this->posted_data['leasecloud_org_nr'],
			'email' => $this->posted_data['billing_email'],
			'phone' => $this->posted_data['billing_phone'],
			'customerIp' => get_post_meta( $this->order_id, '_customer_ip_address', true ),
			'totalAmount' => $total_amount,
			'VAT' => $vat, // Total vat amount.
			'shippingAmount' => $shipping_amount,
			'shippingVAT' => $shipping_vat,
			'currency' => $this->order->get_currency(),
			'months' => $this->posted_data['leasecloud-payment-length'],
			'tariff' => $this->get_tariff_from_months( $this->posted_data['leasecloud-payment-length'] ),
			'billing' => $this->create_billing_object(),
			'items' => $this->create_items_object(),
		);
		if ( '' !== $this->posted_data['order_comments'] ) {
			$order_data['customerMessage'] = $this->posted_data['order_comments'];
		}
		if ( array_key_exists('ship_to_different_address', $this->posted_data) && $this->posted_data['ship_to_different_address'] == 1 ) {
			$order_data['shipping'] = $this->create_shipping_object();
		}
		if ( array_key_exists( 'leasecloud_signatory', $this->posted_data ) && 'on' === $this->posted_data['leasecloud_signatory'] ) {
			$order_data['authorizedSignatory'] = $this->posted_data['leasecloud_signatory_email'];
		}
		LeaseCloud_For_Woocommerce::log( 'Order Data: ' . var_export( $order_data, true ) );
		return $order_data;
	}

	/**
	 * Creates billing_object
	 *
	 * @return array
	 */
	private function create_billing_object() {
		$billing_object = array(
			'address' => $this->posted_data['billing_address_1'],
			'city' => $this->posted_data['billing_city'],
			'postalCode' => $this->posted_data['billing_postcode'],
			'country' => $this->posted_data['billing_country'],
		);
		if ( '' !== $this->posted_data['billing_address_2'] ) {
			$billing_object['address2'] = $this->posted_data['billing_address_2'];
		}
		return $billing_object;
	}

	/**
	 * Creates shipping_object if needed.
	 *
	 * @return array
	 */
	private function create_shipping_object() {
		$shipping_object = array(
			'firstName' => $this->posted_data['shipping_first_name'],
			'lastName' => $this->posted_data['shipping_last_name'],
			'company' => $this->posted_data['shipping_company'],
			'address' => $this->posted_data['shipping_address_1'],
			'city' => $this->posted_data['shipping_city'],
			'postalCode' => $this->posted_data['shipping_postcode'],
			'country' => $this->posted_data['shipping_country'],
		);
		if ( '' !== $this->posted_data['shipping_address_2'] ) {
			$shipping_object['address2'] = $this->posted_data['shipping_address_2'];
		}
		return $shipping_object;
	}

	/**
	 * Creates item object
	 *
	 * @return array
	 */
	private function create_items_object() {
		$items = array();
		foreach ( $this->order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );
			$variations = [];
			if ( $product->is_type( 'variable' ) ) {
				$item_id = $item['variation_id'];
				$product = new WC_Product_Variation( $item_id );
				$attributes = $product->get_variation_attributes();
				foreach ( $attributes as $k => $v ) {
					$attribute = str_replace( 'attribute_', '', $k );
					$value = str_replace( 'attribute_', '', $v );
					$variations[] = ucfirst( $attribute ) . ':' . $value;
				}
			}
			$total_excluded_vat = wc_get_price_excluding_tax( $product, [ 'qty' => $item->get_quantity() ] );
			$total_amount = intval( round( $total_excluded_vat, 2 ) * 100 );
			$unit_amount = intval( round( ( $total_excluded_vat / $item->get_quantity() ), 2 ) * 100 );
			$vat = intval( round( $item->get_total_tax(), 2 ) * 100 );
			$product_basic_name = wc_get_product( $item['product_id'] )->get_name();

			$temp_item = array(
				'name' => $product_basic_name,
				'attributes' => $variations,
				'productId' => $item->get_product_id(),
				'quantity' => $item->get_quantity(),
				'unitAmount' => $unit_amount,
				'totalAmount' => $total_amount,
				'VAT' => $vat,
				'sku' => $this->get_sku( $product ),
			);
			array_push( $items, $temp_item );
		}
		return $items;
	}

	/**
	 * Gets the tariffs for the order
	 *
	 * @param string $months Nr of months for the contract.
	 *
	 * @return float|null
	 */
	private function get_tariff_from_months( $months ) {
		$tariffs = get_option( 'wc_leasecloud_tariffs' );
		$tariff = LeaseCloud\Tariff::tariff( $months, $tariffs );
		return $tariff;
	}

	/**
	 * Gets the SKU for a product
	 *
	 * @param array $product the product object.
	 *
	 * @return bool|string
	 */
	public static function get_sku( $product ) {
		if ( $product->get_sku() ) {
			$part_number = $product->get_sku();
		} else {
			$part_number = $product->get_id();
		}

		return substr( $part_number, 0, 32 );
	}
}
