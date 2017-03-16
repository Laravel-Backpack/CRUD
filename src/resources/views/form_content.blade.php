@if ($crud->model->translationEnabled())
<input type="hidden" name="locale" value={{ $crud->request->input('locale')?$crud->request->input('locale'):App::getLocale() }}>
@endif

<div class="row">
  <div class="{{ $crud->sideBoxesEnabled() ? 'col-md-12' : 'col-md-8 col-md-offset-2' }}">
    <!-- Default box -->
    @if ($crud->hasAccess('list'))
      <a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span class="text-lowercase">{{ $crud->entity_name_plural }}</span></a><br><br>
    @endif

        @if ($crud->model->translationEnabled() && !empty($entry))
            <!-- Single button -->
            <div class="pull-right" style="margin-bottom: 15px">
                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Language: {{ $crud->model->getAvailableLocales()[$crud->request->input('locale')?$crud->request->input('locale'):App::getLocale()] }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                    <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ $locale }}</a></li>
                @endforeach
                </ul>
            </div>
        @endif

    @include('crud::inc.grouped_errors')
  </div>
</div>

<div class="row">
  <div class="col-md-8 {{ $crud->sideBoxesEnabled() ? '' : 'col-md-offset-2' }}">
    @foreach ($crud->getBoxes('content') as $k => $box)
      <div class="box {{ $crud->getBoxOptions($box)['class'] }}">
        <div class="box-header with-border">
          <h3 class="box-title">
            {{ $box }}
          </h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-{{ $crud->getBoxOptions($box)['collapsed'] ? 'plus' : 'minus' }}"></i></button>
          </div>
        </div>
        <div class="box-body row">
          {{-- See if we're using tabs --}}
          @if ($crud->boxHasTabs($box))
              @include('crud::inc.show_tabbed_fields', ['box' => $box])
          @else
              @include('crud::inc.show_fields', ['fields' => $crud->getBoxFields($box)])
          @endif
        </div>
      </div>
    @endforeach
  </div>

  @if ($crud->sideBoxesEnabled())
    <div class="col-md-4">
      @foreach ($crud->getBoxes('side') as $k => $box)
        <div class="box {{ $crud->getBoxOptions($box)['class'] }}">
          <div class="box-header">
            <h3 class="box-title">{{ $box }}</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-{{ $crud->getBoxOptions($box)['collapsed'] ? 'plus' : 'minus' }}"></i></button>
            </div>
          </div>
          <div class="box-body">
            @include('crud::inc.show_fields', ['fields' => $crud->getBoxFields($box)])
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

<div class="row">
  <div class="{{ $crud->sideBoxesEnabled() ? 'col-md-12' : 'col-md-8 col-md-offset-2' }}">
    @include('crud::inc.form_save_buttons')
  </div>
</div>
