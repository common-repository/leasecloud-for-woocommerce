<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'add_meta_boxes', 'leasecloud_meta_box' );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'leasecloud_add_org_nr' );

if ( ! function_exists( 'leasecloud_meta_box' ) ) {
	/**
	 * Add meta box to single order page.
	 *
	 * @param string $post_type Type of post.
	 */
	function leasecloud_meta_box( $post_type ) {
		if ( 'shop_order' === $post_type ) {
			$order = wc_get_order( get_the_ID() );
			if ( 'leasecloud' === $order->get_payment_method() ) {
				add_meta_box( 'leasecloud_meta_box', __( 'LeaseCloud order', 'leasecloud-for-woocommerce' ), 'leasecloud_meta_box_content', 'shop_order', 'side', 'core' );
			}
		}
	}
}

if ( ! function_exists( 'leasecloud_meta_box_content' ) ) {
	/**
	 * Adds content to the meta box.
	 */
	function leasecloud_meta_box_content() {
		// Get order information.
		$order_id = get_the_ID();
		$months   = get_post_meta( $order_id, '_leasecloud_payment_length', true );
		$tariff   = get_post_meta( $order_id, '_leasecloud_payment_tariff', true );

		// Get order status
		try {
			$request_status = new Request_LeaseCloud_Get_Order_Status();
			$status         = $request_status->get_order_status( $order_id );
			if ( isset( $status->errors ) ) {
				throw new \Error( $status->errors->message );
			}
		} catch ( Exception $e ) {
			LeaseCloud_For_Woocommerce::log( 'Fetch status error: ' . $e->getMessage() );
			$status = false;
		}

		// Display order information.
		echo '<p><strong>' . __( 'Months', 'leasecloud-for-woocomerce' ) . ": " . '</strong>' . $months . '<br><strong>' . __( 'Tariff', 'leasecloud-for-woocomerce' ) . ": " . '</strong>' . $tariff . '</p>';

		if ($status) {
			foreach ( $status->statuses as $key => $value ) {
				if ( null !== $value->setAt ) {
					echo '<p><strong>' . __( 'Status', 'leasecloud-for-woocomerce' ) . ": " . '</strong>' . $value->code . '<br>';
					if ( key_exists( 'title', $value ) && null !== $value->title ) {
						echo $value->title;
					} else {
						switch ( $value->code ) {
							case 'ACCEPTED':
								_e( 'LeaseCloud has accepted the order.', 'leasecloud-for-woocomerce' );
								break;
							case 'SIGNED':
								_e( 'The order has been signed by the customer.', 'leasecloud-for-woocomerce' );
								break;
							case 'SHIPPED':
								_e( 'The order has been shipped.', 'leasecloud-for-woocommerce' );
								break;
							case 'DELIVERY_APPROVED':
								_e( 'The order has been recieved by the customer.', 'leasecloud-for-woocommerce' );
								break;
							case 'DECLINED':
								_e( 'LeaseCloud has declined the order. The reason will be emailed to you soon. Sorry.', 'leasecloud-for-woocomerce' );
								break;
							case 'CANCELLED':
								_e( 'The order has been cancelled with LeaseCloud', 'leasecloud-for-woocommerce' );
								break;
						}
					}
					if ( null !== $value->message ) {
						echo '<br><small><strong>' . __( 'Message', 'leasecloud-for-woocommerce' ) . ":" . ' </strong>' . $value->message . '</small>';
					}
					echo '</p>';
				}
			}
		} else {
			echo '<p>' . __( 'Could not fetch statuses', 'leasecloud-for-woocomerce' ) . '</p>';
		}
		// Link to help chat.
		echo '<small>' . __( 'Have questions? visit', 'leasecloud-for-woocommerce' ) . " " . ' <a target="_blank" href="https://www.leasecloud.se/">' . __( 'LeaseCloud support', 'leasecloud-for-woocommerce' ) . '</a></small>';

		// Link to montly site
		echo '<br><small>' . __( 'Visit', 'leasecloud-for-woocommerce' ) . " " . ' <a href="https://www.leasecloud.se/">' . __( 'Leasecloud.se', 'leasecloud-for-woocommerce' ) . '</a></small>';
	}

	function leasecloud_add_org_nr( $order ) {
		if ( 'leasecloud' === $order->get_payment_method() ) {
			$order_id = $order->get_order_number();
			echo '<p><strong>' . __( 'Org Nr', 'leasecloud-for-woocommerce' ) . ':</strong> ' . get_post_meta( $order_id, '_leasecloud_org_number', true ) . '</p>';
		}
	}
}
