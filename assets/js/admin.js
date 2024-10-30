jQuery( function( $ ) {
    var wc_leasecloud_admin = {

        isLeaseOnly: function() {
            return $( '#woocommerce_leasecloud_leasecloud_only_leasing' ).is( ':checked' );
        },

        init: function () {
            $(document.body).on('change', '#woocommerce_leasecloud_leasecloud_only_leasing', function () {
                var display_price_shop = $('#woocommerce_leasecloud_leasecloud_display_price_shop').parents('tr').eq(0),
                    display_price_single = $('#woocommerce_leasecloud_leasecloud_display_price_single').parents('tr').eq(0),
                    default_lesable = $('#woocommerce_leasecloud_leasecloud_default_product_type').parents('tr').eq(0);
                if ($(this).is(':checked')) {
                    display_price_shop.hide();
                    display_price_single.hide();
                    default_lesable.hide();
                } else {
                    display_price_shop.show();
                    display_price_single.show();
                    default_lesable.show();
                }
            });
            $( '#woocommerce_leasecloud_leasecloud_only_leasing' ).change();
        },

        getTariffs: function () {
            var api_key = $( '#woocommerce_leasecloud_leasecloud_api_key' ).val();
            var data = {
                'action': 'get_tariffs',
                'api_key': api_key
            };
            jQuery.post( wc_leasecloud.ajaxurl, data, function (data) {
                if (true === data.success) {
                    location.reload();
                } else {
                    $('#screen-meta-links').after( '<div id="leasecloud-error" class="notice notice-error"></div>' );
                    for ( var i = 0, len = data.data.length; i < len; i++ ) {
                        var message = data.data[i];
                        $('#leasecloud-error').append( '<p>' + message + '</p>' );
                    }
                }
            });
        },

        maybeDisableButton: function() {
            if( $( '#woocommerce_leasecloud_leasecloud_api_key' ).val() === '' ) {
                $( '#leasecloud-get-tariffs' ).addClass( 'leasecloud-button-disabled' );
            } else {
                $( '#leasecloud-get-tariffs' ).removeClass( 'leasecloud-button-disabled' );
            }
        }
    };
    if( wc_leasecloud.lease_only === 'yes' ) {
        wc_leasecloud_admin.isLeaseOnly();
    }
    $( "#leasecloud-get-tariffs" ).click(function() {
        if( $( '#woocommerce_leasecloud_leasecloud_api_key' ).val() !== '' ) {
            wc_leasecloud_admin.getTariffs();
        }
    });
    $(document.body).on('change', '#woocommerce_leasecloud_leasecloud_api_key', function () {
        wc_leasecloud_admin.maybeDisableButton();
    });
    wc_leasecloud_admin.init();
    wc_leasecloud_admin.maybeDisableButton();
});