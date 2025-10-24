{{-- Modal HTML (initially hidden from DOM) --}}
@push('after_styles') @if (request()->ajax()) @endpush @endif
@if (!request()->ajax()) @endpush @endif
@push('after_scripts') @if (request()->ajax()) @endpush @endif
    <div class="d-none" id="modalTemplate{{ md5($controller.$formId) }}">
        <div class="modal modal-blur fade" id="{{$formId}}" tabindex="0" role="dialog" data-bs-backdrop="static" data-backdrop="static" aria-labelledby="formModalLabel{{ md5($controller.$formId) }}" aria-hidden="true" data-hashed-form-id="{{ $hashedFormId }}">
            <div class="{{$classes}}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel{{ md5($controller.$formId) }}">{{ $title }}</h5>
                        <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                    <div class="modal-body bg-light">
                        <div id="modal-form-errors{{ md5($controller.$formId) }}" class="alert alert-danger d-none">
                            <ul id="modal-form-errors-list{{ md5($controller.$formId) }}"></ul>
                        </div>
                        <div 
                            id="modal-form-container{{ md5($controller.$formId) }}" 
                            data-form-load-route="{{ $formUrl ?? '' }}"
                            data-form-action="{{ $formAction }}"
                            data-form-method="{{ $formMethod }}"
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
                        <button type="button" class="btn btn-primary" id="submitForm{{ md5($controller.$formId) }}">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@if (!request()->ajax()) @endpush @endif
@push('after_scripts') @if (request()->ajax()) @endpush @endif
    @include('crud::components.dataform.modal-form-scripts')
@if (!request()->ajax()) @endpush @endif

@push('after_scripts')
@endpush
