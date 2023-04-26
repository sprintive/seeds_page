(function ($, Drupal, once) {
  Drupal.behaviors.seedsAccordion = {
    attach: function (context, settings) {
      $(
        once("seedsAccordion", ".paragraph--view-mode-accordion .title-wrapper")
      ).on("click", function () {
        $(this).toggleClass("active").next().slideToggle();
      });
    },
  };
})(jQuery, Drupal, once);
