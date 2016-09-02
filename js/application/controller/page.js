if(App.namespace){App.namespace('Controller.Page', function(App){

    /**
     * @namespace App.Controller.Page
     */
    var _ = {node: {}};
            
    _.construct = function () {
        App.domLoaded(afterDOMLoaded);
    };


    function afterDOMLoaded () {
        //App.Controller.Page.node['contentBox']
        _.node['contentError'] = App.query('#app-content-error');
        _.node['contentBox'] = App.query('#app-content-dox');
        _.node['contentWrapper'] = App.query('#app-content-wrapper');
        _.node['frontendData'] = App.query('#app-frontend-data');
        _.node['contentInlineError'] = App.query('#app-content-inline-error');

        try{
            App.provide = JSON.parse(_.node['frontendData'].textContent);
            _.node['frontendData'].textContent = '';
            //console.log('App.provide >>> ', App.provide);
        }catch (e) {}

        _.errorLineCloseButtonInit();

        App.Action.Sidebar.init();
        App.Action.List.init();

        //App.Action.Contact.init(App.provide);



    }















    /**
     * @namespace App.Controller.Page.readEvents
     */
    _.readEvents = function(){};


    /**
     * Show red error line with message
     * @namespace App.Controller.Page.errorLine
     * @param text
     */
    _.errorLine = function (text) {
        if(!text)
            _.errorLineClose();
        else {
            _.node['contentInlineError'].style.display = 'block';
            jQuery('.inline_error_content').text(text);
        }
    };

    /**
     * @namespace App.Controller.Page.errorLineClose
     * Hide red error line
     */
    _.errorLineClose = function () {
        _.node['contentInlineError'].style.display = 'none';
    };

    /**
     * @namespace App.Controller.Page.errorLineCloseButtonInit
     * Init button close red error line
     */
    _.errorLineCloseButtonInit = function () {
        jQuery('.icon-close', _.node['contentInlineError']).click(function(event){
            _.errorLineClose();
        });
    };

    /**
     * Blocked page and show error message
     * @namespace App.Controller.Page.errorPage
     * @param title
     * @param text
     */
    _.errorPage = function (title, text) {};

    return _;

})}
