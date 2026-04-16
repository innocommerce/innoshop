/**
 * Single webpack entry for panel Vue/global widgets (after vendor Vue + Element Plus + app.js).
 * Source modules stay split in ./standalone/*.js; load order must match dependencies.
 */
import './panel-entity-autocomplete';
import './panel-entity-picker';
import './panel-locale-text';
import './panel-image-input';
import './panel-inno-panel';
import './panel-menu-search';
