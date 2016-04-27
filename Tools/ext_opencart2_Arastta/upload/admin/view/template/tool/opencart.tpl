<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <?php if ($success) { ?>
        <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" form="form-backup" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-refresh"></i> <?php echo $heading_title; ?></h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal">
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="button-upload"><span data-toggle="tooltip" title="<?php echo $help_upload; ?>"><?php echo $entry_upload; ?></span></label>
                        <div class="col-sm-10">
                            <button type="button" id="button-upload" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary">
                                <i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo $entry_progress; ?></label>
                        <div class="col-sm-10">
                            <div class="progress">
                                <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>
                            </div>
                            <div id="progress-text"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript"><!--
    var step = new Array();
    var total = 0;

    $('#button-upload').on('click', function () {
        $('#form-upload').remove();

        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

        $('#form-upload input[name=\'file\']').trigger('click');
        if (typeof timer != 'undefined') {
            clearInterval(timer);
        }

        timer = setInterval(function () {
            if ($('#form-upload input[name=\'file\']').val() != '') {
                clearInterval(timer);

                // Reset everything
                $('.alert').remove();
                $('#progress-bar').css('width', '0%');
                $('#progress-bar').removeClass('progress-bar-danger progress-bar-success');
                $('#progress-text').html('');

                $.ajax({
                    url        : 'index.php?route=tool/opencart/upload&token=<?php echo $token; ?>',
                    type       : 'post',
                    dataType   : 'json',
                    data       : new FormData($('#form-upload')[0]),
                    cache      : false,
                    contentType: false,
                    processData: false,
                    beforeSend : function () {
                        $('#button-upload').button('loading');
                    },
                    complete   : function () {
                        $('#button-upload').button('reset');
                    },
                    success    : function (json) {
                        if (json['error']) {
                            $('#progress-bar').addClass('progress-bar-danger');
                            $('#progress-text').html('<div class="text-danger">' + json['error'] + '</div>');
                        }

                        if (json['step']) {
                            step  = json['step'];
                            total = step.length;
                            next();
                        }
                    },
                    error      : function (xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        }, 500);
    });

    $('#button-continue').on('click', function () {
        next();

        $('#button-continue').prop('disabled', true);
    });

    function next() {
        data = step.shift();

        if (data) {
            $('#progress-bar').css('width', (100 - (step.length / total) * 100) + '%');
            $('#progress-text').html('<span class="text-info">' + data['text'] + '</span>');

            $.ajax({
                url     : data.url,
                type    : 'post',
                dataType: 'json',
                data    : 'path=' + data.path,
                success : function (json) {
                    if (json['error']) {
                        $('#progress-bar').addClass('progress-bar-danger');
                        $('#progress-text').html('<div class="text-danger">' + json['error'] + '</div>');
                        $('#button-clear').prop('disabled', false);
                    }

                    if (json['success']) {
                        $('#progress-bar').removeClass('progress-bar-danger');
                        $('#progress-bar').addClass('progress-bar-success');
                        $('#progress-text').html('<span class="text-success">' + json['success'] + '</span>');
                    }

                    if (!json['error'] && !json['success']) {
                        next();
                    }
                },
                error   : function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }
    //--></script>
</div>
<?php echo $footer; ?>