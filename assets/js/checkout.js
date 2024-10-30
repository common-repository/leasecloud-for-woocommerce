(function ($) {
    // Update checkout on tariff choice selection.
    var old_selected = '';
    $(document).on('change', 'input[name="leasecloud-payment-length"]', function() {
        if( typeof $('input[name="leasecloud-payment-length"]:checked').val() !== "undefined" ) {
            if ( old_selected !== $('input[name="leasecloud-payment-length"]:checked').val()) {
                var selected_length = $('input[name="leasecloud-payment-length"]:checked').val();
                var data = {
                    'action': 'update_display_price',
                    'selected_length': selected_length
                };
                jQuery.post(wc_leasecloud.ajaxurl, data, function (data) {
                    if (true === data.success) {
                        jQuery(document.body).trigger("update_checkout");
                        old_selected = selected_length;
                    }
                });
            }
        }
    });

    // Update checkout on selecting leasecloud with own calculations.
    var selected_gateway = $('form[name="checkout"] input[name="payment_method"]:checked').val();
    $(document).on("change", "input[name='payment_method']", function (event) {
        if (selected_gateway !== $('form[name="checkout"] input[name="payment_method"]:checked').val()) {
            selected_gateway = $('form[name="checkout"] input[name="payment_method"]:checked').val();
            var data = {
                'action': 'update_selected_gateway',
                'selected_gateway': selected_gateway,
            };
            jQuery.post(wc_leasecloud.ajaxurl, data, function (data) {
                if (true === data.success) {
                    jQuery(document.body).trigger("update_checkout");
                }
            });
        }
    });

    // Update the total taxes field.
    $(document).on('updated_checkout', function () {
        var total_tax = '';
        var itemized = true;
        if( $('tr.tax-total td').length > 0 ) {
            total_tax = $('tr.tax-total td').html();
            itemized = false;
        } else if ( $('tr.tax-rate-tax-1 td').length > 0 ) {
            total_tax = $('tr.tax-rate-tax-1 td').html();
        }
        var data = {
            'action': 'update_total_tax',
            'total_tax': total_tax,
        };
        jQuery.post(wc_leasecloud.ajaxurl, data,    function (data) {
            if (true === data.success) {
                if( itemized == false ) {
                    $('tr.tax-total td').html(data.data.total_tax);
                } else {
                    $('tr.tax-rate-tax-1 td').html(data.data.total_tax);
                }
            }
        });
    });
}(jQuery));
