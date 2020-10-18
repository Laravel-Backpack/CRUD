@php
    /*
        To override all cancel button urls with your custom url you should create a crud macro in your service provider
        We don't need to pass any parameter because there `$this` will be the crud panel.
    */
    if($crud->hasMacro('cancelButtonUrl') && is_null($crud->getOperationSetting('cancelButtonUrl'))) {
        $cancel_button_href = $crud->cancelButtonUrl();
    }else{
        //if developer provided a closure, run the closure.
        if(is_callable($crud->getOperationSetting('cancelButtonUrl'))) {
            $cancel_button_href = $crud->getOperationSetting('cancelButtonUrl')($crud);
        }else{
            //if nothing is specified about cancel button url we use the backpack defaults
            if(is_null($crud->getOperationSetting('cancelButtonUrl'))) {
                $cancel_button_href = $crud->hasAccess('list') ? url($crud->route) : url()->previous();
            }else{
                //developer might use a url string here if he does not need any customization in the closure.
                $cancel_button_href = $crud->getOperationSetting('cancelButtonUrl');
            }
        }
    }
@endphp

@if(!$crud->hasOperationSetting('showCancelButton') || $crud->getOperationSetting('showCancelButton') == true)
    <a href="{{ $cancel_button_href }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
@endif
