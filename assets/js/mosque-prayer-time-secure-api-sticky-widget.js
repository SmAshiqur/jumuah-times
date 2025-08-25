jQuery(document).ready(function($) {
    $('#prayer-time-toggle-secure-api').on('click', function() {
        $('.prayer-times-body').slideToggle();
        $(this).text(function(_, oldText) {
            return oldText === '▼' ? '▲' : '▼';
        });
    });
});
