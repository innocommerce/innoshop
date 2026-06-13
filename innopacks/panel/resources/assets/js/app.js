/**
 * Panel main entry — bundles core panel logic + Vue standalone widgets.
 * Standalone modules guard with `if (typeof Vue === "undefined") return;`
 * so they are no-ops on pages that don't load Vue (login, blank, etc.).
 */
import './app/panel-http';
import './app/panel-form-validation';
import './app/panel-autocomplete';
import { initPanelInno, bindPanelOnReady } from './app/panel-init';
import { setupApiHeaders } from './app/panel-api-setup';
import './standalone/panel-entity-autocomplete';
import './standalone/panel-entity-picker';
import './standalone/panel-locale-text';
import './standalone/panel-image-input';
import './standalone/panel-inno-panel';
import './standalone/panel-menu-search';

initPanelInno();
setupApiHeaders();

$(function () {
  bindPanelOnReady();
});
