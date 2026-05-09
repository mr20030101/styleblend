// jQuery is loaded via CDN script tag in the layout head.
// Set CSRF token for all AJAX requests.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
    }
});
