'use strict';
(function($) {
    $(document).ready(function() {

        /**
         * Disable submit/next buttons for invalid input patterns.
         *
         * @since 1.2.1
         */
        $('body').on('change', 'form[id^="gform_"] input[pattern]', function() {
            if ($(this).is(':invalid')) {
                $('.gform_button, .gform_next_button').attr('disabled', true);
            } else {
                $('.gform_button, .gform_next_button').attr('disabled', false);
            }
        });

    });
}(jQuery));
