import common from "../core/common";

/**
 * Tab navigation component.
 *
 * Handles tab switching and URL updates for tabbed interfaces.
 */
const TabNavigation = {
  /**
   * Initializes tab navigation functionality.
   */
  init: () => {
    $("a[data-bs-target], button[data-bs-target]").on("click", function () {
      const dataBsTarget = $(this).attr("data-bs-target");
      if ($(this).hasClass("nav-link")) {
        const url = new URL(window.location.href);
        url.searchParams.set("tab", dataBsTarget.replace("#", ""));
        window.history.pushState({}, "", url.toString());
      }
    });

    const tab = common.getQueryString("tab");
    if (tab) {
      const tabButton = $(`button[data-bs-target="#${tab}"]`);
      const tabLink = $(`a[data-bs-target="#${tab}"]`);
      if (tabButton.length) {
        tabButton[0].click();
      } else if (tabLink.length) {
        tabLink[0].click();
      }
    }
  },
};

export default TabNavigation;