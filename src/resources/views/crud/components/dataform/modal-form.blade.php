{{-- Modal HTML (initially hidden from DOM) --}}
@php
    if(isset($formRouteOperation)) {
        if(!\Str::isUrl($formRouteOperation)) {
            $formRouteOperation = url($crud->route . '/' . $formRouteOperation);
        }
    }
@endphp
@push('after_styles') @if (request()->ajax()) @endpush @endif
@if (!request()->ajax()) @endpush @endif
@push('after_scripts') @if (request()->ajax()) @endpush @endif
    <div class="d-none" id="modalTemplate{{ md5($controller.$id) }}">
        <div class="modal modal-blur fade" id="{{$id}}" tabindex="0" role="dialog" data-bs-backdrop="static" data-backdrop="static" aria-labelledby="formModalLabel{{ md5($controller.$id) }}" aria-hidden="true" data-hashed-form-id="{{ $hashedFormId }}">
            <div class="{{$classes}}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel{{ md5($controller.$id) }}">{{ $title }}</h5>
                        <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                    <div class="modal-body bg-light">
                        <div id="modal-form-errors{{ md5($controller.$id) }}" class="alert alert-danger d-none">
                            <ul id="modal-form-errors-list{{ md5($controller.$id) }}"></ul>
                        </div>
                        <div 
                            id="modal-form-container{{ md5($controller.$id) }}" 
                            data-form-load-route="{{ $formRouteOperation }}"
                            data-form-action="{{ $action }}"
                            data-form-method="{{ $method }}"
                            data-has-upload-fields="{{ $hasUploadFields ? 'true' : 'false' }}"
                            data-refresh-datatable="{{ $refreshDatatable ? 'true' : 'false' }}"
                            >
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p>Loading form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitForm{{ md5($controller.$id) }}">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@if (!request()->ajax()) @endpush @endif
@push('after_scripts') @if (request()->ajax()) @endpush @endif
    @include('crud::components.dataform.modal-form-scripts')
@if (!request()->ajax()) @endpush @endif
