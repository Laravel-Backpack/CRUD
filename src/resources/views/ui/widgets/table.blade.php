@php
    $widget['wrapper']['class'] = $widget['wrapper']['class'] ?? ($widget['wrapperClass'] ?? 'col-sm-6 col-md-4');
@endphp

@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))
<div class="card">
    @if (isset($widget['content']['header']))
        <div class="card-header">
            <div class="card-title mb-0">{!! $widget['content']['header'] !!}</div>
        </div>
    @endif
    <div class="card-table table-responsive" @isset($widget['height']) style="height: {{ $widget['height'] }}"  @endisset>
        <table class="{{ $widget['class'] ?? 'table' }}">
            @if ($widget['content']['thead'])
                <thead>
                    <tr>
                        @foreach ($widget['content']['thead'] as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @foreach ($widget['content']['tbody'] as $tr)
                    <tr>
                        @foreach ($tr as $td)
                            @if (is_array($td))
                                <td>
                                    @include('crud::columns.text', ['column' => $td])
                                </td>
                            @else
                                <td>{{ $td }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    @foreach ($widget['content']['tfoot'] as $td)
                        <td>{{ $td }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>

    </div>
</div>
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))
