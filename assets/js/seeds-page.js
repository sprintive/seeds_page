(function ($, Drupal, once) {
  Drupal.behaviors.seedsPage = {
    attach: function (context, settings) {
      $(
        once(
          "seedsPage",
          ".block-content--type-seeds-modal .field--name-field-seeds-text"
        )
      ).click(function () {
        var paragraph = $(this).parent().clone();
        paragraph.find(".btn-primary.field--name-field-seeds-text").remove();
        var modalWrapper = $('<div id="paragraph-modal"></div>');
        $("body").append(modalWrapper);
        modalWrapper.html(paragraph.clone());
        var myDialog = modalWrapper.dialog({
          autoOpen: true,
          modal: true,
          draggable: false,
          title: "",
          width: "auto",
          dialogClass: "seeds-modal-dialog container",
          close: function () {
            $(this).dialog("destroy");
            modalWrapper.remove();
          },
        });
        modalWrapper.find(".modal-footer").remove();
      });
    },
  };
})(jQuery, Drupal, once);
