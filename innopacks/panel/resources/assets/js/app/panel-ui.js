import inno from './panel-inno';

export const panelUI = {
  initTooltips: () => {
    $('[data-bs-toggle="tooltip"]').tooltip();
  },

  initTabNavigation: () => {
    // Handle tabs with data-target (internal tabs, don't update URL)
    $("button[data-target]").on("click", function(e) {
      const $tab = $(this);
      
      // Skip if also has data-bs-target (use standard Bootstrap behavior)
      if ($tab.attr("data-bs-target")) {
        return;
      }
      
      e.preventDefault();
      e.stopPropagation();
      
      const target = $tab.data('target');
      if (!target) return;
      
      const $container = $tab.closest('.nav-tabs');
      const $tabContent = $container.siblings('.tab-content');
      let $panes = $tabContent.find('.tab-pane');
      
      if ($panes.length === 0) {
        $panes = $container.parent().find('.tab-content .tab-pane');
      }
      
      // Switch tabs
      $container.find('button[data-target]').removeClass('active').attr('aria-selected', 'false');
      $tab.addClass('active').attr('aria-selected', 'true');
      
      if ($panes.length > 0) {
        $panes.removeClass('show active');
        $(target).addClass('show active');
      }
    });

    // Handle main navigation tabs (update URL) - only for data-bs-target
    $("a[data-bs-target], button[data-bs-target]").on("click", function () {
      const $this = $(this);

      // Skip modal triggers (handled by Bootstrap)
      if ($this.attr("data-bs-toggle") === "modal") {
        return;
      }

      // Skip if has data-target but no data-bs-target (handled above)
      if ($this.attr("data-target") && !$this.attr("data-bs-target")) {
        return;
      }
      
      const dataBsTarget = $this.attr("data-bs-target");
      if ($this.hasClass("nav-link")) {
        const url = new URL(window.location.href);
        url.searchParams.set("tab", dataBsTarget.replace("#", ""));
        window.history.pushState({}, "", url.toString());
        
        // Update header if exists (for settings page)
        const $header = $('.setting-header');
        if ($header.length) {
          $header.text($this.text().trim());
        }
      }
    });

    // Handle URL parameters on page load
    const tab = inno.getQueryString("tab");
    if (tab) {
      const tabTarget = `#tab-setting-${tab}`;
      const $tabLink = $(`.settings-nav a[data-bs-target="${tabTarget}"]`);
      const tabButton = $(`button[data-bs-target="#${tab}"]`);
      const tabLink = $(`a[data-bs-target="#${tab}"]`);
      
      if ($tabLink.length) {
        // Settings page tab
        $('.settings-nav a').removeClass('active');
        $('.tab-pane').removeClass('show active');
        $tabLink.addClass('active');
        $(tabTarget).addClass('show active');
        $('.setting-header').text($tabLink.text().trim());
      } else if (tabButton.length) {
        tabButton[0].click();
      } else if (tabLink.length) {
        tabLink[0].click();
      }
    } else if ($('.settings-nav').length > 0) {
      // Settings page: ensure first tab is active
      const $firstTab = $('.settings-nav a').first();
      if ($firstTab.length && !$firstTab.hasClass('active')) {
        $firstTab.click();
      }
    }
    
    // Handle browser back/forward for settings page
    if ($('.settings-nav').length > 0) {
      window.addEventListener('popstate', function() {
        const tabParam = inno.getQueryString("tab");
        if (tabParam) {
          const tabTarget = `#tab-setting-${tabParam}`;
          const $tabLink = $(`.settings-nav a[data-bs-target="${tabTarget}"]`);
          if ($tabLink.length) {
            $('.settings-nav a').removeClass('active');
            $('.tab-pane').removeClass('show active');
            $tabLink.addClass('active');
            $(tabTarget).addClass('show active');
            $('.setting-header').text($tabLink.text().trim());
            
            // Initialize internal tabs in the active pane
            initInternalTabs($(tabTarget));
          }
        }
      });
    }
    
    // Initialize internal tabs on page load
    function initInternalTabs($container) {
      if (!$container || $container.length === 0) {
        $container = $('.tab-pane.active');
      }
      
      $container.find('.nav-tabs').each(function() {
        const $navTabs = $(this);
        // Only handle tabs with data-target (not data-bs-target)
        const $tabs = $navTabs.find('button[data-target]').not('[data-bs-target]');
        const $tabContent = $navTabs.siblings('.tab-content');
        let $panes = $tabContent.find('.tab-pane');
        
        if ($panes.length === 0) {
          $panes = $navTabs.parent().find('.tab-content .tab-pane');
        }
        
        if ($tabs.length > 0) {
          // Activate first tab if none is active
          const $activeTab = $tabs.filter('.active');
          if ($activeTab.length === 0) {
            const $firstTab = $tabs.first();
            const firstTarget = $firstTab.data('target');
            
            $firstTab.addClass('active').attr('aria-selected', 'true');
            if (firstTarget && $(firstTarget).length) {
              $panes.removeClass('show active');
              $(firstTarget).addClass('show active');
            } else if ($panes.length > 0) {
              $panes.first().addClass('show active');
            }
          }
        }
      });
    }
    
    // Initialize internal tabs for active tab pane
    if ($('.settings-nav').length > 0) {
      initInternalTabs();
    }
  },

  initHoverEffects: () => {
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

  initAlerts: () => {
    $(document).on("click", ".is-alert .btn-close", function () {
      let top = 70;
      $(".is-alert").each(function () {
        $(this).animate({ top }, 100);
        top += $(this).outerHeight() + 10;
      });
    });
  },

  initSidebar: () => {
    $(document).on("click", ".mb-menu", function () {
      $(".sidebar-box").toggleClass("active");
    });

    $(".sidebar-box").on("click", function (e) {
      if (!$(e.target).parents(".sidebar-body").length) {
        $(".sidebar-box").removeClass("active");
      }
    });
  },

  initDatePickers: () => {
    $(document).on("focus", ".date input, .datetime input, .time input", function (event) {
      if (!$(this).prop("id")) {
        $(this).prop("id", Math.random().toString(36).substring(2));
      }

      $(this).attr("autocomplete", "off");

      laydate.render({
        elem: "#" + $(this).prop("id"),
        type: $(this).parent().hasClass("date")
          ? "date"
          : $(this).parent().hasClass("datetime")
          ? "datetime"
          : "time",
        trigger: "click",
        lang: $("html").prop("lang") == "zh-cn" ? "cn" : "en",
      });
    });
  },

  initAIGenerate: () => {
    $(document).on("click", ".ai-generate", function (e) {
      // Find the form-row containing the current button
      const $row = $(this).closest(".form-row");
      // Find input or textarea within the row
      const $input = $row.find("input[data-column], textarea[data-column]");
      if ($input.length === 0) {
        layer.msg('Input field not found', { icon: 2 });
        return;
      }
      // Get field name, language, and current value
      const column = $input.data('column');
      const lang = $input.data('lang');
      const name = $input.attr('name');
      const value = $input.val();

      // Assemble request data
      const formData = {
        column: column,
        lang: lang,
        name: name,
        value: value,
        // Add product_id and other parameters as needed
      };

      layer.load(2, { shade: [0.3, "#fff"] });
      axios
        .post(urls.panel_ai, formData, {})
        .then(function (res) {
          if (res.data && res.data.generated_text) {
            $input.val(res.data.generated_text);
          } else if (res.data && res.data.message) {
            $input.val(res.data.message);
          }
        })
        .catch(function (err) {
          layer.msg(err.response?.data?.message || 'AI generation failed, please try again', { icon: 2 });
        })
        .finally(function () {
          layer.closeAll("loading");
        });
    });
  }
};
