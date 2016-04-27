<div class="row" id="form-trpos">
    <?php if ($banks) { ?>
    <div class="col-sm-12">
        <div class="radio">
            <label>
                <input type="radio" name="instalment" value="<?php echo $trpos_other_id . '_0x' . $single_order_total; ?>" checked="checked"/>
                <?php echo $text_no_instalment . $trpos_single_title . $single_order_total; ?>
            </label>
        </div>
        <h3><?php echo $text_instalments; ?></h3>
    </div>
    <?php foreach ($banks as $bank) {
    if(!empty($bank['instalment']) || $bank['instalment']!=''){
    ?>
    <div class="col-sm-4">
        <?php if (!empty($bank['image'])) {?>
        <img src="<?php echo $bank['image']; ?>"/>
        <?php } else { ?>
        <strong><?php echo $bank['name']; ?></strong>
        <?php } ?>
        <?php foreach($bank['instalments'] as $instalment) { ?>
        <div class="radio">
            <label>
                <input type="radio" name="instalment" value="<?php echo $bank['bank_id'] . '_' . $instalment['count'] . 'x' . $instalment['price'] . '_' . $instalment['ratio']; ?>"/>
                <?php echo $instalment['count'] . $text_instalment . $instalment['total'] . '(' . $instalment['count'] . 'x' . $instalment['price'] . ')'; ?>
            </label>
        </div>
        <?php 	}
        } ?>
    </div>
    <?php } ?>
    <?php } ?>
    <input type="hidden" name="payment_method" value="trpos" checked="checked">
</div>
<div class="col-sm-12" id="form-trpos-confirm"></div>
<script type="text/javascript"><!--
    $(document).on('change', 'input[name=\'instalment\']:checked', function () {
        $.ajax({
            url     : 'index.php?route=payment/trpos/confirm',
            type    : 'post',
            data    : $('#form-trpos :input'),
            dataType: 'html',
            cache   : false,
            success : function (html) {
                $('#form-trpos-confirm').html(html);
            }
        });
    });

    $('input[name=\'instalment\']:checked').trigger("change");
//--></script>