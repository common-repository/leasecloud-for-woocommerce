(function ($) {
    $('#leasecloud_signatory_email').hide();
    $('#leasecloud_signatory_text').hide();
    $(document).on('change', 'input[name="leasecloud_signatory"]', function() {
        if( $('#leasecloud_signatory').is(':checked') ) {
            $('#leasecloud_signatory_email').show();
            $('#leasecloud_signatory_text').show();
        } else {
            if($('#leasecloud_signatory_email').length > 0) {
                $('#leasecloud_signatory_email').hide();
                $('#leasecloud_signatory_text').hide();
            }
        }
    });
}(jQuery));