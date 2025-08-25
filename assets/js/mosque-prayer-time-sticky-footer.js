// script.js
jQuery(document).ready(function($) {
    $('#prayer-time-header').on('click', function() {
        $('#prayer-time-content').slideToggle();
        $('#prayer-time-toggle').text(function(_, oldText) {
            return oldText === '▼' ? '▲' : '▼';
        });
    });
});