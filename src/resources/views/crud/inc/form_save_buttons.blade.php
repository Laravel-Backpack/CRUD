<div id="saveActions" class="form-group">

    <input type="hidden" name="save_action" value="{{ $saveAction['active']['value'] }}">

    <div class="btn-group" role="group">

        <button type="submit" class="btn btn-success">
            <span class="fa fa-save" role="presentation" aria-hidden="true"></span> &nbsp;
            <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
        </button>
    
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">&#x25BC;</span></button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @foreach( $saveAction['options'] as $value => $label)
                <a class="dropdown-item" href="javascript:void(0);" data-value="{{ $value }}">{{ $label }}</a>
                @endforeach
            </div>
          </div>

    </div>

    <a href="{{ $crud->hasAccess('list') ? url($crud->route) : url()->previous() }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
</div>
