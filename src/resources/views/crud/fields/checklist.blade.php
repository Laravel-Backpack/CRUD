<!-- select2 -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <?php $entity_model = $crud->getModel(); ?>

    <div class="row">
        @foreach ($field['model']::all() as $connected_entity_entry)
            <div class="col-sm-4">
                <div class="checkbox">
                  <label class="font-weight-normal">
                    <input type="checkbox"
                      name="{{ $field['name'] }}[]"
                      value="{{ $connected_entity_entry->getRouteKey() }}"

                      @if( ( old( $field["name"] ) && in_array($connected_entity_entry->getRouteKey(), old( $field["name"])) ) || (isset($field['value']) && in_array($connected_entity_entry->getRouteKey(), $field['value']->pluck($connected_entity_entry->getRouteKeyName(), $connected_entity_entry->getRouteKeyName())->toArray())))
                             checked = "checked"
                      @endif > {!! $connected_entity_entry->{$field['attribute']} !!}
                  </label>
                </div>
            </div>
        @endforeach
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
