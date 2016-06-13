(function (Drupal, $) {
    'use strict';

    Drupal.behaviors.clickableTableRow = {
        attach: function (context, settings) {
            $('tr[data-href]').on("click", function() {
                document.location = $(this).data('href');
            });
        }
    };

})(Drupal, jQuery);
