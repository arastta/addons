<div id="iyzico-loader"><img src="catalog/view/theme/default/image/payment/iyzico_checkout_form_spinner.gif" /></div>
<div class="iyzico_checkout_form_payment">
    <h2><?php echo $text_credit_card; ?></h2>
    <div class="iyzico-payment-form-wrapper" id="payment"></div>
    <div id="iyzipay-checkout-form" class="<?php echo $form_class; ?>"></div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $(".iyzico_checkout_form_payment").hide();
        $(".iyzico_checkout_form_confirm").hide();
        $.ajax({
            url: 'index.php?route=payment/iyzico_checkout_form/gettoken',
            type: 'post',
            data: $('#payment :input'),
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#button-confirm').button('loading');
            },
            complete: function() {
                $('#button-confirm').button('reset');
            },
            success: function(json) {
                $('#iyzico-loader').css('display','none');
                if (json.display_direct_confirm == "yes") {
                    $(".iyzico_checkout_form_confirm").show();
                } else if (json.display_direct_confirm == "no" && typeof json.checkout_form_content != "undefined" && json.checkout_form_content != "") {
                    $(".iyzico_checkout_form_payment").show();
                    $('.iyzico-payment-form-wrapper').append(json.checkout_form_content);
                } else {
                    $(".iyzico_checkout_form_payment").show();
                    $('.iyzico-payment-form-wrapper').append('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + json.error + '</div>');
                    $('#iyzico-loader').css('display','none');
                }
            }
        });

    });
</script>