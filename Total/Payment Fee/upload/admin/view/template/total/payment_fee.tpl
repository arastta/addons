<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" onclick="save('save')" form="form-payment-fee" data-toggle="tooltip" title="" class="btn btn-success" data-original-title="Save"><i class="fa fa-check"></i></button>
                <button type="submit" form="form-payment-fee" data-toggle="tooltip" title="" class="btn btn-default" data-original-title="Save & Close"><i class="fa fa-save text-success"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-times-circle text-danger"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payment-fee" class="form-horizontal">
                    <?php $payment_method_row = 0; ?>
                    <div class="table-responsive">
                        <table id="payment-method-fee" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <td class="text-left"><?php echo $entry_payment_method; ?></td>
                                <td class="text-left"><?php echo $entry_fee; ?></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payment_methods as $payment_method) { ?>
                            <tr id="payment-method-row<?php echo $payment_method_row; ?>">
                                <td class="text-left">
                                    <select name="payment_fee_payment_method[<?php echo $payment_method_row; ?>][method]" class="form-control">
                                        <?php foreach ($methods as $method) { ?>
                                        <?php if ($method['code'] == $payment_method['method']) { ?>
                                        <option value="<?php echo $method['code']; ?>" selected="selected"><?php echo $method['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $method['code']; ?>"><?php echo $method['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td class="text-left"><input type="text" name="payment_fee_payment_method[<?php echo $payment_method_row; ?>][fee]" value="<?php echo $payment_method['fee']; ?>" placeholder="<?php echo $entry_fee; ?>" class="form-control" /></td>
                                <td class="text-left"><button type="button" onclick="$(this).tooltip('destroy');$('#payment-method-row<?php echo $payment_method_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                            </tr>
                            <?php $payment_method_row++; ?>
                            <?php } ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-left"><button type="button" onclick="addPaymentMethodFee();" data-toggle="tooltip" title="<?php echo $button_payment_method_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <label class="radio-inline">
                                <?php if ($payment_fee_status) { ?>
                                <input type="radio" name="payment_fee_status" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <?php } else { ?>
                                <input type="radio" name="payment_fee_status" value="1" />
                                <?php echo $text_yes; ?>
                                <?php } ?>
                            </label>
                            <label class="radio-inline">
                                <?php if (!$payment_fee_status) { ?>
                                <input type="radio" name="payment_fee_status" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="payment_fee_status" value="0" />
                                <?php echo $text_no; ?>
                                <?php } ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="payment_fee_sort_order" value="<?php echo $payment_fee_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
    var payment_method_row = <?php echo $payment_method_row; ?>;

    function addPaymentMethodFee() {
        html  = '<tr id="payment-method-row' + payment_method_row + '">';
        html += '  <td class="text-left"><select name="payment_fee_payment_method[' + payment_method_row + '][method]" class="form-control">';
        <?php foreach ($methods as $method) { ?>
        html += '      <option value="<?php echo $method['code']; ?>"><?php echo $method['name']; ?></option>';
        <?php } ?>
        html += '      </select></td>';
        html += '  <td class="text-left"><input type="text" name="payment_fee_payment_method[' + payment_method_row + '][fee]" value="" placeholder="<?php echo $entry_fee; ?>" class="form-control" /></td>';
        html += '  <td class="text-left"><button type="button" onclick="$(this).tooltip(\'destroy\');$(\'#payment-method-row' + payment_method_row + '\').remove();" data-toggle="tooltip" rel="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
        html += '</tr>';

        $('#payment-method-fee tbody').append(html);
        $('[rel=tooltip]').tooltip();

        payment_method_row++;
    }
    //--></script>
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
