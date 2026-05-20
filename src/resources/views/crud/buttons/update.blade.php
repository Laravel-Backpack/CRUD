@if ($crud->hasAccess('update', $entry))
    @php
        $backToAllEntriesUrl = $crud->getOperationSetting('backToAllEntriesUrl');
        $editUrl = url($crud->route.'/'.$entry->getKey().'/edit') . ($backToAllEntriesUrl ? '?_backToAllEntriesUrl='.urlencode($backToAllEntriesUrl) : '');
    @endphp
    {{-- Regular update button that redirects to edit page --}}
    @if (!$crud->model->translationEnabled() || $crud->getOperationSetting('showLanguagesDirectlyInEditButton') === false)
        {{-- Single edit button --}}
        <a href="{{ $editUrl }}" bp-button="update" class="btn btn-sm btn-link">
            <i class="la la-edit"></i> <span>{{ trans('backpack::crud.edit') }}</span>
        </a>
    @else
        {{-- Edit button group --}}
        <div class="btn-group">
            <a href="{{ $editUrl }}" class="btn btn-sm btn-link pr-0">
                <span><i class="la la-edit"></i> {{ trans('backpack::crud.edit') }}</span>
            </a>
            <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li class="dropdown-header">{{ trans('backpack::crud.edit_translations') }}:</li>
                @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                    <a class="dropdown-item" href="{{ $editUrl.(str_contains($editUrl, '?') ? '&' : '?').'_locale='.$key }}">{{ $locale }}</a>
                @endforeach
            </ul>
        </div>
    @endif
@endif
