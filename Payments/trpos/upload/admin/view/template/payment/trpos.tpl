<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="button" data-toggle="tooltip" title="<?php echo $tab_add; ?>" class="btn btn-success add-bank">
                    <i class="fa fa-plus"></i></button>
                <?php if (!$banks) { ?>
                <div class="ba-message">
                    <span><?php echo $tab_add; ?></span>
                    <div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $text_close; ?>">
                            <span aria-hidden="true"><i class="fa fa-times-circle"></i></span></button>
                    </div>
                </div>
                <?php } ?>
                <button type="submit" onclick="save('save')" form="form-trpos" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-success" data-original-title="Save">
                    <i class="fa fa-check"></i></button>
                <button type="submit" form="form-trpos" data-toggle="tooltip" title="<?php echo $button_saveclose; ?>" class="btn btn-default" data-original-title="Save & Close">
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
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-trpos" class="form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab-general" class="tab-general" data-toggle="tab"><?php echo $tab_general; ?></a>
                        </li>
                        <?php foreach ($banks as $tab_bank) { ?>
                        <li id="bank-id-<?php echo $tab_bank['bank_id']; ?>">
                            <a href="#tab-bank-<?php echo $tab_bank['bank_id']; ?>" data-toggle="tab">
                                <?php if (!empty($tab_bank['image'])) {?>
                                <img src="<?php echo $tab_bank['image']; ?>"/>
                                <?php } else { ?>
                                <?php echo $tab_bank['name']; ?>
                                <?php } ?>
                                <span class="edit-bank" data-toggle="tooltip" title="<?php echo $button_edit_bank; ?>"><i class="fa fa-pencil text-info"></i></span>
                                <span class="remove-bank" data-toggle="tooltip" title="<?php echo $button_remove_bank; ?>"><i class="fa fa-times-circle text-danger"></i></span>
                            </a></li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active in" id="tab-general">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-mode"><?php echo $entry_mode; ?></label>
                                <div class="col-sm-10">
                                    <select name="trpos_mode" id="input-mode" class="form-control">
                                        <?php if ($trpos_mode == 'live') { ?>
                                        <option value="live" selected="selected"><?php echo $text_live; ?></option>
                                        <?php } else { ?>
                                        <option value="live"><?php echo $text_live; ?></option>
                                        <?php } ?>
                                        <?php if ($trpos_mode == 'test') { ?>
                                        <option value="test" selected="selected"><?php echo $text_test; ?></option>
                                        <?php } else { ?>
                                        <option value="test"><?php echo $text_test; ?></option>
                                        <?php } ?>
                                        <?php if ($trpos_mode == 'debug') { ?>
                                        <option value="debug" selected="selected"><?php echo $text_debug; ?></option>
                                        <?php } else { ?>
                                        <option value="debug"><?php echo $text_debug; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-other"><?php echo $entry_other; ?></label>
                                <div class="col-sm-10">
                                    <select name="trpos_other_id" id="input-other" class="form-control">
                                        <?php foreach ($banks as $bank) { ?>
                                        <?php if ($bank['bank_id'] == $trpos_other_id) { ?>
                                        <option value="<?php echo $bank['bank_id']; ?>" selected="selected"><?php echo $bank['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $bank['bank_id']; ?>"><?php echo $bank['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="trpos_total" value="<?php echo $trpos_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="trpos_order_status_id" id="input-order-status" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $trpos_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
                                <div class="col-sm-10">
                                    <select name="trpos_geo_zone_id" id="input-geo-zone" class="form-control">
                                        <option value="0"><?php echo $text_all_zones; ?></option>
                                        <?php foreach ($geo_zones as $geo_zone) { ?>
                                        <?php if ($geo_zone['geo_zone_id'] == $trpos_geo_zone_id) { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="trpos_status" id="input-status" class="form-control">
                                        <?php if ($trpos_status) { ?>
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
                                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="trpos_sort_order" value="<?php echo $trpos_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($banks as $tab_bank) { ?>
                        <div class="tab-pane" id="tab-bank-<?php echo $tab_bank['bank_id']; ?>">
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][bank_id]" value="<?php echo $tab_bank['bank_id']; ?>"/>
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][name]" value="<?php echo $tab_bank['name']; ?>"/>
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][image]" value="<?php echo $tab_bank['image']; ?>"/>
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][method]" value="<?php echo $tab_bank['method']; ?>"/>
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][model]" value="<?php echo $tab_bank['model']; ?>"/>
                            <input type="hidden" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][status]" value="<?php echo $tab_bank['status']; ?>"/>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $tab_bank['name'] . ' , ' . $tab_bank['method'] . ' , ' . $tab_bank['model'] . ' , ' . $tab_bank['status']; ?></label>
                                <div class="col-sm-10"></div>
                            </div>
                            <?php foreach ($tab_bank['entries'] as $entry => $value) { ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-<?php echo $entry; ?>"><?php echo ${'entry_'.$entry}; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="trpos_banks_info[<?php echo $tab_bank['bank_id']; ?>][<?php echo $entry; ?>]" value="<?php echo $value; ?>" placeholder="<?php echo $entry; ?>" id="input-<?php echo $entry; ?>" class="form-control"/>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style type="text/css"><!--
.tab-general {
    height       : 62px;
    width        : 120px;
    font-size    : 20px;
    padding-left : 30px !important;
    padding-top  : 15px !important;
}
.pull-right .ba-message {
    left  : -40px;
    width : 150px;
}
.edit-bank {
    margin-right : 10px;
    font-size    : 15px;
}
.remove-bank {
    font-size : 15px;
}
--></style>
<script type="text/javascript"><!--
$(document).ready(function () {
    $(document).on('click', '.add-bank', function (e) {
        e.preventDefault();

        $('#modal-popup').remove();

        var element = this;

        $.ajax({
            url     : 'index.php?route=payment/trpos/bank&token=<?php echo $token; ?>',
            type    : 'get',
            dataType: 'html',
            success : function (data) {
                html = '<div id="modal-popup" class="modal">';
                html += '  <div class="modal-dialog">';
                html += '    <div class="modal-content">';
                html += '      <div class="modal-header">';
                html += '        <h4 class="modal-title"><?php echo $text_add_bank; ?></h4>';
                html += '      </div>';
                html += '      <div class="modal-body">' + data + '</div>';
                html += '    </div';
                html += '  </div>';
                html += '</div>';

                $('body').append(html);

                $('#modal-popup').modal('show');
            }
        });
    });

    $(document).on('click', '#new-bank', function (e) {
        e.preventDefault();

        $.ajax({
            url     : 'index.php?route=payment/trpos/addBank&token=<?php echo $token; ?>',
            type    : 'post',
            data    : $('#form-new-bank').serialize(),
            dataType: 'json',
            success : function (json) {
                if (json['redirect']) {
                    window.location.href = json['redirect'];
                }

                if (json['html']) {
                    $('#modal-popup').remove();

                    html = '<div id="modal-popup" class="modal">';
                    html += '  <div class="modal-dialog">';
                    html += '    <div class="modal-content">';
                    html += '      <div class="modal-header">';
                    html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                    html += '        <h4 class="modal-title"><?php echo $text_add_bank; ?></h4>';
                    html += '      </div>';
                    html += '      <div class="modal-body">' + json['html'] + '</div>';
                    html += '    </div';
                    html += '  </div>';
                    html += '</div>';

                    $('body').append(html);

                    $('#modal-popup').modal('show');

                    $('.tooltip.fade.top.in').remove();
                }
            }
        });
    });

    $(document).on('click', '.edit-bank', function (e) {
        e.preventDefault();

        tab_bank_id = $(this).closest('li').attr('id');

        bank_id = tab_bank_id.replace('bank-id-', '');

        $.ajax({
            url     : 'index.php?route=payment/trpos/bank&token=<?php echo $token; ?>',
            type    : 'get',
            data    : {bank_id: bank_id},
            dataType: 'html',
            success : function (data) {
                $('#modal-popup').remove();

                html = '<div id="modal-popup" class="modal">';
                html += '  <div class="modal-dialog">';
                html += '    <div class="modal-content">';
                html += '      <div class="modal-header">';
                html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                html += '        <h4 class="modal-title"><?php echo $text_add_bank; ?></h4>';
                html += '      </div>';
                html += '      <div class="modal-body">' + data + '</div>';
                html += '    </div';
                html += '  </div>';
                html += '</div>';

                $('body').append(html);

                $('#modal-popup').modal('show');

                $('.tooltip.fade.top.in').remove();

            }
        });
    });

    $(document).on('click', '#edit-bank', function (e) {
        e.preventDefault();

        $.ajax({
            url     : 'index.php?route=payment/trpos/editBank&token=<?php echo $token; ?>',
            type    : 'post',
            data    : $('#form-new-bank').serialize(),
            dataType: 'json',
            success : function (json) {
                if (json['redirect']) {
                    window.location.href = json['redirect'];
                }

                if (json['html']) {
                    $('#modal-popup').remove();

                    html = '<div id="modal-popup" class="modal">';
                    html += '  <div class="modal-dialog">';
                    html += '    <div class="modal-content">';
                    html += '      <div class="modal-header">';
                    html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                    html += '        <h4 class="modal-title"><?php echo $text_add_bank; ?></h4>';
                    html += '      </div>';
                    html += '      <div class="modal-body">' + json['html'] + '</div>';
                    html += '    </div';
                    html += '  </div>';
                    html += '</div>';

                    $('body').append(html);

                    $('#modal-popup').modal('show');

                    $('.tooltip.fade.top.in').remove();
                }
            }
        });
    });

    $(document).on('click', '.remove-bank', function (e) {
        e.preventDefault();

        var remove_bank = false;

        confirm('<?php echo $text_confirm; ?>') ? remove_bank = true : remove_bank = false;

        if (remove_bank) {
            tab_bank_id = $(this).closest('li').attr('id');

            bank_id = tab_bank_id.replace('bank-id-', '');

            $.ajax({
                url     : 'index.php?route=payment/trpos/deleteBank&token=<?php echo $token; ?>',
                type    : 'post',
                data    : {bank_id: bank_id},
                dataType: 'json',
                success : function (json) {
                    $('#' + tab_bank_id).remove();
                    $('#tab-' + tab_bank_id).remove();
                }
            });
        }
    });
});
--></script>
<script type="text/javascript"><!--
function save(type) {
    var input   = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'button';
    input.value = type;
    form        = $("form[id^='form-']").append(input);
    form.submit();
}
//--></script>
<?php echo $footer; ?>