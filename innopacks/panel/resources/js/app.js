// Import core modules
import axios from 'axios';
window.axios = axios;
import config from './core/config.js';
import utils from './core/utils.js';
import common from './core/common.js';
import dominantColor from './core/dominant-color.js';
import http from './core/http.js';

// Import UI components
import tabNavigation from './components/tab-navigation.js';
import hoverEffects from './components/hover-effects.js';
import datePickers from './components/date-pickers.js';
import uiComponents from './components/ui-components.js';
import formValidation from './components/form-validation.js';
import autocomplete from './components/autocomplete.js';

// Import features
import aiGenerate from './features/ai-generate.js';
import productSelector from './features/product-selector.js';
import fileManagerIframe from './features/file-manager-iframe.js';

$(function() {
  // Initialize core modules
  http.init();
  utils.setupApiHeaders();

  // Initialize UI components
  tabNavigation.init();
  hoverEffects.init();
  datePickers.init();
  uiComponents.init();
  formValidation.init();
  autocomplete.init();

  // Initialize features
  aiGenerate.init();
  productSelector.init();
  fileManagerIframe.init();

  // Make modules globally available
  window.axios = axios;
  window.dominantColor = dominantColor;
  window.common = common;
  window.fileManagerIframe = fileManagerIframe;
  window.productSelectorIframe = productSelector;
  window.Config = config;
  window.Utils = utils;
  
  // Add fileManagerIframe method to common object
  common.fileManagerIframe = fileManagerIframe.open;
  
  window.inno = common;

  common.getTranslate();
});
