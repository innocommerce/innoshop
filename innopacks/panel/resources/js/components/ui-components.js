/**
 * UI Components module.
 *
 * This module handles various UI components initialization and interactions.
 */
const UIComponents = {
  /**
   * 初始化工具提示
   */
  initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  },

  /**
   * 初始化选项卡导航
   */
  initTabNavigation() {
    $('.nav-tabs a').on('click', function (e) {
      e.preventDefault();
      $(this).tab('show');
      
      // 更新 URL 中的 hash
      const hash = $(this).attr('href');
      if (hash && hash.startsWith('#')) {
        history.replaceState(null, null, hash);
      }
    });

    // 根据 URL hash 激活对应的选项卡
    if (window.location.hash) {
      $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
    }
  },

  /**
   * 初始化悬停效果
   */
  initHoverEffects() {
    $('.product-card').hover(
      function() {
        $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
      },
      function() {
        $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
      }
    );

    $('.nav-item').hover(
      function() {
        $(this).find('.nav-link').addClass('text-primary');
      },
      function() {
        $(this).find('.nav-link').removeClass('text-primary');
      }
    );
  },

  /**
   * 初始化警告消息
   */
  initAlerts() {
    // 自动关闭警告消息
    $('.alert').each(function() {
      const $alert = $(this);
      if (!$alert.hasClass('alert-permanent')) {
        setTimeout(() => {
          $alert.fadeOut();
        }, 5000);
      }
    });
  },

  /**
   * 初始化侧边栏
   */
  initSidebar() {
    // 侧边栏切换
    $('.sidebar-toggle').on('click', function() {
      $('.sidebar').toggleClass('collapsed');
      $('.main-content').toggleClass('expanded');
    });

    // 侧边栏菜单项点击
    $('.sidebar .nav-link').on('click', function() {
      $('.sidebar .nav-link').removeClass('active');
      $(this).addClass('active');
    });
  },

  /**
   * 初始化所有 UI 组件
   */
  init() {
    this.initTooltips();
    this.initTabNavigation();
    this.initHoverEffects();
    this.initAlerts();
    this.initSidebar();
  }
};

export default UIComponents;