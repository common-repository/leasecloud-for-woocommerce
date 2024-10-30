<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Register the Widget
 */
function register_select_payment_length_widget() {
	register_widget( 'LeaseCloud_Select_Payment_Length' );
}
add_action( 'widgets_init', 'register_select_payment_length_widget' );

/**
 * Class LeaseCloud_Select_Payment_Length
 */
class LeaseCloud_Select_Payment_Length extends WP_Widget {
	/**
	 * LeaseCloud_Select_Payment_Length constructor.
	 *
	 * @extends WP_Widget
	 */
	public function __construct() {
		$widget_options = array(
			'classname' => 'LeaseCloud_Select_Payment_Length',
			'description' => __( 'Let the customer decide what payment length they want to be used to display the prices', 'leasecloud_for_woocommerce' ),
		);

		parent::__construct( 'leasecloud_select_payment_length', __( 'Select payment length', 'leasecloud_for_woocommerce' ), $widget_options );
	}

	/**
	 * Main function for the Widget
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
		echo '<label for="selected_payment_length">' . __( 'Payment length', 'leasecloud-for-woocommerce' ) . ':</label>';
		echo '<select name="selected_payment_length" id="selected_payment_length">';

		$tariffs = get_option( 'wc_leasecloud_tariffs' );
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
		$default_tariff = $leasecloud_settings['leasecloud_default_tariff'];
		$display_tariff = false;
		if( isset( $_COOKIE['leasecloud_selected_lenght'] ) ) {
			$display_tariff = $_COOKIE['leasecloud_selected_lenght'];
		}
		foreach ( $tariffs as $key => $value ) {
			if ( isset( $_COOKIE['leasecloud_display_tariff'] ) && $_COOKIE['leasecloud_display_tariff'] == $value->months ) {
				$selected = 'selected="selected"';
			} elseif ( ! isset( $_COOKIE['leasecloud_display_tariff'] ) && $default_tariff == $value->months ) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo '<option ' . $selected . ' value="' . $value->months . '">' . $value->months . " " . __( 'months', 'leasecloud-for-woocommerce' ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Creates the form for the Widget.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		echo '<p><label for="' . $this->get_field_id( 'title' ) . '">Title: </label>';
		echo '<input type="text" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" value="' . esc_attr( $title ) . '" /></p>';
	}
}
