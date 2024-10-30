<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Request_LeaseCloud_Cancel_Order
 *
 * Cancels an order with LeaseCloud
 */
class Request_LeaseCloud_Cancel_Order {
	/**
	 * Makes the API call that cancels the order
	 *
	 * @param int $order_id WooCommerce order_id.
	 *
	 * @return object
	 */
	public function cancel_order( $order_id ) {
		// Set the API Key.
		$set_api = new Request_LeaseCloud_Set_API();
		$set_api->set_api_key();

		$response = LeaseCloud\Order::cancel( $order_id );

		return $response;
	}
}
