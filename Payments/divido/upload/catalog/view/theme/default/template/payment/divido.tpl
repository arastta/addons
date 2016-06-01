<link href="catalog/view/theme/default/stylesheet/divido.css" rel="stylesheet" media="screen" />
<div id="divido-checkout" data-divido-calculator class="divido-calculator divido-theme-blue" data-divido-amount="<?php echo $grand_total; ?>" data-divido-plans="<?php echo $plan_list; ?>">
    <h1>
        <a href="https://www.divido.com" target="_blank" class="divido-logo divido-logo-sm" style="float:right;">Divido</a>
        <?php echo $text_checkout_title; ?>
    </h1>
    <div style="clear:both;"></div>
    <dl>
        <dt><span data-divido-choose-finance data-divido-label="<?php echo $text_choose_plan; ?>" data-divido-form="divido_finance"></span></dt>
        <dd><span class="divido-deposit" data-divido-choose-deposit data-divido-label="<?php echo $text_choose_deposit; ?>" data-divido-form="divido_deposit"></span></dd>
    </dl>
    <div class="description">
        <strong>
        <span data-divido-agreement-duration></span> <?php echo $text_monthly_payments; ?> <span data-divido-monthly-instalment></span>
        </strong>
    </div>
    <div class="divido-info">
        <dl>
            <dt><?php echo $text_term; ?></dt>
            <dd><span data-divido-agreement-duration></span> <?php echo $text_months; ?></dd>
            <dt><?php echo $text_monthly_installment; ?></dt>
            <dd><span data-divido-monthly-instalment></span></dd>
            <dt><?php echo $text_deposit; ?></dt>
            <dd><span data-divido-deposit></span></dd>
            <dt><?php echo $text_credit_amount; ?></dt>
            <dd><span data-divido-credit-amount-rounded></span></dd>
            <dt><?php echo $text_amount_payable; ?></dt>
            <dd><span data-divido-total-payable-rounded></span></dd>
            <dt><?php echo $text_total_interest; ?></dt>
            <dd><span data-divido-interest-rate></span></dd>
        </dl>
    </div>
    <div class="clear"></div>
    <p><?php echo $text_redirection; ?></p>
</div>

<div class="buttons">
    <div class="pull-right">
        <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
    </div>
</div>

<script>
(function($) {
    $(function () {
        $.getScript('<?php echo $merchant_script; ?>', function () {
            divido_calculator($('#divido-checkout'));
        });

        $('#button-confirm').on('click', function() {
            var finance_elem = $('select[name="divido_finance"]');
            var deposit      = $('[name="divido_deposit"]').val();

            var finance;

            if (finance_elem.length > 0) {
                finance = finance_elem.val();
            } else {
                finance = $('[data-divido-calculator]').data('divido-plans');
            }

            var data = {
                finance: finance,
                deposit: deposit
            };

            $.ajax({
                type     : 'post',
                url      : 'index.php?route=payment/divido/confirm',
                data     : data,
                dataType : 'json',
                cache    : false,
                beforeSend: function() {
                    $('#button-confirm').button('loading');
                },
                complete: function() {
                    $('#button-confirm').button('reset');
                },
                success: function(data) {
                    if (data.status == 'ok') {
                        location = data.url;
                    } else {
                        message = data.message || '<?php echo $generic_credit_req_error; ?>';
                        $('#divido-checkout').prepend('<div class="alert alert-warning">' + message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                }
            });
        });
    });
})(jQuery);
</script>
