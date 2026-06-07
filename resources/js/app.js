/**
 * Charge les dépendances JS du projet (jQuery, Bootstrap, axios, datepicker).
 */

require('./bootstrap');

// Vue n'est pas utilisé : l'interface repose sur jQuery + Bootstrap.
// L'ancien `new Vue({ el: '#app' })` provoquait « Vue is not defined »
// car le chargement de Vue (window.Vue = require('vue')) était désactivé.
