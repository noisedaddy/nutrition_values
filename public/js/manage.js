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
                    
                    //var fixedResponse = data.replace(/['"]/g, '').replace(/[[\]]/g,'');                    
                    //var jsonObj = JSON.parse(fixedResponse);
                    var html = '';
                    var array = data.split(",");
                           
                    $.each(array, function(index, value){
                        html += "<input class='btn btn-default' name='tags' type='button' style='padding: 3px; margin: 2px;' value="+value.replace(/[[\]]/g,'')+">";                            
                    });
                    
                    $('div.div-warning', imageForm).show().html(html);

                });
            });
            //Generate report
            $('button[name=add-report]').on('click', function () {

                App.upload(imageForm, '/getReport', function (data) {

                    var jsonObj = JSON.parse(data);
                    var htmlText = '';
                    var count = Object.keys(jsonObj).length;
                    var check = JSON.stringify(jsonObj);
                    
                    if(check.indexOf('error') !== -1) {
                        alert('Error: Request limit exceeded!');
                        htmlText = '<b>Error: Request limit exceeded!</b>';
                    } else {
                        htmlText += "<ul style='-webkit-column-count: "+count+"; -moz-column-count: "+count+"; column-count: "+count+";'>";                    
                                    $.map(jsonObj, function(value, index) { 
                                        $.map(value.report.food.nutrients, function(val, ind){                                        
                                            htmlText += "<li style='font-size: 10px;'><b>"+val.name+"</b>, "+val.value+val.unit+"</li>";                                        
                                        });
                                    }); 
                        htmlText += "</ul>";
                    }
                    
                    $('.alert', imageForm).remove();
                    $('#fileContainer', imageForm).remove();
                    $('#cropContainer', imageForm).empty();    
                    $('#cropContainer', imageForm).css({"height":"500px","overflow":"scroll"}); 
                    $('#cropContainer', imageForm).html(htmlText);             
                    $('h4.modal-title').text('Nutrition Values');
                });
                                
            });
                                
        });
        
        //Generate single report for tags
        $("body").delegate('input[name=tags]','click', function(){
            
            var imageForm = imageFormInitial.clone(); 
            imageForm.append('<input type="hidden" name="tagname" value="'+this.value+'" />');
            
            App.upload(imageForm, '/getSingleTagReport', function (data) {
                
                    var jsonObj = JSON.parse(data);
                    var htmlText = '';
                    //var check = JSON.stringify(data);

                    if(data.search("error") !== -1) {                     
                        htmlText = ((jsonObj.message === undefined || jsonObj.message === null) ? '<b>Your search resulted in zero results.Change your parameters and try again</b>' : '<b>'+jsonObj.message+'</b>');                                                          
                    } else {
                        htmlText += "<ul style='-webkit-column-count: 3; -moz-column-count: 3; column-count: 3;'>";                    
                                    $.map(jsonObj, function(value, index) { 
                                        $.map(value.report.food.nutrients, function(val, ind){   
                                            htmlText += "<li style='font-size: 15px;'><b>"+val.name+"</b>, "+val.value+val.unit+"</li>";
                                        });
                                    }); 
                        htmlText += "</ul>";
                    }
                    $('.alert').remove();
                    $('#fileContainer').remove();
                    $('#cropContainer').empty();    
                    $('#cropContainer').css({"height":"300px","overflow":"scroll"}); 
                    $('#cropContainer').html(htmlText);             
                    $('h4.modal-title').text('Nutrition Values');
                
                   
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
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt, position, total) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        var percentVal = parseInt( (evt.loaded / evt.total * 100), 10) + "%"
                        bar.width(percentVal);
                        percen.html(percentVal);
                    }
               }, false);
               xhr.addEventListener("progress", function(evt, position, total) {
                   if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        var percentVal = parseInt( (evt.loaded / evt.total * 100), 10) + "%"
                        bar.width(percentVal);
                        percen.html(percentVal);
                   }
               }, false);

               return xhr;
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