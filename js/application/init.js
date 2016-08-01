

var App = new NamespaceApplication({
    debug: true,
    constructsType: false,
    name: 'owncollab_contacts',
    url: OC.generateUrl('/apps/owncollab_contacts'),
    urlBase: OC.getProtocol() + '://' + OC.getHost(),
    getUrl: function(link){
        link = link ? '/' + link : '';
        return OC.getProtocol() + '://' + OC.getHost() + OC.generateUrl('/apps/owncollab_contacts') + link;
    },
    urlScript: '/apps/owncollab_contacts/js/',
    host: OC.getHost(),
    locale: OC.getLocale(),
    protocol: OC.getProtocol(),
    isAdmin: null,
    corpotoken: null,
    requesttoken: oc_requesttoken ? encodeURIComponent(oc_requesttoken) : null,
    uid: oc_current_user ? encodeURIComponent(oc_current_user) : null
});


/**
 * date.js - https://code.google.com/archive/p/datejs/wikis/APIDocumentation.wiki
 */

App.require('libs', [
    App.urlScript + 'libs/util.js',
    App.urlScript + 'libs/linker.js'
], initLibrary, initError);


App.require('dependence', [
    App.urlScript + 'application/extension/cache.js',
    App.urlScript + 'application/action/api.js',
    App.urlScript + 'application/controller/page.js'

], initDependence, initError);



App.requireStart('libs');



function initError(error){
    console.error('initError' , error);
}



function initLibrary(list){
    App.requireStart('dependence');
}


// start
function initDependence(list){
    console.log('Application start!');

    App.Controller.Page.construct();
}


