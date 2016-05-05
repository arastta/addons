<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" onclick="save('save')" form="form-iyzico-checkout" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-success" data-original-title="Save">
                    <i class="fa fa-check"></i></button>
                <button type="submit" form="form-iyzico-checkout" data-toggle="tooltip" title="<?php echo $button_saveclose; ?>" class="btn btn-default" data-original-title="Save & Close">
                    <i class="fa fa-save text-success"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-times-circle text-danger"></i></a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <?php echo $text_iyzico_checkout_installment_info; ?>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-iyzico-checkout" class="form-horizontal">
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-order-status">  <?php echo $entry_api_id_live; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="iyzico_checkout_installment_api_id_live" value="<?php echo $iyzico_checkout_installment_api_id_live; ?>" class="form-control"/>
                            <?php if ($error_api_id_live) { ?>
                            <span class="text-danger"><?php echo $error_api_id_live; ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-order-status"> <?php echo $entry_secret_key_live; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="iyzico_checkout_installment_secret_key_live" value="<?php echo $iyzico_checkout_installment_secret_key_live; ?>" class="form-control"/>
                            <?php if ($error_secret_key_live) { ?>
                            <span class="text-danger"><?php echo $error_secret_key_live; ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-iyzico-checkout-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="iyzico_checkout_installment_status" id="input-iyzico-checkout-status" class="form-control">
                                <?php if ($iyzico_checkout_installment_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="iyzico-checkout-form-class"><?php echo $entry_class; ?></label>
                        <div class="col-sm-10">
                            <select name="iyzico_checkout_installment_form_class" id="iyzico-checkout-form-class" class="form-control">
                                <?php if ($iyzico_checkout_installment_form_class == "responsive") { ?>
                                <option value="popup"><?php echo $entry_class_popup; ?></option>
                                <option value="responsive" selected="selected"><?php echo $entry_class_responsive; ?></option>
                                <?php } else { ?>
                                <option value="popup" selected="selected"><?php echo $entry_class_popup ?></option>
                                <option value="responsive"><?php echo $entry_class_responsive; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-order-status">
                            <span data-toggle="tooltip" title="<?php echo $order_status_after_payment_tooltip; ?>">
                                <?php echo $entry_order_status; ?>
                            </span>
                        </label>
                        <div class="col-sm-10">
                            <select name="iyzico_checkout_installment_order_status_id" id="input-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $iyzico_checkout_installment_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-cancel-order-status"><span data-toggle="tooltip" title="<?php echo $order_status_after_cancel_tooltip; ?>"><?php echo $entry_cancel_order_status; ?></span></label>
                        <div class="col-sm-10">
                            <select name="iyzico_checkout_installment_cancel_order_status_id" id="input-cancel-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $iyzico_checkout_installment_cancel_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="iyzico_checkout_installment_sort_order" value="<?php echo $iyzico_checkout_installment_sort_order; ?>" size="1" class="form-control"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
function save(type) {
    var input   = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'button';
    input.value = type;

    form = $("form[id^='form-']").append(input);

    form.submit();
}
//--></script>
<?php echo $footer; ?> 