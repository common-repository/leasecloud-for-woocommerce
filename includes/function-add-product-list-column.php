<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


add_filter( 'manage_edit-product_columns', 'leasecloud_leasable_product_column', 15 );

function leasecloud_leasable_product_column( $columns ) {

	// Add column.
	$columns['leasable'] = __( 'Leaseable' , 'leasecloud-for-woocommerce' );

	return $columns;
}

add_action( 'manage_product_posts_custom_column', 'leasecloud_leasable_product_column_content', 10, 2 );

function leasecloud_leasable_product_column_content( $column, $product_id ) {
	if ( 'leasable' === $column ) {
		$leasecloud_product_type = new LeaseCloud_Product_Type();
		if ( $leasecloud_product_type->is_leasable( $product_id ) ) {
			echo '<span class="dashicons dashicons-yes"></span>';
		} else {
			echo '<span class="dashicons dashicons-no"></span>';
		}
	}
}
