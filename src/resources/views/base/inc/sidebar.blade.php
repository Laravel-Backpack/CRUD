@if (backpack_auth()->check())
    {{-- Left side column. contains the sidebar  --}}
    <div class="{{ config('backpack.base.sidebar_class') }}">
      {{-- sidebar: style can be found in sidebar.less  --}}
      <nav class="sidebar-nav overflow-hidden">
        {{-- sidebar menu: : style can be found in sidebar.less  --}}
        <ul class="nav">
          {{-- <li class="nav-title">{{ trans('backpack::base.administration') }}</li>  --}}
          {{-- ================================================  --}}
          {{-- ==== Recommended place for admin menu items ====  --}}
          {{-- ================================================  --}}

          @include(backpack_view('inc.sidebar_content'))

          {{-- =======================================  --}}
          {{-- <li class="divider"></li>  --}}
          {{-- <li class="nav-title">Entries</li>  --}}
        </ul>
      </nav>
      {{-- /.sidebar  --}}
    </div>
@endif

@push('before_scripts')
  <script type="text/javascript">
    /* Recover sidebar state */
    if (Boolean(sessionStorage.getItem('sidebar-collapsed'))) {
      var body = document.getElementsByTagName('body')[0];
      body.className = body.className.replace('sidebar-lg-show', '');
    }

    /* Store sidebar state */
    var navbarToggler = document.getElementsByClassName("navbar-toggler");
    for (var i = 0; i < navbarToggler.length; i++) {
      navbarToggler[i].addEventListener('click', function(event) {
        event.preventDefault();
        if (Boolean(sessionStorage.getItem('sidebar-collapsed'))) {
          sessionStorage.setItem('sidebar-collapsed', '');
        } else {
          sessionStorage.setItem('sidebar-collapsed', '1');
        }
      });
    }
  </script>
@endpush

@push('after_scripts')
  <script>
  </script>
@endpush
