<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
	<h4 class="modal-title">@yield('title')</h4>
</div>
<div class="modal-body">
	@yield('content')
</div>

@yield('after_styles')
@yield('after_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
<script src="{{ asset('vendor/backpack/crud/js/ajax.js') }}"></script>