<?php $pos_methods = array('nestpay', 'gvp', 'posnet', 'boa', 'get724', 'payflex', 'payu', 'ipara'); ?>
<?php $pos_models = array('classic', '3d_model', '3d_pay', '3d_hosting', 'hosting'); ?>
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-new-bank" class="form-horizontal">
    <div class="form-group required">
        <label class="col-sm-3 control-label" for="input-name"><?php echo $entry_name; ?></label>
        <div class="col-sm-9">
            <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control"/>
            <?php if ($error_name) { ?>
            <div class="text-danger"><?php echo $error_name; ?></div>
            <?php } ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="input-image"><?php echo $entry_image; ?></label>
        <div class="col-sm-9">
            <a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>"/></a>
            <input type="hidden" name="image" value="<?php echo $image; ?>" id="input-image"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="input-status"><?php echo $entry_status; ?></label>
        <div class="col-sm-9">
            <select name="status" id="input-status" class="form-control">
                <?php if ($status) { ?>
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
        <label class="col-sm-3 control-label" for="input-method"><?php echo $entry_method; ?></label>
        <div class="col-sm-9">
            <select name="method" id="input-method" class="form-control">
                <?php foreach($pos_methods as $method_item) {
                if ($method_item == $method) { ?>
                <option value="<?php echo $method_item; ?>" selected="selected"><?php echo $method_item; ?></option>
                <?php } else { ?>
                <option value="<?php echo $method_item; ?>"><?php echo $method_item; ?></option>
                <?php } }?>
            </select>
            <?php if ($error_method) { ?>
            <div class="text-danger"><?php echo $error_method; ?></div>
            <?php } ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="input-model"><?php echo $entry_model; ?></label>
        <div class="col-sm-9">
            <select name="model" id="input-model" class="form-control">
                <?php foreach($pos_models as $model_item) {
                if ($model_item == $model) { ?>
                <option value="<?php echo $model_item; ?>" selected="selected"><?php echo $model_item; ?></option>
                <?php } else { ?>
                <option value="<?php echo $model_item; ?>"><?php echo $model_item; ?></option>
                <?php } }?>
            </select>
            <?php if ($error_model) { ?>
            <div class="text-danger"><?php echo $error_model; ?></div>
            <?php } ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" for="input-short"><?php echo $entry_short; ?></label>
        <div class="col-sm-9">
            <input type="text" name="short" value="<?php echo $short; ?>" placeholder="<?php echo $entry_short; ?>" id="input-short" class="form-control"/>
            <?php if ($error_short) { ?>
            <div class="text-danger"><?php echo $error_short; ?></div>
            <?php } ?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-12">
            <div class="pull-right">
                <?php ?>
                <button type="button" id="<?php echo $button_id; ?>" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-success add-new-bank">
                    <i class="fa fa-check"></i></button>
                <button type="button" data-dismiss="modal" aria-hidden="true" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default">
                    <i class="fa fa-times-circle text-danger"></i></button>
            </div>
        </div>
    </div>
    <input type="hidden" name="bank_id" value="<?php echo $bank_id; ?>">
</form>