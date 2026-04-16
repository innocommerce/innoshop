/**
 * LocaleModalHelper - Shared locale modal translate/fill logic.
 * Works with both Vue reactive state and plain DOM inputs.
 *
 * Usage (DOM / Blade pages):
 *   const helper = new LocaleModalHelper({
 *     modalId: 'groupEditModal',
 *     inputPrefix: 'group-name',
 *   });
 *
 * Usage (Vue reactive):
 *   const helper = new LocaleModalHelper({
 *     getLocaleValue: (code) => valueDialog.value.form.name[code] || '',
 *     setLocaleValue: (code, val) => { valueDialog.value.form.name[code] = val; },
 *   });
 *
 * Auto-binding:
 *   If modalId is provided, automatically binds [data-lm-translate] and [data-lm-fill]
 *   buttons inside the modal element.
 */
class LocaleModalHelper {
  constructor(config) {
    var defaultConfig = window._lmConfig || {};
    this.defaultLocale = config.defaultLocale || defaultConfig.defaultLocale;
    this.locales = config.locales || defaultConfig.locales || [];
    this.otherLocales = this.locales.filter(function(l) { return l.code !== this.defaultLocale; }.bind(this));
    this.hasTranslator = config.hasTranslator !== undefined ? config.hasTranslator : defaultConfig.hasTranslator;
    this.translateUrl = config.translateUrl || defaultConfig.translateUrl;

    var prefix = config.inputPrefix || '';
    this.getLocaleValue = config.getLocaleValue || (prefix
      ? function(code) { var el = document.getElementById(prefix + '-' + code); return el ? el.value : ''; }
      : function() { return ''; });
    this.setLocaleValue = config.setLocaleValue || (prefix
      ? function(code, val) { var el = document.getElementById(prefix + '-' + code); if (el) el.value = val; }
      : function() {});

    this.messages = {
      sourceEmpty: (config.messages && config.messages.sourceEmpty) || (defaultConfig.messages && defaultConfig.messages.sourceEmpty) || 'Source is empty',
      noEmpty: (config.messages && config.messages.noEmpty) || (defaultConfig.messages && defaultConfig.messages.noEmpty) || 'No empty languages',
      translated: (config.messages && config.messages.translated) || (defaultConfig.messages && defaultConfig.messages.translated) || 'Translated :count',
      copied: (config.messages && config.messages.copied) || (defaultConfig.messages && defaultConfig.messages.copied) || 'Copied :count',
    };

    if (config.modalId) {
      this.bindToModal(config.modalId);
    }
  }

  bindToModal(modalId) {
    var self = this;
    var modal = document.getElementById(modalId);
    if (!modal) return;

    modal.querySelectorAll('[data-lm-translate]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        self.translate(btn.dataset.lmTranslate, btn);
      });
    });

    var fillBtn = modal.querySelector('[data-lm-fill]');
    if (fillBtn) {
      fillBtn.addEventListener('click', function() {
        self.fillEmpty();
      });
    }
  }

  translate(targetLocale, btn) {
    var self = this;
    var sourceText = (this.getLocaleValue(this.defaultLocale) || '').trim();
    if (!sourceText) {
      layer.msg(this.messages.sourceEmpty, {icon: 2});
      return;
    }

    var $btn = $(btn);
    if ($btn.prop('disabled')) return;
    $btn.prop('disabled', true);
    var originalHtml = $btn.html();
    $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');

    axios.post(this.translateUrl, {
      source: this.defaultLocale,
      target: targetLocale,
      text: sourceText,
    }, { headers: { 'X-Skip-Loading': true } }).then(function(res) {
      res.data.forEach(function(item) {
        self.setLocaleValue(targetLocale, item.result);
      });
    }).catch(function(err) {
      layer.msg((err.response && err.response.data && err.response.data.message) || err.message, {icon: 2});
    }).finally(function() {
      $btn.prop('disabled', false);
      $btn.html(originalHtml);
    });
  }

  fillEmpty() {
    var self = this;
    var primaryText = (this.getLocaleValue(this.defaultLocale) || '').trim();
    if (!primaryText) {
      layer.msg(this.messages.sourceEmpty, {icon: 2});
      return;
    }

    var emptyLocales = this.otherLocales.filter(function(l) {
      var val = (self.getLocaleValue(l.code) || '').trim();
      return !val;
    });

    if (emptyLocales.length === 0) {
      layer.msg(this.messages.noEmpty, {icon: 0});
      return;
    }

    if (this.hasTranslator) {
      var completed = 0;
      var total = emptyLocales.length;
      emptyLocales.forEach(function(locale) {
        axios.post(self.translateUrl, {
          source: self.defaultLocale,
          target: locale.code,
          text: primaryText,
        }, { headers: { 'X-Skip-Loading': true } }).then(function(res) {
          res.data.forEach(function(item) {
            self.setLocaleValue(locale.code, item.result);
          });
        }).catch(function() {
          self.setLocaleValue(locale.code, primaryText);
        }).finally(function() {
          completed++;
          if (completed === total) {
            layer.msg(self.messages.translated.replace(':count', completed), {icon: 1});
          }
        });
      });
    } else {
      emptyLocales.forEach(function(locale) {
        self.setLocaleValue(locale.code, primaryText);
      });
      layer.msg(this.messages.copied.replace(':count', emptyLocales.length), {icon: 1});
    }
  }
}

window.LocaleModalHelper = LocaleModalHelper;
