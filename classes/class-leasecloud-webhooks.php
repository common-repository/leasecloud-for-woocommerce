<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Webhooks
 */
class LeaseCloud_Webhooks {
	/**
	 * Validates the Payload from the Webhook using the SDK.
	 *
	 * @return bool
	 */
	public function validate_payload() {
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		$secret = $leasecloud_settings['leasecloud_webhook_key'];

		LeaseCloud\Webook::setSecret( $secret );
		$signature = $_SERVER['HTTP_LEASECLOUD_SIGNATURE'];
		$payload = file_get_contents( 'php://input' );
		LeaseCloud_For_Woocommerce::log( 'Webhook received: ' . var_export( $payload, true ) );
		$valid = LeaseCloud\Webook::validateSignature( $signature, $payload );

		if ( $valid ) {
			$json   = json_decode( $payload );
			$id     = $json->id;
			$action = $json->action;
			$data   = $json->data;
		} else {
			LeaseCloud_For_Woocommerce::log( 'Webhook has invalid payload: ' . $signature );
			header( 'HTTP/1.1 401 Unauthorized' );
			return false;
		}

		// Run function based on response.
		switch ( $action ) {
			case 'order.accepted':
				$this->order_accepted( $data );
				break;
			case 'order.declined':
				$this->order_declined( $data );
				break;
			case 'order.signed':
				$this->order_signed( $data );
				break;
			case 'order.deliveryApproved':
				$this->order_delivery_approved( $data );
				break;
			case 'tariff.updated':
				$this->tariff_updated( $data );
				break;
		}

		header( 'HTTP/1.1 200 OK' );
		header( 'Content-Type: application/json' );
		echo json_encode([ 'id' => $id ]);
		die();
	}

	/**
	 * Adds order note
	 *
	 * @param array $data Payload from LeaseCloud.
	 */
	public function order_accepted( $data ) {
		$order = wc_get_order( $data->orderId );
		$order->add_order_note( __( 'The order has been accepted by LeaseCloud', 'leasecloud-for-woocommerce' ) );
	}

	/**
	 * Adds order note
	 *
	 * @param array $data Payload from LeaseCloud.
	 */
	public function order_declined($data) {
		$order = wc_get_order( $data->orderId );
		$order->add_order_note( sprintf( __( 'The order has been rejected by LeaseCloud. Reason: %s', 'leasecloud-for-woocommerce' ), $data->reason ) );
		$order->update_status( apply_filters( 'leasecloud_order_declined_status', 'failed' ) );
	}

	/**
	 * Adds order note
	 *
	 * @param array $data Payload from LeaseCloud.
	 */
		public function order_signed( $data ) {
		$order = wc_get_order( $data->orderId );
		$order->add_order_note( __( 'The order has been signed by the customer and can now be processed', 'leasecloud-for-woocommerce' ) );
		$order->update_status( apply_filters( 'leasecloud_order_signed_status', 'processing' ) );
	}

	/**
	 * Adds order note
	 *
	 * @param array $data Payload from LeaseCloud.
	 */
	public function order_delivery_approved( $data ) {
		$order = wc_get_order( $data->orderId );
		$order->add_order_note( __( 'The order has been received by the customer.', 'leasecloud-for-woocommerce' ) );
	}

	/**
	 * Updates the Tariffs from the payload.
	 *
	 * @param array $data Payload from LeaseCloud.
	 */
	public function tariff_updated( $data ) {
		update_option( 'wc_leasecloud_tariffs', $data->tariffs );
	}
}
new LeaseCloud_Webhooks();
