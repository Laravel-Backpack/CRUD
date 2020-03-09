<div id="saveActions" class="form-group">

    <input type="hidden" name="save_action" value="{{ $saveAction['active']['value'] }}">

    <div class="btn-group" role="group">

        <button type="submit" class="btn btn-success">
            <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
            <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
        </button>

        <div class="btn-group" role="group">
            <button id="bpSaveButtonsGroup" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">&#x25BC;</span></button>
            <div class="dropdown-menu" aria-labelledby="bpSaveButtonsGroup">
                @foreach( $saveAction['options'] as $value => $label)
                <a class="dropdown-item" href="#" data-value="{{ $value }}">{{ $label }}</a>
                @endforeach
            </div>
          </div>

    </div>

    <a href="{{ $crud->hasAccess('list') ? url($crud->route) : url()->previous() }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
</div>

@push('after_scripts')
<script>
    function checkFormValidity(form) {
        if (!form[0].checkValidity || form[0].checkValidity()) {
            return true;
        }
        return false;
    }

    function checkReportValidity(form) {
        if (form[0].reportValidity) {
            $('#saveActions').find('.dropdown-menu').removeClass('show');
            form[0].reportValidity();
        }
    }

    function changeTabIfNeeded(form) {
        //we get the first erroed field
        var $firstErrorField = form.find(":invalid").first();
        //we find the closest tab
        var $closest = $($firstErrorField).closest('.tab-pane');
        //if we found the tab we will change to that tab before reporting validity of form
        if($closest.length) {
            var id = $closest.attr('id');
                // switch tabs
                $('.nav a[href="#' + id + '"]').tab('show');
        }
    }

    // make Save Buttons that are anchors behave like Submit buttons (trigger HTML5 validation)
    jQuery(document).ready(function($) {

        var selector = $('#bpSaveButtonsGroup').next();
        var form = $(selector).closest('form');
        var saveActionField = $('[name="save_action"]');
        var $defaultSubmitButton = $(form).find(':submit');

        //we need to also emulate for the default button that's not on anchor list.
        $($defaultSubmitButton).on('click', function(e) {
            e.preventDefault();
            $saveAction = $(this).children('span').eq(1);
            if(checkFormValidity(form)) {
                saveActionField.val( $saveAction.attr('data-value') );
                form.submit();
            }else{
                changeTabIfNeeded(form);
                checkReportValidity(form);
            }
        });

        //this is for the anchors
        $(selector).find('a').each(function() {
            $(this).click(function(e) {
                //we check if form is valid
                if (checkFormValidity(form)) {
                    //if everything is validated we proceed with the submission
                    var saveAction = $(this).data('value');
                    saveActionField.val( saveAction );
                    form.submit();
                }else{
                    changeTabIfNeeded(form);
                    checkReportValidity(form);
                }
                e.stopPropagation();
            });
        });
    });

    </script>
@endpush
