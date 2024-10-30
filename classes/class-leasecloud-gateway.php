<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Gateway
 */
class LeaseCloud_Gateway extends WC_Payment_Gateway {
	/**
	 * LeaseCloud_Gateway constructor.
	 */
	public function __construct() {
		$this->set_title();
		$this->set_icon();
		$this->id                 = 'leasecloud';
		$this->method_title       = __( 'LeaseCloud', 'leasecloud-for-woocommerce' );
		$this->method_description = __( 'LeaseCloud payment solution for WooCommerce.', 'leasecloud-for-woocommerce' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		// Load the form fields.
		$this->init_form_fields();
		// Load the settings.
		$this->init_settings();
		$this->supports = array('products');
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		// Add action for listener.
		add_action( 'woocommerce_api_leasecloud_gateway', array( $this, 'leasecloud_listener' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}
	/**
	 * Initiates the form fields
	 */
	public function init_form_fields() {
		// Load form fields from include file.
		$this->form_fields = include( LEASECLOUD_PLUGIN_DIR . '/includes/leasecloud-settings.php' );
	}

	public function admin_options() { ?>
		<h3><?php _e( 'LeaseCloud', 'leasecloud-for-woocommerce' ); ?></h3>
		<div class="leasecloud-settings">
			<div class="leasecloud-settings-content">
				<table class="form-table">
					<?php
					$this->generate_settings_html();
					?>
				</table>
			</div>
			<div class="leasecloud-settings-sidebar">
				<?php
				// If there are no tariffs saved
				if ( false === get_option( 'wc_leasecloud_tariffs' ) ) {
					?>
					<h4><?php _e( 'You have not yet gotten your tariffs. Please fill in an API key and get them now.', 'leasecloud-for-woocommerce' ) ?></h4>
					<div class="button-primary" id="leasecloud-get-tariffs"><?php _e( 'Get tariffs', 'leasecloud-for-woocommerce' ); ?></div>
					<?php
				} else {
					$finance_company = get_option( 'wc_leasecloud_finance' );
					?>
					<h4><?php _e( 'Finance company', 'leasecloud-for-woocommerce' ) . ': '; echo $finance_company->name; ?></h4>
					<h4><?php _e( 'Available LeaseCloud Tariff lengths', 'leasecloud-for-woocommerce' ); ?></h4>
					<table>
						<th><?php _e( 'Months', 'leasecloud-for-woocommerce' ); ?></th>
						<th><?php _e( 'Tariffs', 'leasecloud-for-woocommerce' ); ?></th>
						<?php
						foreach ( get_option( 'wc_leasecloud_tariffs' ) as $key => $value ) {
							echo '<tr><td>' . $value->months . '</td><td>' . $value->tariff . '</td></tr>';
						}
						?>
					</table>
					<div class="button-primary" id="leasecloud-get-tariffs"><?php _e( 'Update tariffs', 'leasecloud-for-woocommerce' ); ?></div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Checks to see if the gateway is available.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			// Check if API Key is filled in.
			if ( ! is_admin() ) {
				$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
				$leasecloud_api = $leasecloud_settings['leasecloud_api_key'];

				if ( '' === $leasecloud_api ) {
					LeaseCloud_For_Woocommerce::log( 'Gateway not available, no API key found in settings' );
					return false;
				}
			}

			// Currency check.
			if ( 'SEK' !== get_woocommerce_currency() ) {
				LeaseCloud_For_Woocommerce::log( 'Gateway not available, currency not SEK. Currency: ' . get_woocommerce_currency() );
				return false;
			}

			// Check order total, minimum 6000 SEK.
			if ( 6000 > $this->get_order_total_ex_tax() ) {
				return false;
			}

			// Check if cart has at least one leasable product.
			$cart = WC()->cart->get_cart();
			$count = 0;
			$product_type = new LeaseCloud_Product_Type();
			foreach ( $cart as $cart_item ) {
				if ( key_exists( 'product_id', $cart_item ) ) {
					$product_id = $cart_item['product_id'];
					if ( $product_type->is_leasable( $product_id ) ) {
						$count ++;
						break;
					}
				}
			}
			if ( 0 === $count ) {
				LeaseCloud_For_Woocommerce::log( 'Gateway not available, no leasable products in cart.' );
				return false;
			}
			return true;
		}
		LeaseCloud_For_Woocommerce::log( 'Gateway not available, not enabled in settings' );
		return false;
	}

	/**
	 * Get the order total in checkout and pay_for_order.
	 *
	 * @return float
	 */
	protected function get_order_total_ex_tax() {
		$total = 0;
		$order_id = absint( get_query_var( 'order-pay' ) );

		// Gets order total from "pay for order" page.
		if ( 0 < $order_id ) {
			$order = wc_get_order( $order_id );
			$total = (float) ($order->get_total('lc') - $order->get_total_tax('lc'));
		// Gets order total from cart/checkout.
		} elseif ( 0 < WC()->cart->total ) {
			$total = (float) (WC()->cart->get_total('lc') - WC()->cart->get_total_tax('lc'));
		}

		return $total;
	}

	/**
	 * Adds input fields to the Payment Fields
	 */
	public function payment_fields() {
		parent::payment_fields();
		// Print the Org Nr. input field.
		$org_nr_label = __( 'Organisation Number', 'leasecloud-for-woocommerce' );
		echo '<label class="leasecloud_label" for="leasecloud_org_nr">' . $org_nr_label . '</label>';
		echo '<input type="text" name="leasecloud_org_nr" id="leasecloud_org_nr" placeholder="XXXXXX-XXXX"/>';
		// Get the leasing alternatives.
		$get_checkout_tarrifs = new LeaseCloud_Calculate_Tariffs();
		echo $get_checkout_tarrifs->get_checkout_page_tariffs();
		// Checkbox for additional email input.
		$signatory_label = __( 'I do not have the authority to sign for the firm', 'leasecloud-for-woocommerce' );
		echo '<input type="checkbox" name="leasecloud_signatory" id="leasecloud_signatory"/>';
		echo '<label class="leasecloud_label" for="leasecloud_signatory">' . $signatory_label . '</label>';
		echo '<p id="leasecloud_signatory_text">' . __( 'Enter the email address we can use to send the leasing contract for the order.', 'leasecloud-for-woocommerce' ) . '</p>';
		echo '<input placeholder="Email" type="email" id="leasecloud_signatory_email" name="leasecloud_signatory_email">';
	}

	/**
	 * Processes the payment
	 *
	 * @param int  $order_id WooCommerce order id.
	 * @param bool $retry Should the order retry if failed.
	 *
	 * @return array
	 */
	public function process_payment( $order_id, $retry = false ) {
		$order = wc_get_order( $order_id );
		$set_api = new Request_LeaseCloud_Set_API();
		$set_api->set_api_key();
		$order_data_helper = new LeaseCloud_Helper_Create_Order_Data( $order_id, $_POST, $order );
		$order_data = $order_data_helper->create_order_data();
		$create_order = LeaseCloud\Order::create( $order_data );
		// Error handling.
		$error = false;
		if ( ! array_key_exists( 'monthlyAmount', $create_order ) ) {
			if ( isset( $create_order->error ) ) {
				foreach ( $create_order->error->fields as $key => $value ) {
					$order->add_order_note( 'LeaseCloud validation error <br/>field: ' . $value->field . '<br/>message: ' . $value->message );
					if ( 'company' === $value->field ) {
						wc_add_notice( __( 'Company name required', 'leasecloud-for-woocommerce' ), 'error' );
					} else {
						wc_add_notice( __( $value->message, 'leasecloud-for-woocommerce' ), 'error' );
					}
				}
			}
			$error = true;
			LeaseCloud_For_Woocommerce::log( 'Error creating order with LeaseCloud: ' . var_export( $create_order, true ) );
		}
		if ( false === $error ) {
			// Save the monthly payments as order meta.
			update_post_meta( $order_id, '_leasecloud_monthly_payment', $create_order->monthlyAmount );
			// Save the payment length as order meta.
			update_post_meta( $order_id, '_leasecloud_payment_length', $order_data['months'] );
			// Save the payment tariff as order meta.
			update_post_meta( $order_id, '_leasecloud_payment_tariff', $order_data['tariff'] );
			// Save the payment orgNumber as order meta.
			update_post_meta( $order_id, '_leasecloud_org_number', $order_data['orgNumber'] );
			// Save the payment authorizedSignatory as order meta.
			if ( isset( $order_data['authorizedSignatory'] ) ) {
				update_post_meta( $order_id, '_leasecloud_authorized_signatory', $order_data['authorizedSignatory'] );
			}
			// Set order status to on-hold, and await call from montly.
			$order->update_status( 'on-hold' );
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * Set the title for the checkout
	 */
	public function set_title() {
		$finance = get_option( 'wc_leasecloud_finance' );
		$this->title = $finance->name;
	}

	/**
	 * Set the icon for the checkout
	 */
	public function set_icon() {
		$finance = get_option( 'wc_leasecloud_finance' );
		$this->icon = $finance->logo;
	}

	/**
	 * Listener for webhooks from LeaseCloud.
	 */
	public function leasecloud_listener() {
		$validate_payload = new LeaseCloud_Webhooks();
		$validate_payload->validate_payload();
	}

	/**
	 * Loads admin scripts
	 */
	public function admin_scripts() {
		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		$leasecloud_only_leasable = isset($leasecloud_settings['leasecloud_only_leasing']) ? $leasecloud_settings['leasecloud_only_leasing'] : 'no';
		wp_register_script( 'admin', plugins_url( '../assets/js/admin.js', __FILE__ ), array( 'jquery' ), LEASECLOUD_VERSION_NUMBER );
		wp_localize_script( 'admin', 'wc_leasecloud', array(
			'lease_only' => $leasecloud_only_leasable,
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
		) );
		wp_enqueue_script( 'admin' );
	}
}
