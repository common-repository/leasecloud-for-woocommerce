<?php
/**
 * LeaseCloud for WooCommerce
 *
 * @package WC_LeaseCloud
 *
 * @wordpress-plugin
 * Plugin Name:     LeaseCloud for WooCommerce
 * Plugin URI:      https://krokedil.se/produkt/leasecloud/
 * Description:     Extends WooCommerce. Provides a <a href="https://https://www.leasecloud.se/" target="_blank">LeaseCloud</a> checkout for WooCommerce.
 * Version:         1.1.9
 * Author:          Krokedil
 * Author URI:      https://krokedil.se/
 * Developer:       Krokedil
 * Developer URI:   https://krokedil.se/
 * Text Domain:     leasecloud-for-woocommerce
 * Domain Path:     /languages
 * Copyright:       © 2009-2017 LeaseCloud AB.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'LeaseCloud_For_Woocommerce' ) ) {
	/**
	 * Class LeaseCloud_For_Woocommerce
	 */
	class LeaseCloud_For_Woocommerce {
		/**
		 * Log message.
		 *
		 * @var string
		 */
		public static $log = '';

		/**
		 * LeaseCloud_For_Woocommerce constructor.
		 */
		public function __construct() {
			// Initiate the gateway.
			add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );

			// Load scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Enqueue admin stylesheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ) );
		}

		/**
		 * Sets definitions.
		 */
		public function define() {
			// Define plugin directory.
			define( 'LEASECLOUD_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			// Define version number.
			define( 'LEASECLOUD_VERSION_NUMBER', '1.1.9' );
			// Define LeaseCloud SDK.
			define( 'LEASECLOUD_SDK_DIR', '/vendor/' );
		}

		/**
		 * Initiates the plugin.
		 */
		public function init_plugin() {
			$this->define();
			$this->include_files();
			// Translations
			load_plugin_textdomain( 'leasecloud-for-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Loads the scripts.
		 */
		public function load_scripts() {
			// Enqueue scripts.
			wp_enqueue_script( 'jquery' );
			if ( is_checkout() ) {
				wp_register_script( 'signatory', plugins_url( '/assets/js/signatory-email.js', __FILE__ ), array( 'jquery' ), LEASECLOUD_VERSION_NUMBER );

				wp_localize_script(
					'signatory', 'wc_leasecloud', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
					)
				);
				wp_enqueue_script( 'signatory' );

				wp_register_script( 'leasecloud_checkout', plugins_url( '/assets/js/checkout.js', __FILE__ ), array( 'jquery' ), LEASECLOUD_VERSION_NUMBER );

				wp_localize_script(
					'leasecloud_checkout', 'wc_leasecloud', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
					)
				);
				wp_enqueue_script( 'leasecloud_checkout' );
			}
			wp_register_script( 'payment-length', plugins_url( '/assets/js/select-payment-length.js', __FILE__ ), array( 'jquery' ), LEASECLOUD_VERSION_NUMBER );

			wp_localize_script(
				'payment-length', 'wc_leasecloud', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
			wp_enqueue_script( 'payment-length' );
			// Load stylesheet for the checkout page.
			wp_register_style(
				'leasecloud',
				plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
				array(),
				LEASECLOUD_VERSION_NUMBER
			);
			wp_enqueue_style( 'leasecloud' );
		}

		public function enqueue_admin_css( $hook ) {
			if ( 'woocommerce_page_wc-settings' === $hook && isset( $_GET['section'] ) && 'leasecloud' === $_GET['section'] ) {
				wp_register_style( 'leasecloud-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', false );
				wp_enqueue_style( 'leasecloud-admin' );
			}
		}

		/**
		 * Includes the needed files.
		 */
		public function include_files() {
			// Include and add the Gateway.
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-gateway.php';
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_leasecloud_gateway' ) );
			}

			// Include classes.
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-get-tariffs.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-calculate-tariffs.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-get-wc-cart.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-post-checkout.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-webhooks.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-ajax.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/class-leasecloud-product-type.php';

			// Include Requests classes.
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/requests/class-request-leasecloud-get-tariff-and-finance.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/requests/class-request-leasecloud-set-api.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/requests/class-request-leasecloud-get-order-status.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/requests/class-request-leasecloud-order-shipped.php';
			include_once LEASECLOUD_PLUGIN_DIR . '/classes/requests/class-request-leasecloud-cancel-order.php';

			// Include the LeaseCloud SDK.
			require_once 'vendor/autoload.php';

			// Include function files.
			require_once LEASECLOUD_PLUGIN_DIR . '/includes/function-process-checkout-fields.php';
			require_once LEASECLOUD_PLUGIN_DIR . '/includes/function-change-price-display.php';
			require_once LEASECLOUD_PLUGIN_DIR . '/includes/function-single-order-page.php';
			require_once LEASECLOUD_PLUGIN_DIR . '/includes/function-add-to-order-details.php';
			require_once LEASECLOUD_PLUGIN_DIR . '/includes/function-add-product-list-column.php';

			// Include helper classes.
			require_once LEASECLOUD_PLUGIN_DIR . '/classes/helper/class-helper-leasecloud-create-order-data.php';

			// Include widget classes.
			require_once LEASECLOUD_PLUGIN_DIR . '/classes/widget/class-leasecloud-select-payment-length.php';
		}

		/**
		 * Adds the gateway to WooCommerce.
		 *
		 * @param array $methods The payment methods that exist.
		 *
		 * @return array
		 */
		public function add_leasecloud_gateway( $methods ) {
			$methods[] = 'LeaseCloud_Gateway';
			return $methods;
		}

		/**
		 * Debug logging.
		 *
		 * @param string $message The message to be logged.
		 */
		public static function log( $message ) {
			$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
			if ( 'yes' === $leasecloud_settings['debug_mode'] ) {
				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}
				self::$log->add( 'leasecloud', $message );
			}
		}
	}
	new LeaseCloud_For_Woocommerce();
}
