(function ($) {
    $(document).on('change', 'select[name="selected_payment_length"]', function() {
        var selected_length = $('#selected_payment_length').val();
        var data = {
            'action': 'update_display_price',
            'selected_length': selected_length
        };
        $.ajax({
            type: 'POST',
            url: wc_leasecloud.ajaxurl,
            data: data,
            dataType: 'json',
            success: function(data) {
            },
            error: function(data) {
            },
            complete: function(data) {
                location.reload(true);
            }
        });
    });
}(jQuery));
