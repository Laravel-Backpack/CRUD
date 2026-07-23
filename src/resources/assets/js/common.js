// Third-party jQuery plugins (select2, jQuery UI autocomplete) still use $.ajax() internally.
// Keep this global CSRF header until jQuery is fully removed as a dependency.
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    }
});

// Enable deep link to tab
document.querySelectorAll('.nav-tabs a').forEach(e => {
    if(e.dataset.name === location.hash.substring(1)) (new bootstrap.Tab(e)).show();
    e.addEventListener('click', () => location.hash = e.dataset.name);
});
