<div @include('crud::inc.field_wrapper_attributes') >

    <h3>{!! $field['label'] !!}</h3>

    @if (isset($field['model']))

        <?php
            $pivot_entries = null;
            if (isset($entry)) {
                $pivot_entries = $entry->{$field['entity']}->keyBy(function($item) {
                    return $item->getKey();
                });
            }
        ?>

        @if(isset($field['pivotFields']))
            <a class="btn btn-default" role="button" data-toggle="collapse" id="trigger-toggle-all" aria-expanded="false">
                <span class="caret"></span> {{ trans('Toggle all') }}
            </a>

            @push('crud_fields_scripts')
                <script>
                    jQuery(document).ready(function($) {
                        $('#trigger-toggle-all').on('click', function () {
                            $('div.collapse').collapse('toggle');
                        });
                    });
                </script>
            @endpush
        @endif

        @foreach ($field['model']::all() as $connected_entity_entry)
            <div>
                <div class="checkbox">
                    <label style="vertical-align: middle;">
                        <input type="checkbox" name="{{ $field['name'] }}[{{ $connected_entity_entry->getKey() }}]" value="{{ $connected_entity_entry->getKey() }}"
                               @if ( (isset($field['value']) && in_array($connected_entity_entry->getKey(), $field['value']->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())) || ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) )
                               checked="checked"
                                @endif
                                />
                        {!! $connected_entity_entry->{$field['attribute']} !!}
                    </label>

                    @if(isset($field['pivotFields']))
                        <a class="btn btn-default btn-xs" role="button" data-toggle="collapse" href="#toggle-pivot-{{ $connected_entity_entry->getKey() }}" aria-expanded="false">
                            <span class="caret"></span>
                        </a>
                    @endif
                </div>

                @if(isset($field['pivotFields']))
                    <div class="collapse" id="toggle-pivot-{{ $connected_entity_entry->getKey() }}">
                        <div class="container-fluid">
                            @foreach(array_chunk($field['pivotFields'], 2, true) as $pivot_chunk)
                                <div class="row">
                                    @foreach ($pivot_chunk as $pivot_field => $pivot_name)
                                        <?php
                                            $pivot_attr = null;
                                            if ($pivot_entries) {
                                                if ($pivot_entries->has($connected_entity_entry->getKey())) {
                                                    $pivot = $pivot_entries->get($connected_entity_entry->getKey())->pivot;
                                                    $pivot_attr = $pivot->getAttribute($pivot_field);
                                                }
                                            }
                                        ?>

                                        <div class="col-sm-6">
                                            <label>{!! $pivot_name !!}</label>
                                            <input type="text" name="{!! $pivot_field !!}[{{ $connected_entity_entry->getKey() }}]" value="{{ $pivot_attr }}" @include('crud::inc.field_attributes') />
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
