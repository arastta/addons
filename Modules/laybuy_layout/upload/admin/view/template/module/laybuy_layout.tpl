<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
          <button type="submit" onclick="save('save')" form="form-laybuy-layout" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-success" data-original-title="Save"><i class="fa fa-check"></i></button>
          <button type="submit" form="form-laybuy-layout" data-toggle="tooltip" title="<?php echo $button_saveclose; ?>" class="btn btn-default" data-original-title="Save & Close"><i class="fa fa-save text-success"></i></button>
          <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-times-circle text-danger"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
    </div>
  </div>
  <div class="container-fluid">
    <?php if (isset($error['error_warning'])) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error['error_warning']; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-laybuy-layout" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-laybuy-layout-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                  <?php if ($laybuy_layout_status) { ?>
                  <input type="radio" name="laybuy_layout_status" value="1" checked="checked" />
                  <?php echo $text_yes; ?>
                  <?php } else { ?>
                  <input type="radio" name="laybuy_layout_status" value="1" />
                  <?php echo $text_yes; ?>
                  <?php } ?>
              </label>
              <label class="radio-inline">
                  <?php if (!$laybuy_layout_status) { ?>
                  <input type="radio" name="laybuy_layout_status" value="0" checked="checked" />
                  <?php echo $text_no; ?>
                  <?php } else { ?>
                  <input type="radio" name="laybuy_layout_status" value="0" />
                  <?php echo $text_no; ?>
                  <?php } ?>
              </label>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>