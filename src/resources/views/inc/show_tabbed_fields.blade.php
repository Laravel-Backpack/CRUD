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

<div class="tab-container {{ $horizontalTabs ? 'col-md-12' : 'col-md-3 m-t-10' }}">

    <div class="nav-tabs-custom" id="form_tabs">
        <ul class="nav {{ $horizontalTabs ? 'nav-tabs' : 'nav-stacked nav-pills'}}" role="tablist">
            @foreach ($crud->getTabs($box) as $k => $tab)
                <li role="presentation" class="{{$k == 0 ? 'active' : ''}}">
                    <a href="#tab_{{ camel_case($tab) }}" aria-controls="tab_{{ camel_case($tab) }}" role="tab" data-toggle="tab">{{ $tab }}</a>
                </li>
            @endforeach
        </ul>
    </div>

</div>

<div class="tab-content {{$horizontalTabs ? 'col-md-12' : 'col-md-9 m-t-10'}}">

    @foreach ($crud->getTabs($box) as $k => $tab)
    <div role="tabpanel" class="tab-pane{{$k == 0 ? ' active' : ''}}" id="tab_{{ camel_case($tab) }}">

        @include('crud::inc.show_fields', ['fields' => $crud->getTabFields($box, $tab)])

    </div>
    @endforeach

</div>