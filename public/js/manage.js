var Manage = {
    bind: function (elements) {
        // Store forms
        var imageFormInitial = $('form', $('#modal-image', elements));
        var requestFormInitial = $('form', $('#modal-request', elements));
        // On image modal show

        $('#modal-image', elements).on('show.bs.modal', function (e) {
            // Clone fresh form
            var imageForm = imageFormInitial.clone(); 
            $('.modal-content', this).html(imageForm);

	        //$('.photoupload').bootstrapFileInput();
            // On file selection

            $('input[type=file]', imageForm).on('change', function () {

                App.upload(imageForm, function (data) {

                    $('.alert', imageForm).remove();
                    $('#fileContainer', imageForm).remove();
                    $('#cropContainer', imageForm).removeClass('hidden').children('#cropTarget').attr('src', data.string);
                    $('input[name=path]', imageForm).val(data.path);

                });
            });
            // On submission
            $('button[name=add]', imageForm).on('click', function () {
                Manage.store(imageForm);
            });
        });
        // On image modal hidden
        $('#modal-image', elements).on('hidden.bs.modal', function (e) {
            $('.modal-content', this).html(imageFormInitial);
        });
        // Request popover
        $('[data-toggle=request]', elements).popover({
            container: 'body',
            html: true,
            content: function() {
                return $(this).find('.popover .popover-content').html()
            }
        });
        // Show request modal
        $('[data-target="#modal-request"]', elements).on('click', function(event) {
            $.ajax({
                url: $(this).attr('href'),
                type: 'GET',
                success: function (response) {
                    $('#modal-request', $('#content')).remove();
                    $(response).appendTo($('#content'));
                    // On modal shown
                    $('#modal-request', $('#content')).on('show.bs.modal', function (event) {
                        var requestForm = $('form', this);
                        // On submission
                        $('button[name=send]', requestForm).on('click', function () {
                            requestForm.ajaxForm({
                                data: requestForm.serialize(),
                                success: function (response) {
                                    window.location.reload();
                                },
                                error: function (xhr, status, error) {
                                    return App.errors(xhr, $('.modal-body .column1', requestForm));
                                }
                            });
                        });
                    });
                    // Show it
                    $('#modal-request').modal('show');
                }
            });
            return false;
        });
        // Toggle request
        //$('[data-toggle=request]', elements).on('shown.bs.popover', function(event) {
        //    $('input[name=Request_Status]:radio', $('#' + $(this).attr('aria-describedby'))).change(function() {
        //        $.ajax({
        //            url: $(this).closest('form').attr('action'),
        //            type: 'POST',
        //            data: $(this).closest('form').serialize(),
        //            success: function () {
        //                window.location.reload();
        //            }
        //        });
        //        return false;
        //    });
        //});
        
    },
    store: function(form) {
        // Ajax submission
        var progress = $('.progress', form);
        var bar = $('.progress-bar', progress);
        var percentage = $('span', bar);
        // Set data
        var data = $(form).serializeArray();

        $(form).ajaxForm({
            data: data,
            beforeSend: function () {
                progress.removeClass('hidden');
                var percentVal = '0%';
                bar.width(percentVal);
                percentage.html(percentVal);
            },
            uploadProgress: function (event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                bar.width(percentVal);
                percentage.html(percentVal);
            },
            success: function (response) {
                alert(response);
            },
            complete: function (xhr) {
                var percentVal = '0%';
                bar.width(percentVal);
                percentage.html(percentVal);
                progress.addClass('hidden');
            },
            error: function (xhr, status, error) {
                return App.errors(xhr, $('.modal-body', form));
            }
        });
    }

}

$(document).ready(function () {
    Manage.bind($('body'));
});