/**
 * Panel app bootstrap (aligned with front inno-init.js).
 * Attaches window.inno and runs DOM-ready initializers.
 */
import inno from './panel-inno';
import dominantColor from './panel-dominant-color';
import ProductSelector from './panel-product-selector';
import { setupApiHeaders } from './panel-api-setup';
import { panelUI } from './panel-ui';
import fileManager from './panel-file-manager';
import { initEditor } from './panel-editor';
import localeField from './locale-field';

export function initPanelInno() {
  window.inno = inno;
}

export function bindPanelOnReady() {
  window.dominantColor = dominantColor;
  window.inno.fileManagerIframe = fileManager.init;
  window.inno.productSelectorIframe = ProductSelector.init;

  setupApiHeaders();

  panelUI.initTooltips();
  panelUI.initTabNavigation();
  panelUI.initHoverEffects();
  panelUI.initAlerts();
  panelUI.initSidebar();
  panelUI.initDatePickers();
  panelUI.initAIGenerate();

  initEditor();

  inno.getTranslate();
  inno.initSlugFormatting();

  localeField.init();
}
