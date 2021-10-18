(function($, Drupal) {
    Drupal.behaviors.seedsAccordion = {
        attach: function(context, settings) {
            $(".paragraph--view-mode-accordion .title-wrapper").once('seedsAccordion').on(
                "click",
                function() {
                    $(this)
                        .toggleClass('active')
                        .next()
                        .slideToggle();
                }
            );
        }
    };
})(jQuery, Drupal);
