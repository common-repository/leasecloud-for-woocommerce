<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Request_LeaseCloud_Set_API
 *
 * Sets the API Key
 */
class Request_LeaseCloud_Set_API {
	/**
	 * LeaseCloud API Key
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * LeaseCloud test mode setting
	 *
	 * @var string
	 */
	private $test_mode = '';

	/**
	 * Request_LeaseCloud_Set_API constructor.
	 */
	public function __construct() {
		// Get API Key from settings.
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		$api_key = $leasecloud_settings['leasecloud_api_key'];
		$this->api_key = $api_key;

		if ($leasecloud_settings['environment'] === 'test' ) {
			define('LEASECLOUD_TEST_ENDPOINT', 'https://api.staging.leasecloud.com');
		}
	}

	/**
	 * Set the API Key and endpoint using the SDK
	 */
	public function set_api_key( $api_key = '' ) {
		if ( '' !== $api_key ) {
			LeaseCloud\LeaseCloud::setApiKey( $api_key );
		} else {
			LeaseCloud\LeaseCloud::setApiKey( $this->api_key );
		}
		if ( defined( 'LEASECLOUD_TEST_ENDPOINT' ) ) {
			LeaseCloud\LeaseCloud::setApiBase( LEASECLOUD_TEST_ENDPOINT );
		}
	}
}
