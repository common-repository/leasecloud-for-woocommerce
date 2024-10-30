<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Get_Tariffs
 */
class LeaseCloud_Get_Tariffs {
	/**
	 * LeaseCloud_Get_Tariffs constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_tariffs', array( $this, 'get_tariffs' ) );
		add_action( 'wp_ajax_nopriv_get_tariffs', array( $this, 'get_tariffs' ) );
	}

	/**
	 * Gets the tariffs on button click.
	 */
	public function get_tariffs() {
		// Check if plugin is enabled and the saved settings was for LeaseCloud.
		$this->api_key = $_POST['api_key'];
		if ( '' === $this->api_key ) {
			return;
		}
		// Get the tariffs.
		$request = new Request_LeaseCloud_Get_Tarrifs_And_Finance();
		$response = $request->get_tarffis_and_finance( $this->api_key );

		if ( key_exists( 'tariffs', $response ) ) {
			$tariffs = $response->tariffs;
			$finance = $response->financeCompany;
			update_option( 'wc_leasecloud_tariffs', $tariffs );
			update_option( 'wc_leasecloud_finance', $finance );

			// Check if the setting for default tariffs is set, if not set it to the first value returned.
			$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
			if ( false === $leasecloud_settings['leasecloud_default_tariff'] ) {
				$leasecloud_settings['leasecloud_default_tariff'] = $tariffs[0]->months;
				update_option( 'woocommerce_leasecloud_settings', $leasecloud_settings );
			}
			LeaseCloud_For_Woocommerce::log( 'Tariffs updated: ' . var_export( get_option( 'wc_leasecloud_tariffs' ), true ) );
			wp_send_json_success();
			wp_die();
		} else {
			$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
			$leasecloud_settings['enabled'] = 'no';
			update_option( 'woocommerce_leasecloud_settings', $leasecloud_settings );
			$errors = array();
			foreach ( $response as $error ) {
				array_push( $errors, $error->message );
			}
			LeaseCloud_For_Woocommerce::log( 'Tariffs failed to update: ' . var_export( $response, true ) );
			wp_send_json_error( $errors );
			wp_die();
		}
	}
}
new LeaseCloud_Get_Tariffs();
