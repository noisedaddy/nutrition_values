var Manage = {
    bind: function (elements) {
        // Store forms
        var imageFormInitial = $('form', $('#modal-image', elements));
        var requestFormInitial = $('form', $('#modal-request', elements));
        // On image modal show

        $('#modal-image', elements).on('show.bs.modal', function (e) {
            console.log('dsazdada');
            // Clone fresh form
            var imageForm = imageFormInitial.clone(); 
            $('.modal-content', this).html(imageForm);

	        //$('.photoupload').bootstrapFileInput();
            // On file selection

            $('input[type=file]', imageForm).on('change', function () {
                console.log('inside element');
                App.upload(imageForm, function (response) {

                    $('.alert', imageForm).remove();
                    $('#fileContainer', imageForm).remove();
                    $('#cropContainer', imageForm).removeClass('hidden').children('#cropTarget').attr('src', response);
                    $('input[name=status]', imageForm).val(true);
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
        $(form).ajaxForm({
            data: $(form).serialize(),
            success: function (response) {
                $('#modal-image').modal('hide');
                location.reload();
            },
            error: function (xhr, status, error) {
                return App.errors(xhr, $('.modal-body', form));
            }
        });
    },
    //imageDefault: function (id, token) {
    //    $.ajax({
    //        url: '/manage/image/default',
    //        type: 'POST',
    //        data: {'id': id, '_token': token},
    //        success: function () {
    //            window.location.reload();
    //        }
    //    });
    //    return false;
    //},
    //imageDelete: function (id, token) {
    //    $.ajax({
    //        url: '/manage/image/delete',
    //        type: 'POST',
    //        data: {'id': id, '_token': token},
    //        success: function () {
    //            window.location.reload();
    //        }
    //    });
    //    return false;
    //}

}

$(document).ready(function () {
    Manage.bind($('body'));
});