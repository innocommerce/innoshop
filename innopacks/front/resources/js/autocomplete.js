$(function() {
  // Autocomplete plugin
  $.fn.autocomplete = function(option) {
    return this.each(function() {
      this.timer = null;
      this.items = new Array();

      $.extend(this, option);

      $(this).attr('autocomplete', 'off');

      // Focus event
      $(this).on('focus', function() {
        this.request();
      });

      // Blur event
      $(this).on('blur', function() {
        setTimeout(function(object) {
          object.hide();
        }, 200, this);
      });

      // Keydown event
      $(this).on('keydown', function(event) {
        switch(event.keyCode) {
          case 27: // ESC key
            this.hide();
            break;
          default:
            this.request();
            break;
        }
      });

      // Click event handler
      this.click = function(event) {
        event.preventDefault();

        let value = $(event.target).parent().attr('data-value');

        if (value && this.items[value]) {
          this.select(this.items[value]);
        }
      }

      // Show dropdown menu
      this.show = function() {
        var pos = $(this).position();

        $(this).siblings('ul.dropdown-menu').css({
          top: pos.top + $(this).outerHeight(),
          left: pos.left
        });

        $(this).siblings('ul.dropdown-menu').show();
      }

      // Hide dropdown menu
      this.hide = function() {
        $(this).siblings('ul.dropdown-menu').hide();
      }

      // Send request
      this.request = function() {
        clearTimeout(this.timer);

        this.timer = setTimeout(function(object) {
          object.source($(object).val(), $.proxy(object.response, object));
        }, 200, this);
      }

      // Handle response data
      this.response = function(json) {
        let hasFocus = $(this).is(':focus');
        if (!hasFocus) return;

        var html = '';

        if (json.length) {
          // Store all item data
          for (var i = 0; i < json.length; i++) {
            this.items[json[i]['value']] = json[i];
          }

          // Handle items without categories
          for (var i = 0; i < json.length; i++) {
            if (!json[i]['category']) {
              html += '<li data-value="' + json[i]['value'] + '"><a href="#" class="dropdown-item">' + json[i]['label'] + '</a></li>';
            }
          }

          // Get all items with categories
          var category = new Array();

          for (var i = 0; i < json.length; i++) {
            if (json[i]['category']) {
              if (!category[json[i]['category']]) {
                category[json[i]['category']] = new Array();
                category[json[i]['category']]['name'] = json[i]['category'];
                category[json[i]['category']]['item'] = new Array();
              }

              category[json[i]['category']]['item'].push(json[i]);
            }
          }

          // Render category items
          for (var i in category) {
            html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

            for (j = 0; j < category[i]['item'].length; j++) {
              html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
            }
          }
        }

        // Show or hide dropdown menu
        if (html) {
          this.show();
        } else {
          this.hide();
        }

        $(this).siblings('ul.dropdown-menu').html(html);
      }

      // Initialize dropdown menu
      $(this).after('<ul class="dropdown-menu"></ul>');
      $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));
    });
  }
});