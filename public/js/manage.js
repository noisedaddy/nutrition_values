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

	        $('.photoupload').bootstrapFileInput();
            // On file selection

            $('input[type=file]', imageForm).on('change', function () {

                App.upload(imageForm, '/upload', function (data) {

                    $('.alert', imageForm).remove();
                    $('#fileContainer', imageForm).remove();
                    $('#cropContainer', imageForm).removeClass('hidden').children('#cropTarget').attr('src', data.string);
                    $('input[name=path]', imageForm).val(data.path);
                    $('button[name=add]', imageForm).removeClass('hide');
                    $('button[name=add-report]', imageForm).removeClass('hide');
                    $('div.div-warning', imageForm).hide();

                });
            });
            // On submission
            $('button[name=add]', imageForm).on('click', function () {
                Manage.store(imageForm, function(data) {

                    var fixedResponse = data.replace(/\\'/g, "'");
                    var jsonObj = JSON.parse(fixedResponse);
                    $('div.div-warning', imageForm).show().empty().text(jsonObj);

                });
            });
            //Generate report
            $('button[name=add-report]').on('click', function () {

                App.upload(imageForm, '/getReport', function (data) {

                    var fixedResponse = data.replace(/\\'/g, "'");
                    var jsonObj = JSON.parse(fixedResponse);
                    var htmlText = '';
                    htmlText += "<ul style='-webkit-column-count: "+jsonObj.length+"; -moz-column-count: "+jsonObj.length+"; column-count: "+jsonObj.length+";'>";
                    
                                $.map(jsonObj, function(value, index) {  
                                    
//                                    //console.log("WWWWWWW:   INDEX:      "+index+" Value: "+value);
                                    
                                    $.map(value, function(val, ind){
                                        
                                        console.log("Index: "+ind);
                                        htmlText += "<li>"+val.name+", "+val.value+val.unit+"</li>";
                                        
                                    });
                                    
                                }); 
                    htmlText += "</ul>";
                    
                    $('.alert', imageForm).remove();
                    $('#fileContainer', imageForm).remove();
                    $('#cropContainer', imageForm).empty();    
                    $('#cropContainer', imageForm).css({"height":"500px","overflow":"scroll"}); 
                    $('#cropContainer', imageForm).html(htmlText);             
                    $('h4.modal-title').text('Nutrition Values');
                });
                
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
        
    },
    store: function(form, callback) {

        // Ajax submission
        var progress = $('.progress', form);
        var bar = $('.progress-bar', progress);
        var percen = $('span', bar);

        // Set data
        var data = $(form).serializeArray();

        $(form).ajaxForm({
            data: data,
            beforeSend: function () {
                progress.removeClass('hidden');
                var percentVal = '0%';
                bar.width(percentVal);
                percen.html(percentVal);
            },
            uploadProgress: function (event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                console.log(percentComplete);
                console.log(total);
                console.log(percentVal);
                bar.width(percentVal);
                percen.html(percentVal);

            },
            success: function (response) {
                callback(response);
                //var fixedResponse = response.replace(/\\'/g, "'");
                //var jsonObj = JSON.parse(fixedResponse);
                //$('div.div-warning', form).show().empty().text(jsonObj);

            },
            complete: function (xhr) {
                var percentVal = '0%';
                bar.width(percentVal);
                percen.html(percentVal);
                progress.addClass('hidden');
            },
            error: function (xhr, status, error) {
                return App.errors(xhr, $('.modal-body', form));
            }
        }).submit();
    }

}

$(document).ready(function () {
    Manage.bind($('body'));
});