$(document).on('hidden.bs.modal', '.modal', function () {
    var modalData = $(this).data('bs.modal');

    if (modalData && modalData.options.remote) {
        $(this).removeData('bs.modal');
        $(this).find(".modal-content").empty();
    }
});

crudForm.submit(function(event) {
    event.preventDefault();

    $(this).ajaxSubmit({
        dataType: 'json',
        success: function(response, statusText, xhr, form) {
            if (xhr.status == 200 && !$.isEmptyObject(response))
            {
                new PNotify({
                    text: response.message,
                    type: 'success'
                });

                $('#crudTable').dataTable().api().ajax.reload();

                switch (saveActionField.val()) {
                    case 'save_and_new':
                    case 'save_and_edit':
                        $("#modal-ajax-crud .modal-content").load(response.redirect_url, function() {
                            $("#modal-ajax-crud").modal("show"); 
                        });
                        break;
                    case 'save_and_back':
                    default:
                        $("#modal-ajax-crud").modal("hide");
                        break;
                }
            }
        },
        error: function(xhr, statusText, error) {
            if (xhr.status == 422)
            {
                if (!$.isEmptyObject(xhr.responseJSON))
                {
                    if (!$.isEmptyObject(xhr.responseJSON.errors))
                    {
                        $.each(xhr.responseJSON.errors, function(property, messages) {
                            var normalizedProperty = property.split('.').map(function(item, index) {
                                    return index === 0 ? item : '['+item+']';
                                }).join('');

                            var field = $('[name="' + normalizedProperty + '[]"]').length ?
                                        $('[name="' + normalizedProperty + '[]"]') :
                                        $('[name="' + normalizedProperty + '"]'),
                                        container = field.parents('.form-group');

                            container.addClass('has-error');

                            $.each(messages, function(key, msg) {
                                // highlight the input that errored
                                var row = $('<div class="help-block">' + msg + '</div>');
                                row.appendTo(container);

                                // highlight its parent tab
                                var tab_id = $(container).parent().attr('id');
                                $("#form_tabs [aria-controls="+tab_id+"]").addClass('text-red');
                            });
                        });
                    }
                    else if (!$.isEmptyObject(xhr.responseJSON.message))
                    {
                        new PNotify({
                            text: xhr.responseJSON.message,
                            type: 'error'
                        });
                    }
                }
                else
                {
                    new PNotify({
                        text: error,
                        type: 'error'
                    });
                }
            }
        }
    }); 

    return false;
});