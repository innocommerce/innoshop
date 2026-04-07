/**
 * Panel main webpack entry — bundles everything under ./app/ into build/panel/js/app.js.
 * Vue widgets: ./standalone/*.js → build/panel/js/panel-standalone.js (see layouts).
 */
import './app/panel-http';
import './app/panel-form-validation';
import './app/panel-autocomplete';
import { initPanelInno, bindPanelOnReady } from './app/panel-init';

initPanelInno();

$(function () {
  bindPanelOnReady();
});
