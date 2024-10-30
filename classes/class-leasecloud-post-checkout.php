<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Post_Checkout
 */
class LeaseCloud_Post_Checkout {
	/**
	 * LeaseCloud_Post_Checkout constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'leasecloud_order_completed' ) );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'leasecloud_order_cancel' ) );
	}

	/**
	 * Adds order note to order when completed and tells LeaseCloud that the order has been shipped.
	 *
	 * @param int $order_id Woocommerce order id.
	 */
	public function leasecloud_order_completed( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( 'leasecloud' === $order->get_payment_method() ) {
			if ( get_post_meta( $order_id, '_leasecloud_order_activated', true ) ) {
				$order->add_order_note( __( 'Could not activate the order with LeaseCloud, it has already been activated', 'leasecloud-for-woocommerce' ) );
				return;
			}

			$request  = new Request_LeaseCloud_Order_Shipped();
			$response = $request->order_shipped( $order_id );

			if ( 204 === $response->code ) {
				$order->add_order_note( __( 'The order has been shipped and activated with LeaseCloud', 'leasecloud-for-woocommerce' ) );
				update_post_meta( $order_id, '_leasecloud_order_activated', time() );
			} else {
				$order->add_order_note( sprintf( __( 'There was a problem with activating the order with LeaseCloud: %s', 'leasecloud-for-woocommerce' ), $response->status ) );
				LeaseCloud_For_Woocommerce::log( 'Error activating order: ' . var_export( $response, true ) );
				$order->update_status( $order->get_status() );
			}
		}
	}

	/**
	 * Cancels the order with LeaseCloud and adds an order note.
	 *
	 * @param int $order_id WooCommerce order id.
	 */
	public function leasecloud_order_cancel( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( 'leasecloud' === $order->get_payment_method() && metadata_exists( 'post', $order_id, '_leasecloud_org_number' ) ) {
			if ( get_post_meta( $order_id, '_leasecloud_order_canceled', true ) ) {
				$order->add_order_note( __( 'Could not cancel the order with LeaseCloud, the order has already been canceled.', 'leasecloud-for-woocommerce' ) );

				return;
			}

			$request  = new Request_LeaseCloud_Cancel_Order();
			$response = $request->cancel_order( $order_id );

			if ( 200 === $response->code ) {
				$order->add_order_note( __( 'The order has been canceled with LeaseCloud', 'leasecloud-for-woocommerce' ) );
				update_post_meta( $order_id, '_leasecloud_order_canceled', time() );
			} else {
				$order->add_order_note( sprintf( __( 'There was a problem with canceling the order with LeaseCloud: %s', 'leasecloud-for-woocommerce' ), $response->status ) );
				LeaseCloud_For_Woocommerce::log( 'Error canceling order: ' . var_export( $response, true ) );
			}
		}
	}
}
new LeaseCloud_Post_Checkout();
