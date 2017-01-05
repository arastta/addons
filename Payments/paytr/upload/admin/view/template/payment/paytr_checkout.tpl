<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" onclick="save('save')" form="form-paytr" data-toggle="tooltip" title="" class="btn btn-success" data-original-title="Save"><i class="fa fa-check"></i></button>
                <button type="submit" form="form-paytr" data-toggle="tooltip" title="Save & Close" class="btn btn-default" data-original-title="Save & Close"><i class="fa fa-save text-success"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="İptal Et" class="btn btn-default"><i class="fa fa-reply"></i></a>
            </div>
            <h1>PayTR</h1>
        </div>
    </div>
    <div class="container-fluid">
        <?php if (isset($errors)) {
        foreach ($errors as $key => $val) {
        ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $errors_message[$key]; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php }
        } 
        ?>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-paytr" class="form-horizontal">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <strong>Entegrasyon API Bilgileriniz</strong> (Mağaza panelindeki BİLGİ sekmesinden görebilirsiniz)</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status"> Mağaza No</label>
                    <div class="col-sm-10">
                        <input type="text" name="paytr_checkout_merchant_id" value="<?php echo $paytr_checkout_merchant_id; ?>" class="form-control"/>
                        <?php if ( isset($errors['paytr_checkout_merchant_id']) OR !$paytr_checkout_merchant_id OR $paytr_checkout_merchant_id == null ) { ?>
                        <span class="text-danger"><?php echo $errors_message['paytr_checkout_merchant_id'] ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status"> Mağaza Parola</label>
                    <div class="col-sm-10">
                        <input type="text" name="paytr_checkout_merchant_key" value="<?php echo $paytr_checkout_merchant_key; ?>" class="form-control"/>
                        <?php if ( isset($errors['paytr_checkout_merchant_key']) OR !$paytr_checkout_merchant_key OR $paytr_checkout_merchant_key == null ) { ?>
                        <span class="text-danger"><?php echo $errors_message['paytr_checkout_merchant_key'] ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status"> Mağaza Gizli Anahtar</label>
                    <div class="col-sm-10">
                        <input type="text" name="paytr_checkout_merchant_salt" value="<?php echo $paytr_checkout_merchant_salt; ?>" class="form-control"/>
                        <?php if ( isset($errors['paytr_checkout_merchant_salt']) OR !$paytr_checkout_merchant_salt OR $paytr_checkout_merchant_salt == null ) { ?>
                        <span class="text-danger"><?php echo $errors_message['paytr_checkout_merchant_salt'] ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <strong>Sipariş Durumları</strong></h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status">Ödeme Onaylandığında</label>
                    <div class="col-sm-10">
                        <select name="paytr_checkout_order_completed_id" id="input-order-status-completed" class="form-control">
                            <?php if ( $paytr_checkout_order_completed_id == '' ) { echo "<option value='' selected>Lütfen seçiniz, zorunlu alandır.</option>"; } ?>
                            <?php foreach ($order_statuses as $order_status) { ?>
                            <?php if ( $order_status['order_status_id'] == $paytr_checkout_order_completed_id ) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                        <span class="help">Müşterinin ödemesi başarılı olarak tahsil edildiğinde siparişe hangi durum atansın?</span>
                        <?php if ( isset($errors['paytr_checkout_order_completed_id']) OR $paytr_checkout_order_completed_id == '' ) { ?>
                        <br/><span class="text-danger"><?php echo $errors_message['paytr_checkout_order_completed_id'] ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status">Ödeme Onay Almazsa</label>
                    <div class="col-sm-10">
                        <select name="paytr_checkout_order_canceled_id" id="input-order-status-canceled" class="form-control">
                            <?php if ( $paytr_checkout_order_canceled_id == '' ) { echo "<option value='' selected>Lütfen seçiniz, zorunlu alandır.</option>"; } ?>
                            <?php foreach ($order_statuses as $order_status) { ?>
                            <?php if ( $order_status['order_status_id'] == $paytr_checkout_order_canceled_id ) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                        <span class="help">Müşterinin ödemesi başarısız olduğunda siparişe hangi durum atansın?</span>
                        <?php if ( isset($errors['paytr_checkout_order_canceled_id']) OR $paytr_checkout_order_canceled_id == '' ) { ?>
                        <br/><span class="text-danger"><?php echo $errors_message['paytr_checkout_order_canceled_id'] ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <strong>Modül Ayarları</strong></h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status"> Modül Durumu</label>
                    <div class="col-sm-10">
                        <select name="paytr_checkout_status" class="form-control">
                            <?php if ($paytr_checkout_status == 0) { ?>
                            <option value="1">Aktif</option>
                            <option value="0" selected="selected">Kapalı</option>
                            <?php } else { ?>
                            <option value="1" selected="selected">Aktif</option>
                            <option value="0">Kapalı</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status"> Modül Dil Seçeneği</label>
                    <div class="col-sm-10">
                        <select name="paytr_checkout_lang" class="form-control">
                            <?php foreach ( $language_arr as $key => $val ) { ?>
                            <option value="<?php echo $key; ?>" <?php echo ( $paytr_checkout_lang == $key ? ' selected="selected"': null ); ?> ><?php echo $val; ?></option>
                            <?php } ?>
                            </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-order-status">Genel Maksimum Taksit Sayısı</label>
                    <div class="col-sm-10">
                        <select name="paytr_checkout_installment_number" id="input-order-status" class="form-control">
                            <?php
                             foreach ($installment_arr as $key => $val ) {
                             if ($key == $paytr_checkout_installment_number) {
                             ?>
                            <option value="<?php echo $key; ?>" selected="selected"><?php echo $val; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (!array_key_exists( 'paytr_checkout_installment_number', $errors ) AND $paytr_checkout_installment_number == 13) {
            unset($installment_arr[13]);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <strong>KATEGORİ BAZLI TAKSİT AYARLARI</strong></h3>
            </div>
            <div class="panel-body">
            <?php foreach( $paytr_checkout_category_list as $key => $val ) { ?>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="input-order-status"> <?php echo $val; ?></label>
                <div class="col-sm-10">
                    <select name="paytr_checkout_category_installment[<?php echo $key; ?>]" class="form-control">
                            <?php foreach ( $installment_arr as $installment_key => $installment_val ) {
                                    if ( $paytr_checkout_category_installment[ $key ] == $installment_key ) { $selected = 'selected'; } else { $selected = null; } ?>
                                    <option value="<?php echo $installment_key; ?>" <?php echo $selected; ?>><?php echo $installment_val; ?></option>
                                <?php } ?>
                        </select>
                </div>
            </div>
            <?php } ?>
        </div>
        </div>
        <?php } ?>
        </form>
    </div>
</div>
<script type="text/javascript"><!--
function save(type){
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'button';
    input.value = type;
    form = $("form[id^='form-']").append(input);
    form.submit();
}
//--></script>
<?php echo $footer; ?> 