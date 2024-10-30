<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LeaseCloud_Product_Type
 */
class LeaseCloud_Product_Type {
	/**
     * The ID of the setting.
     *
	 * @var string
	 */
    private $id;

	/**
     * The label for the setting.
     *
	 * @var string
	 */
    private $label;

	/**
     * The description for the setting.
     *
	 * @var string
	 */
    private $description;

	/**
     * The LeaseCloud settings array.
     *
	 * @var mixed|void
	 */
    private $leasecloud_settings;

	/**
	 * LeaseCloud_Product_Type constructor.
	 */
	public function __construct() {
		$this->leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );

		$this->set_id();
		$this->set_label();
		$this->set_description();

		add_filter( 'product_type_options', array( $this, 'add_leasable_product_type' ) );
		add_action( 'save_post_product', array( $this, 'save_custom_product_type' ), 10, 1 );
		add_action( 'woocommerce_variation_options', array( $this, 'add_leasable_variable_product_type' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_leasable_variable_product' ), 10, 2 );
	}

	/**
	 * Adds the Leasable product type.
	 *
	 * @param array $product_type_options The product_type_options array.
	 *
	 * @return array
	 */
	public function add_leasable_product_type( $product_type_options ) {
		$product_type_options[ substr( $this->id, 1 ) ] = array(
			'id'            => $this->id,
			'wrapper_class' => 'show_if_simple',
			'label'         => $this->label,
			'description'   => $this->description,
			'default'       => 'no',
		);
		return $product_type_options;
	}

	/**
	 * Saves the custom product type
	 *
	 * @param int $post_id The post id.
	 */
	public function save_custom_product_type( $post_id ) {
		$is_leasable_product = isset( $_POST[ $this->id ] ) ? 'yes' : 'no';
		update_post_meta( $post_id, $this->id, $is_leasable_product );
	}

	/**
	 * Checks if product is a leasable product.
	 *
	 * @param int $product_id The WooCommerce product id.
	 *
	 * @return bool
	 */
	public function is_leasable( $product_id ) {
		$leasecloud_settings = get_option( 'woocommerce_leasecloud_settings' );
	    if ( 'no' === $leasecloud_settings['leasecloud_default_product_type'] ) {
		    $leasable = get_post_meta( $product_id, '_leasecloud_leasable' );
		    if ( empty( $leasable ) ) {
			    return false;
		    } else {
			    if ( 'yes' === $leasable[0] ) {
				    return true;
			    } else {
				    return false;
			    }
		    }
	    } else {
		    $not_leasable = get_post_meta( $product_id, '_leasecloud_not_leasable' );
		    if ( empty( $not_leasable ) ) {
			    return true;
		    } else {
			    if ( 'yes' === $not_leasable[0] ) {
				    return false;
			    } else {
				    return true;

			    }
		    }
        }
	}

	public function add_leasable_variable_product_type( $loop, $variation_data, $variation ) {
		$checked = false;
		if ( ! empty( get_post_meta( $variation->ID, $this->id ) ) ) {
			if ( 'yes' === get_post_meta( $variation->ID, $this->id )[0] ) {
                $checked = true;
			}
		}
		?>
		<label class="tips" data-tip="<?php echo $this->description; ?>">
            <?php echo $this->label; ?>
			<input type="checkbox" class="leasecloud_variable_leaseable" name="<?php echo $this->id; ?>[<?php echo $loop; ?>]" <?php checked( $checked, true ); ?> />
		</label>
		<?php
	}

	public function save_leasable_variable_product( $variation_id, $i ) {
		$is_leasable_product = isset( $_POST[ $this->id ] ) ? 'yes' : 'no';
		update_post_meta( $variation_id, $this->id, $is_leasable_product );
	}

	private function set_id() {
	    $default = $this->leasecloud_settings['leasecloud_default_product_type'];
	    if ( 'no' === $default ) {
	        $this->id = '_leasecloud_leasable';
        } else {
		    $this->id = '_leasecloud_not_leasable';
	    }
	}

	private function set_label() {
		$default = $this->leasecloud_settings['leasecloud_default_product_type'];
		if ( 'no' === $default ) {
			$this->label = __( 'Leasable', 'leasecloud-for-woocommerce' );
		} else {
			$this->label = __( 'Not Leasable', 'leasecloud-for-woocommerce' );
		}
    }

	private function set_description() {
		$default = $this->leasecloud_settings['leasecloud_default_product_type'];
		if ( 'no' === $default ) {
			$this->description = __( 'Leasable product for LeaseCloud', 'leasecloud-for-woocommerce' );
		} else {
			$this->description = __( 'Not a leasable product for LeaseCloud', 'leasecloud-for-woocommerce' );
		}
    }
}
new LeaseCloud_Product_Type();
