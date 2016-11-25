var App = {
    upload: function (form, url, callback) {
        // Set progress bar
        var progress = $('.progress', form);
        var bar = $('.progress-bar', progress);
        var percentage = $('span', bar);
        // Set data
        var data = $(form).serializeArray();
        data.push({name: 'width', value: $('.modal-body', form).width() });
        
        // Ajax submission
        $(form).ajaxForm({
            url: url,
            data: data,
            beforeSend: function () {
                progress.removeClass('hidden');
                var percentVal = '0%';
                bar.width(percentVal);
                percentage.html(percentVal);
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt, position, total) {
                    console.log(evt.lengthComputable);
                    console.log((evt));
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        var percentVal = evt.loaded / 100 + '%';
                        bar.width(percentVal);
                        percentage.html(percentVal);
                        console.log(percentComplete);
                        console.log(total);
                    }
               }, false);

               xhr.addEventListener("progress", function(evt, position, total) {
                   console.log(evt.lengthComputable);
                   console.log((evt));
                   if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        var percentVal = parseInt( (evt.loaded / evt.total * 100), 10) + "%"
                        bar.width(percentVal);
                        percentage.html(percentVal);
                        console.log(percentComplete);
                        console.log(total);
                   }
               }, false);

               return xhr;
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
                return App.errors(xhr, status, $('.modal-body', form));
            }
        }).submit();

    },
    errors: function (xhr, status, container) {

        if (!$('.alert-danger', container).length) {
            $('<div class="alert alert-danger"></div>').prependTo(container);
        }
        
        $('.alert-danger', container).empty();

        if(xhr.status == 413 || xhr.status == 500) {
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