@php
    $horizontalTabs = $crud->getTabsType()=='horizontal' ? true : false;
@endphp

@push('crud_fields_styles')
    <style>
        .nav-tabs-custom {
            box-shadow: none;
        }

        .nav-tabs-custom > .nav-tabs.nav-stacked > li {
            margin-right: 0;
        }

        .tab-pane .form-group h1:first-child,
        .tab-pane .form-group h2:first-child,
        .tab-pane .form-group h3:first-child {
            margin-top: 0;
        }
    </style>
@endpush

@if ($crud->getFieldsWithoutATab()->count())
    <div class="card no-padding no-border">
        <table class="table table-striped mb-0">
            <tbody>
            @foreach ($crud->getFieldsWithoutATab() as $column)
                <tr>
                    <td>
                        <strong>{!! $column['label'] !!}:</strong>
                    </td>
                    <td>
                        @if (!isset($column['type']))
                            @include('crud::columns.text')
                        @else
                            @if(view()->exists('vendor.backpack.crud.columns.'.$column['type']))
                                @include('vendor.backpack.crud.columns.'.$column['type'])
                            @else
                                @if(view()->exists('crud::columns.'.$column['type']))
                                    @include('crud::columns.'.$column['type'])
                                @else
                                    @include('crud::columns.text')
                                @endif
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endif

<div class="tab-container {{ $horizontalTabs ? '' : 'container'}} mb-2">

    <div class="nav-tabs-custom {{ $horizontalTabs ? '' : 'row'}}" id="form_tabs">
        <ul class="nav {{ $horizontalTabs ? 'nav-tabs' : 'flex-column nav-pills'}} {{ $horizontalTabs ? '' : 'col-md-3' }}"
            role="tablist">
            @foreach ($crud->getTabs() as $k => $tab)
                <li role="presentation" class="nav-item">
                    <a href="#tab_{{ Str::slug($tab) }}"
                       aria-controls="tab_{{ Str::slug($tab) }}"
                       role="tab"
                       tab_name="{{ Str::slug($tab) }}"
                       data-toggle="tab"
                       class="nav-link {{ isset($tabWithError) ? ($tab == $tabWithError ? 'active' : '') : ($k == 0 ? 'active' : '') }}"
                    >{{ $tab }}</a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content p-0 {{$horizontalTabs ? '' : 'col-md-9'}}">

            @foreach ($crud->getTabs() as $k => $tab)
                <div role="tabpanel" class="card no-padding no-border tab-pane {{  ($k == 0 ? ' active' : '') }}" id="tab_{{ Str::slug($tab) }}">
                    <table class="table table-striped mb-0">
                        <tbody>
                        @foreach ($crud->getTabFields($tab) as $column)
                            <tr>
                                <td>
                                    <strong>{!! $column['label'] !!}:</strong>
                                </td>
                                <td>
                                    @if (!isset($column['type']))
                                        @include('crud::columns.text')
                                    @else
                                        @if(view()->exists('vendor.backpack.crud.columns.'.$column['type']))
                                            @include('vendor.backpack.crud.columns.'.$column['type'])
                                        @else
                                            @if(view()->exists('crud::columns.'.$column['type']))
                                                @include('crud::columns.'.$column['type'])
                                            @else
                                                @include('crud::columns.text')
                                            @endif
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

        </div>
    </div>
</div>
