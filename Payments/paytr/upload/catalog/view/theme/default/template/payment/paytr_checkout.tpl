<div id="paytr-loader">
    <img src="catalog/view/theme/default/image/payment/spinner.gif" />
</div>

<div class="paytr-payment-form-wrapper" id="payment"></div>
<script type="text/javascript">
    $.ajax({
        url: 'index.php?route=payment/paytr_checkout/gettoken',
        type: 'post',
        data: $('#payment :input'),
        dataType: 'json',
        cache: false,
        beforeSend: function() {
            $('#button-confirm').button('loading');
        },
        complete: function(json) {
            $('#paytr-loader').html(json.responseText);
            $('#button-confirm').button('reset');
        },
        success: function(json) {
            $('#paytr-loader').css('display','none');

            if (typeof json.access_token != "undefined" && json.access_token != "") {
                $('.paytr-payment-form-wrapper').append(json.access_token);
            } else {
                $('.paytr-payment-form-wrapper').append('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button> Hata: ' + json.error + '</div>');

                $('#paytr-loader').css('display','none');
            }
        }
    });
</script> 