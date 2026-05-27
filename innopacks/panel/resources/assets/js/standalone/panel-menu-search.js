/**
 * Panel Menu Search + Sidebar Mini Mode.
 * Included via standalone/index.js → panel-standalone.js.
 * Depends on: Vue 3, Element Plus (loaded globally in app layout).
 *
 * Module 1 — Sidebar Toggle: collapsed state (60px mini mode)
 * Module 2 — Mini Popover: hover icon → popup sub-menu on the right
 * Module 3 — Menu Search: Vue el-autocomplete for global menu search
 */
(function (global) {
  "use strict";

  /* ========================================================
   * Module 1: Sidebar Toggle (expanded ↔ mini)
   * ======================================================= */
  function initSidebarToggle() {
    var toggleBtn = document.getElementById("sidebar-toggle");
    var sidebar = document.getElementById("sidebar-box");
    if (!toggleBtn || !sidebar) return;

    var COLLAPSED_KEY = "inno_sidebar_collapsed";
    var iconEl = toggleBtn.querySelector("i");

    function applyState(collapsed, animate) {
      if (animate === false) {
        sidebar.style.transition = "none";
      }

      if (collapsed) {
        sidebar.classList.add("sidebar-collapsed");
        document.body.classList.add("sidebar-collapsed");
        if (iconEl) iconEl.className = "bi bi-chevron-right";
      } else {
        sidebar.classList.remove("sidebar-collapsed");
        document.body.classList.remove("sidebar-collapsed");
        if (iconEl) iconEl.className = "bi bi-chevron-left";
        miniPopover.hide(); // close any open popover
      }

      if (animate === false) {
        // Restore transition after layout is stable
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            sidebar.style.transition = "";
          });
        });
      }
    }

    // Restore saved state (no animation on page load)
    if (localStorage.getItem(COLLAPSED_KEY) === "1") {
      applyState(true, false);
    }

    toggleBtn.addEventListener("click", function () {
      var isCollapsed = sidebar.classList.contains("sidebar-collapsed");
      applyState(!isCollapsed, true);
      localStorage.setItem(COLLAPSED_KEY, isCollapsed ? "0" : "1");
    });
  }

  /* ========================================================
   * Module 2: Mini Popover — hover shows sub-menu on right
   * ======================================================= */
  var miniPopover = (function () {
    var popoverEl = null;
    var hideTimer = null;
    var currentItem = null;

    function getEl() {
      if (popoverEl) return popoverEl;
      popoverEl = document.createElement("div");
      popoverEl.className = "sidebar-mini-popover";
      popoverEl.style.display = "none";
      document.body.appendChild(popoverEl);

      // Mouse enters popover → keep it open
      popoverEl.addEventListener("mouseenter", function () {
        clearTimeout(hideTimer);
      });
      popoverEl.addEventListener("mouseleave", function () {
        scheduleHide();
      });
      return popoverEl;
    }

    function clearHighlight() {
      if (currentItem) {
        currentItem.classList.remove("mini-hover");
        currentItem = null;
      }
    }

    function scheduleHide() {
      hideTimer = setTimeout(function () {
        hide();
      }, 120);
    }

    function hide() {
      var el = getEl();
      el.style.display = "none";
      el.innerHTML = "";
      clearHighlight();
    }

    function show(accordionItem) {
      var sidebar = document.getElementById("sidebar-box");
      if (!sidebar || !sidebar.classList.contains("sidebar-collapsed")) return;

      // Must have children
      if (accordionItem.getAttribute("data-has-children") !== "true") return;

      var collapseEl = accordionItem.querySelector(".accordion-collapse");
      if (!collapseEl) return;

      var navLinks = collapseEl.querySelectorAll("a.nav-link");
      if (navLinks.length === 0) return;

      // Build popover HTML
      var titleEl = accordionItem.querySelector(".accordion-button .menu-text");
      var title = titleEl ? titleEl.textContent : "";

      var html = '<div class="mini-popover-title">' + title + "</div><ul>";
      navLinks.forEach(function (link) {
        var cls = link.classList.contains("active") ? ' class="active"' : "";
        var tgt = link.getAttribute("target") ? ' target="' + link.getAttribute("target") + '"' : "";
        html += '<li><a href="' + link.getAttribute("href") + '"' + cls + tgt + ">" + link.textContent + "</a></li>";
      });
      html += "</ul>";

      var el = getEl();
      el.innerHTML = html;

      // Position: right of the icon row
      var rect = accordionItem.getBoundingClientRect();
      el.style.display = "block";
      el.style.top = rect.top + "px";
      el.style.left = rect.right + "px";

      // Adjust if overflows screen
      requestAnimationFrame(function () {
        if (rect.top + el.offsetHeight > window.innerHeight) {
          el.style.top = Math.max(4, window.innerHeight - el.offsetHeight - 4) + "px";
        }
      });

      // Highlight the icon row
      clearHighlight();
      currentItem = accordionItem;
      currentItem.classList.add("mini-hover");
    }

    function init() {
      var sidebar = document.getElementById("sidebar-box");
      if (!sidebar) return;

      var items = sidebar.querySelectorAll(".accordion-item");
      items.forEach(function (item) {
        item.addEventListener("mouseenter", function () {
          var sb = document.getElementById("sidebar-box");
          if (!sb || !sb.classList.contains("sidebar-collapsed")) return;
          clearTimeout(hideTimer);
          show(item);
        });
        item.addEventListener("mouseleave", function () {
          var sb = document.getElementById("sidebar-box");
          if (!sb || !sb.classList.contains("sidebar-collapsed")) return;
          scheduleHide();
        });
      });
    }

    return { init: init, hide: hide };
  })();

  /* ========================================================
   * Module 3: Menu Search (Vue el-autocomplete)
   * ======================================================= */
  function initMenuSearch() {
    if (typeof Vue === "undefined" || typeof ElementPlus === "undefined") return;

    var container = document.getElementById("panel-menu-search");
    if (!container) return;

    var searchUrl = container.dataset.searchUrl || "";
    var placeholder = container.dataset.placeholder || "Search menus...";

    var app = Vue.createApp({
      template:
        '<el-autocomplete' +
        '  v-model="keyword"' +
        '  :fetch-suggestions="fetchSuggestions"' +
        '  :trigger-on-focus="false"' +
        '  placeholder="' + placeholder + '"' +
        '  popper-class="menu-search-popper"' +
        '  @select="handleSelect"' +
        '  clearable>' +
        '  <template #prefix><i class="bi bi-search"></i></template>' +
        '</el-autocomplete>',
      setup: function () {
        var keyword = Vue.ref("");
        var timeout = null;

        var fetchSuggestions = function (queryString, cb) {
          clearTimeout(timeout);
          if (!queryString || queryString.trim() === "") { cb([]); return; }
          timeout = setTimeout(function () {
            fetch(searchUrl + "?keyword=" + encodeURIComponent(queryString.trim()))
              .then(function (res) { return res.json(); })
              .then(function (data) {
                var items = Array.isArray(data) ? data : [];
                cb(items.map(function (item) { return { value: item.title, url: item.url }; }));
              })
              .catch(function () { cb([]); });
          }, 300);
        };

        var handleSelect = function (item) {
          if (item && item.url) window.location.href = item.url;
        };

        return { keyword: keyword, fetchSuggestions: fetchSuggestions, handleSelect: handleSelect };
      },
    });

    app.use(ElementPlus);
    app.mount(container);
  }

  /* ========================================================
   * Bootstrap
   * ======================================================= */
  document.addEventListener("DOMContentLoaded", function () {
    initSidebarToggle();
    miniPopover.init();
    initMenuSearch();
  });
})(window);
