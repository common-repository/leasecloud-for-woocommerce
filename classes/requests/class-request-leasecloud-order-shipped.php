<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Request_LeaseCloud_Order_Shipped
 *
 * Sends call to LeaseCloud saying the order is being shipped
 */
class Request_LeaseCloud_Order_Shipped {
	/**
	 * Makes the API call to LeaseCloud.
	 *
	 * @param int $order_id WooCommerce order id.
	 *
	 * @return object
	 */
	public function order_shipped( $order_id ) {
		// Set the API Key.
		$set_api = new Request_LeaseCloud_Set_API();
		$set_api->set_api_key();

		$response = LeaseCloud\Order::shipped( $order_id );

		return $response;
	}
}
