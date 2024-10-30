<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Settings for LeaseCloud For WooCommerce
 */
$settings = array(
	'enabled' => array(
		'title'         => __( 'Enable/Disable', 'leasecloud-for-woocommerce' ),
		'type'          => 'checkbox',
		'label'         => __( 'Enable LeaseCloud', 'leasecloud-for-woocommerce' ),
		'default'       => 'no',
	),
	'description' => array(
		'title'         => __( 'Description', 'leasecloud-for-woocommerce' ),
		'type'          => 'text',
		'description'   => __( 'This controls the description which the user sees during checkout.', 'leasecloud-for-woocommerce' ),
		'default'       => __( 'Pay using LeaseCloud.', 'leasecloud-for-woocommerce' ),
		'desc_tip'      => true,
	),
	'leasecloud_api_key' => array(
		'title'         => __( 'LeaseCloud API Key', 'leasecloud-for-woocommerce' ),
		'type'          => 'text',
		'description'   => __( 'Enter your LeaseCloud API Key', 'leasecloud-for-woocommerce' ),
		'default'       => '',
		'desc_tip'      => true,
	),
	'leasecloud_webhook_key' => array(
		'title'         => __( 'LeaseCloud Webhook key', 'leasecloud-for-woocommerce' ),
		'type'          => 'text',
		'description'   => __( 'Enter your LeaseCloud Webhook key', 'leasecloud-for-woocommerce' ),
		'default'       => '',
		'desc_tip'      => true,
	),
	'leasecloud_only_leasing' => array(
		'title'         => __( 'Lease only shop', 'leasecloud-for-woocommerce' ),
		'type'          => 'checkbox',
		'label'         => __( 'Display all prices in the shop / cart and emails as leasing prices', 'leasecloud-for-woocommerce' ),
		'default'       => 'no',
	),
	'leasecloud_default_product_type' => array(
		'title'         => __( 'Default leasable product.', 'leasecloud-for-woocommerce' ),
		'type'          => 'checkbox',
		'label'         => __( 'Check this box if you want to default all products as leasable(can be overwriten on each product.)', 'leasecloud-for-woocommerce' ),
		'default'       => 'no',
	),
	'leasecloud_display_price_shop'  => array(
		'title'   => __( 'Shop page price display', 'leasecloud-for-woocommerce' ),
		'type'    => 'select',
		'label'   => __( 'Display price per month in the category and shop pages', 'leasecloud-for-woocommerce' ),
		'default' => 'no',
		'options' => array(
			'standard'  => __( 'Standard (product price)', 'leasecloud-for-woocommerce' ),
			'both'      => __( 'Both', 'leasecloud-for-woocommerce' ),
		),
	),
	'leasecloud_display_price_single'  => array(
		'title'   => __( 'Single product price display', 'leasecloud-for-woocommerce' ),
		'type'    => 'select',
		'label'   => __( 'Display price per month on the single product pages', 'leasecloud-for-woocommerce' ),
		'default' => 'no',
		'options' => array(
			'standard'  => __( 'Standard (product price)', 'leasecloud-for-woocommerce' ),
			'both'      => __( 'Both', 'leasecloud-for-woocommerce' ),
		),
	),
	'test_mode_settings_title' => array(
		'title' => __( 'Test Mode Settings', 'leasecloud-for-woocommerce' ),
		'type'  => 'title',
	),
	'debug_mode' => array(
		'title'         => __( 'Debug', 'leasecloud-for-woocommerce' ),
		'type'          => 'checkbox',
		'label'         => __( 'Enable logging.', 'leasecloud-for-woocommerce' ),
		'description'   => sprintf( __( 'Log LeaseCloud events, in <code>%s</code>', 'leasecloud-for-woocommerce' ), wc_get_log_file_path( 'leasecloud' ) ),
		'default'       => 'no',
	),
	'environment' => array(
		'title' => __( 'Environment', 'leasecloud-for-woocommerce' ),
		'description' => __( 'Option to make calls to test but requires different api keys', 'leasecloud-for-woocommerce' ),
		'type' => 'select',
		'default' => 'live',
		'options' => array(
			'live' => __( 'Live / Production', 'leasecloud-for-woocommerce' ),
			'test' => __( 'Test server', 'leasecloud-for-woocommerce' )
		)
	)
);

if ( false !== get_option( 'wc_leasecloud_tariffs' ) ) {
	$tariffs = get_option( 'wc_leasecloud_tariffs' );
	$default_tariff_setting = array(
		'title' => __( 'Default tariff', 'leasecloud-for-woocommerce' ),
		'description' => __( 'The default tariffs to use for the display of monthly costs', 'leasecloud-for-woocommerce' ),
		'type' => 'select',
		'default' => '36',
	);
	$options = array();
	foreach ( $tariffs as $key => $value ) {
		$options[ $value->months ] = $value->months;
	}
	$default_tariff_setting['options'] = $options;

	$settings['leasecloud_default_tariff'] = $default_tariff_setting;
}
return apply_filters( 'leasecloud_settings', $settings );

