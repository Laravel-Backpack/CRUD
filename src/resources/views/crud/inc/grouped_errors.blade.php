{{-- Show the errors, if any --}}
@if ($crud->groupedErrorsEnabled() && session()->get('errors'))
    @php
        $submittedFormId = old('_form_id') ?? 'crudForm';
        $currentFormId = $formId ?? $id ?? 'crudForm';
    @endphp
    @if (!$submittedFormId || $submittedFormId === $currentFormId)
    <div class="alert alert-danger text-danger">
        <ul class="list-unstyled mb-0">
            @foreach(session()->get('errors')->getBags() as $bag => $errorMessages)
                @foreach($errorMessages->getMessages() as $errorMessageForInput)
                    @foreach($errorMessageForInput as $message)
                        <li><i class="la la-info-circle"></i> {{ $message }}</li>
                    @endforeach
                @endforeach
            @endforeach
        </ul>
    </div>
    @endif
@endif