if(App.namespace){App.namespace('Controller.Page', function(App){

    /**
     * @namespace App.Controller.Page
     */
    var _ = {node: {}};
            
    _.construct = function () {
        App.domLoaded(afterDOMLoaded);
    };


    function afterDOMLoaded () {

        _.node['contentError'] = App.query('#app-content-error');
        _.node['contentInlineError'] = App.query('#app-content-inline-error');
        _.errorLineCloseButtonInit();

        App.Action.Sidebar.init();
        App.Action.Editable.init();

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
