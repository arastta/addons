<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" onclick="save('save')" form="form-paysondirect" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-success" data-original-title="Save"><i class="fa fa-check"></i></button>
				<button type="submit" form="form-paysondirect" data-toggle="tooltip" title="<?php echo $button_saveclose; ?>" class="btn btn-default" data-original-title="Save & Close"><i class="fa fa-save text-success"></i></button>
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
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-paysondirect" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-modul-version"><?php echo $text_modul_name . ' V- '. $paysondirect_modul_version; ?></label>
						<div class="col-sm-10" hidden>
							<input type="text" name="paysondirect_modul_version" hidden value="<?php echo $text_modul_version; ?>" placeholder="<?php echo $text_modul_version; ?>" id="input-modul-version" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_sort_order" value="<?php echo $paysondirect_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
						<div class="col-sm-10">
							<select name="paysondirect_status" id="input-status" class="form-control">
								<?php if ($paysondirect_status) { ?>
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
						<label class="col-sm-2 control-label" for="input-method-mode"><span data-toggle="tooltip" title="<?php echo $help_method_mode; ?>"><?php echo $paysondirect_method_mode; ?></span></label>
						<div class="col-sm-10">
							<select name="paysondirect_mode" id="input-method-mode" class="form-control">
								<option value="1"
								<?php echo ($paysondirect_mode?'selected':''); ?> ><?php echo $paysondirect_method_mode_live; ?></option>
								<option value="0"
								<?php echo ($paysondirect_mode?'':'selected'); ?> ><?php echo $paysondirect_method_mode_sandbox; ?></option>
							</select>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-2 control-label" for="input-user-name"><span data-toggle="tooltip" title="<?php echo $help_user_name; ?>"><?php echo $user_name; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_user_name" value="<?php echo $paysondirect_user_name; ?>" placeholder="<?php echo $user_name; ?>" id="input-user-name" class="form-control"/>
							<?php if ($error_user_name) { ?>
							<div class="text-danger"><?php echo $error_user_name; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-2 control-label" for="input-agent-id"><span data-toggle="tooltip" title="<?php echo $help_agent_id; ?>"><?php echo $agent_id; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_agent_id" value="<?php echo $paysondirect_agent_id; ?>" placeholder="<?php echo $agent_id; ?>" id="input-agent-id" class="form-control"/>
							<?php if ($error_agent_id) { ?>
							<div class="text-danger"><?php echo $error_agent_id; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-2 control-label" for="input-md5"><span data-toggle="tooltip" title="<?php echo $help_md5; ?>"><?php echo $md5; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_md5" value="<?php echo $paysondirect_md5; ?>" placeholder="<?php echo $md5; ?>" id="input-md5" class="form-control"/>
							<?php if ($error_md5) { ?>
							<div class="text-danger"><?php echo $error_md5; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-payment-method"><span data-toggle="tooltip" title="<?php echo $help_method_card_bank_info; ?>"><?php echo $paysondirect_method_card_bank_info; ?></span></label>
						<div class="col-sm-10">
							<select name="paysondirect_payment_method" id="input-payment-method" class="form-control">
								<option value="0"
								<?php echo ($paysondirect_payment_method == 0 ? 'selected="selected"' : '""') . '>' . $payment_method_none; ?></option>
								<option value="1"
								<?php echo ($paysondirect_payment_method == 1 ? 'selected="selected"' : '""') . '>' . $payment_method_card; ?></option>
								<option value="2"
								<?php echo ($paysondirect_payment_method == 2 ? 'selected="selected"' : '""') . '>' . $payment_method_bank; ?></option>
								<option value="3"
								<?php echo ($paysondirect_payment_method == 3 ? 'selected="selected"' : '""') . '>' . $payment_method_inv; ?></option>
								<option value="4"
								<?php echo ($paysondirect_payment_method == 4 ? 'selected="selected"' : '""') . '>' . $payment_method_sms; ?></option>
								<option value="5"
								<?php echo ($paysondirect_payment_method == 5 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_bank; ?></option>
								<option value="6"
								<?php echo ($paysondirect_payment_method == 6 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_card; ?></option>
								<option value="7"
								<?php echo ($paysondirect_payment_method == 7 ? 'selected="selected"' : '""') . '>' . $payment_method_card_bank; ?></option>
								<option value="8"
								<?php echo ($paysondirect_payment_method == 8 ? 'selected="selected"' : '""') . '>' . $payment_method_card_bank_sms; ?></option>
								<option value="9"
								<?php echo ($paysondirect_payment_method == 9 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_inv; ?></option>
								<option value="10"
								<?php echo ($paysondirect_payment_method == 10 ? 'selected="selected"' : '""') . '>' . $payment_method_bank_inv; ?></option>
								<option value="11"
								<?php echo ($paysondirect_payment_method == 11 ? 'selected="selected"' : '""') . '>' . $payment_method_card_inv; ?></option>
								<option value="12"
								<?php echo ($paysondirect_payment_method == 12 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_bank_inv; ?></option>
								<option value="13"
								<?php echo ($paysondirect_payment_method == 13 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_card_inv; ?></option>
								<option value="14"
								<?php echo ($paysondirect_payment_method == 14 ? 'selected="selected"' : '""') . '>' . $payment_method_inv_car_ban; ?></option>
								<option value="15"
								<?php echo ($paysondirect_payment_method == 15 ? 'selected="selected"' : '""') . '>' . $payment_method_sms_bank_card_inv; ?></option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-secure-word"><span data-toggle="tooltip" title="<?php echo $help_secure_word; ?>"><?php echo $secure_word; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_secure_word" value="<?php echo $paysondirect_secure_word; ?>" placeholder="<?php echo $secure_word; ?>" id="input-secure-word" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-logg"><span data-toggle="tooltip" title="<?php echo $help_logg; ?>"><?php echo $entry_logg; ?></span></label>
						<div class="col-sm-10">
							<select name="paysondirect_logg" id="input-logg" class="form-control">
								<option value="1"
								<?php echo ($paysondirect_logg == 1 ? 'selected="selected"' : '""') . '>' . $text_enabled?></option>
								<option value="0"
								<?php echo ($paysondirect_logg == 0 ? 'selected="selected"' : '""') . '>' . $text_disabled?></option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_total" value="<?php echo $paysondirect_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
						<div class="col-sm-10">
							<select name="paysondirect_order_status_id" id="input-order-status" class="form-control">
								<?php foreach ($order_statuses as $order_status) { ?>
								<?php if ($order_status['order_status_id'] == $paysondirect_order_status_id) { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-order-status">Invoice order status</label>
						<div class="col-sm-10">
							<select name="paysondirect_invoice_status_id" id="input-invoice-status-id" class="form-control">
								<?php foreach ($order_statuses as $order_status) { ?>
								<?php if ($order_status['order_status_id'] == $paysondirect_invoice_status_id) { ?>
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
							<select name="paysondirect_geo_zone_id" id="input-geo-zone" class="form-control">
								<option value="0"><?php echo $text_all_zones; ?></option>
								<?php foreach ($geo_zones as $geo_zone) { ?>
								<?php if ($geo_zone['geo_zone_id'] == $paysondirect_geo_zone_id) { ?>
								<option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-logg"><?php echo $entry_show_receipt_page; ?></label>
						<div class="col-sm-10">
							<select name="paysondirect_receipt" id="input-receipt" class="form-control">
								<option value="1"
								<?php echo ($paysondirect_receipt?'selected':''); ?>> <?php echo $entry_show_receipt_page_yes; ?></option>
								<option value="0"
								<?php echo ($paysondirect_receipt?'':'selected'); ?>><?php echo $entry_show_receipt_page_no; ?></option>
							</select>
						</div>
					</div>
					<div class="form-group required">
						<label class="col-sm-2 control-label" for="input-ignored-order-totals"><span data-toggle="tooltip" title="<?php echo $help_totals_to_ignore; ?>"><?php echo $entry_totals_to_ignore; ?></span></label>
						<div class="col-sm-10">
							<input type="text" name="paysondirect_ignored_order_totals" value="<?php echo ($paysondirect_ignored_order_totals == '' ? '' : $paysondirect_ignored_order_totals); ?>" placeholder="<?php echo $entry_totals_to_ignore; ?>" id="input-ignored-order-totals" class="form-control"/>
							<?php if ($error_ignored_order_totals) { ?>
							<div class="text-danger"><?php echo $error_ignored_order_totals; ?></div>
							<?php } ?>
						</div>
					</div>
				</form>
			</div>
		</div>
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