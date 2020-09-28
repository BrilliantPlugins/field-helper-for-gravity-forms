'use strict';
(function($) {

    /**
     * Toggle checkbox fields.
     *
     * @param {Object} input
     */
    function toggleCheckboxFields(input) {
        var inputId = input.data('input'),
            returnFormat = input.val(),
            simple = $('#gaddon-setting-row-' + inputId),
            fields = $('[id^="gaddon-setting-row-' + inputId + '_"]');

        if ('combined' === returnFormat) {
            fields.hide();
            simple.show();
        } else {
            fields.show();
            simple.hide();
        }
    }

    $(document).ready(function() {

        /**
         * Set initial state based on checked field.
         */
        $('.checkbox-return-format:checked').each( function() {
            toggleCheckboxFields($(this));
        });

        /**
         * Watch checkbox return format radio for changes.
         */
        $('.checkbox-return-format').on('change', function() {
            toggleCheckboxFields($(this));
        });

    });
}(jQuery));
