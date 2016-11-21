var App = {
    upload: function (form, callback) {
        // Set progress bar
        var progress = $('.progress', form);
        var bar = $('.progress-bar', progress);
        var percentage = $('span', bar);
        // Set data
        var data = $(form).serializeArray();
        data.push({name: 'width', value: $('.modal-body', form).width() });

        // Ajax submission
        $(form).ajaxForm({
            url: '/upload',
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
                callback(response);
            },
            complete: function (xhr) {
                var percentVal = '0%';
                bar.width(percentVal);
                percentage.html(percentVal);
                progress.addClass('hidden');
            },
            error: function (xhr, status, error) {
                console.log(JSON.stringify(xhr));
                console.log(JSON.stringify(status));
                console.log(JSON.stringify(error));
                return App.errors(xhr, $('.modal-body', form));
            }
        }).submit();

    },
    errors: function (xhr, container) {

        if (!$('.alert-danger', container).length) {
            $('<div class="alert alert-danger"></div>').prependTo(container);
        }

        $('.alert-danger', container).empty();

        if(xhr.status == 413) {
            $('<p>' + xhr.statusText + '</p>').appendTo($('.alert-danger', container));
            return false;
        }

        $.each(xhr.responseJSON, function (index, value) {
            $('<p>' + value + '</p>').appendTo($('.alert-danger', container));
        });

        return false;
    },
    messages: function (messages, container) {

        alert(JSON.stringify(messages));
        if ( ! $('.alert', container).length) {
            $('<div class="alert alert-success"></div>').prependTo(container);
        }
        $('.alert', container).empty();
        $.each(messages, function (index, value) {
            $('<p>' + value + '</p>').appendTo($('.alert', container));
        });
        return false;
    }
}
//
//$(document).ready(function () {
//    App.bind($('body'));
//});