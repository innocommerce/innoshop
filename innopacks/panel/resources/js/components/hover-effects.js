/**
 * Hover effects component.
 *
 * Adds hover animations to various UI elements.
 */
const HoverEffects = {
  /**
   * Initializes hover effects for product cards and navigation items.
   */
  init: () => {
    $(".product-item-card")
      .mouseenter(function () {
        $(this)
          .css("transform", "translateY(-2%)")
          .removeClass("shadow-sm")
          .addClass("shadow-lg");
      })
      .mouseleave(function () {
        $(this)
          .css("transform", "translateY(0)")
          .removeClass("shadow-lg")
          .addClass("shadow-sm");
      });

    $(".plugin-market-nav-item")
      .mouseenter(function () {
        $(this).addClass("panel-item-hover");
      })
      .mouseleave(function () {
        $(this).removeClass("panel-item-hover");
      });
  },
};

export default HoverEffects;