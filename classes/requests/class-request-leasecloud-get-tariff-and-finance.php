<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Get_Finance_Company
 */
class Request_LeaseCloud_Get_Tarrifs_And_Finance {
	/**
	 * Request headers
	 *
	 * @var array
	 */
	private $headers;

	/**
	 * LeaseCloud_Get_Finance_Company constructor.
	 */
	public function __construct() {
		$this->headers = $this->set_headers();
	}

	/**
	 * Sets the request headers
	 */
	private function set_headers() {
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		$api_key = $leasecloud_settings['leasecloud_api_key'];

		return [
			'Authorization: Bearer ' . $api_key,
			'Content-Type: application/json',
		];
	}

	/**
	 * Gets the finance company.
	 */
	public function get_tarffis_and_finance( $api_key ) {
		// Set the API Key.
		$set_api = new Request_LeaseCloud_Set_API();
		$set_api->set_api_key( $api_key );
		$response = LeaseCloud\Config::retrieve();
		return $response;
	}
}
