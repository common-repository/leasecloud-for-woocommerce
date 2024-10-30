<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Request_LeaseCloud_Get_Order_Status
 *
 * Gets the order status
 */
class Request_LeaseCloud_Get_Order_Status {
	/**
	 * Makes the API call to get the LeaseCloud order status.
	 *
	 * @param int $order_id WooCommerce order id.
	 *
	 * @return mixed
	 */
	public function get_order_status( $order_id ) {
		// Set the API Key.
		$set_api = new Request_LeaseCloud_Set_API();
		$set_api->set_api_key();

		$status = LeaseCloud\Order::status( $order_id );

		return $status;
	}
}
