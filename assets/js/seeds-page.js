(function($, Drupal) {
  Drupal.behaviors.seedsPage = {
    attach: function(context, settings) {
      $(".block-content--type-seeds-modal .field--name-field-seeds-text")
        .once("seedsPage")
        .click(function() {
          var paragraph = $(this).next();
          var modalWrapper = $('<div id="paragraph-modal"></div>');
          $("body").append(modalWrapper);
          modalWrapper.html(paragraph.clone());
          var myDialog = Drupal.dialog(modalWrapper, {
            resizable: false,
            title: "",
            width: "auto",
            close: function() {
              $(this).dialog("destroy");
              modalWrapper.remove();
            }
          });
          myDialog.showModal();
          modalWrapper.find(".modal-footer").remove();
        });

        $('.paragraph--view-mode-accordion .title-wrapper').on('click', function () {
          $(this).next().slideToggle();
        });
    }
  };
})(jQuery, Drupal);
