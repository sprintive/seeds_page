(function($, Drupal) {
  Drupal.behaviors.seedsTabs = {
    attach: function(context, settings) {
      $(".seeds-paragraph-block .tabs>.field--item:eq(0)").addClass("seeds-expand");
	  // Turn tabs into a slider.
	  $(".seeds-paragraph-block .tabs-wrapper").slick({
		variableWidth: true,
		infinite: false,
		arrows:false,
		swipeToSlide: true,
		draggable: true,
		swipe: true,
		touchThreshold: 20,
    touchMove: true,
    rtl: $('html').attr('dir') == 'rtl'?true:false
	  });
	$()
      $(".seeds-paragraph-block .tabs .tabs-wrapper li").once('seedsTabs').each(function() {
        $(this).click(function() {
          $(this).parents(".tabs").find(">.field--item").removeClass("seeds-expand");
          $(this).parents(".tabs").find(`>.field--item:eq("${$(this).index()}")`).addClass(
            "seeds-expand"
          );
        });
      });
    }
  };
})(jQuery, Drupal);
