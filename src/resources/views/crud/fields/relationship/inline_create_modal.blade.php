@php

    $loadedAssets = json_decode($parentLoadedAssets, true);

    //mark parent crud assets as loaded.
    foreach($loadedAssets as $asset) {
        Assets::markAssetAsLoaded($asset);
    }
@endphp
<div class="modal fade" id="inline-create-dialog" tabindex="0" role="dialog" aria-labelledby="{{$entity}}-inline-create-dialog-label" aria-hidden="true">
<div class="{{ $modalClass }}" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="{{ $entity }}-inline-create-dialog-label">
            {!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name !!}
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body bg-light">
            <form method="post"
            id="{{$entity}}-inline-create-form"
            action="#"
            onsubmit="return false"
          @if ($crud->hasUploadFields('create'))
          enctype="multipart/form-data"
          @endif
            >
        {!! csrf_field() !!}

        <!-- load the view from the application if it exists, otherwise load the one in the package -->
        @if(view()->exists('vendor.backpack.crud.fields.relationship.form_content'))
            @include('vendor.backpack.crud.fields.relationship.form_content', [ 'fields' => $fields, 'action' => $action])
        @else
            @include('crud::fields.relationship.form_content', [ 'fields' => $fields, 'action' => $action])
        @endif


    </form>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelButton">{{trans('backpack::crud.cancel')}}</button>
          <button type="button" class="btn btn-primary" id="saveButton">{{trans('backpack::crud.save')}}</button>
        </div>
      </div>
    </div>
  </div>



