if(App.namespace) { App.namespace('Action.Sidebar', function(App) {

    /**
     * @namespace App.Action.Sidebar
     * @type {*}
     */
    var _ = {
        node:{}
    };

    /**
     * @namespace App.Action.Sidebar.init
     */
    _.init = function () {

        jQuery('.add_vcontact').click(onAddVCard);

    };

    function onAddVCard (event) {
        var target = event.target;
        var vc_item = App.query('.vcontact_item', target.parentNode);
        var vc_form = App.query('form', vc_item);

        vc_item.style.display = 'block';

        console.log(target, vc_item, vc_form);

        vc_form.addEventListener('submit', onVCardSubmit);
    }

    function onVCardSubmit (event) {
        event.preventDefault();
        var target = event.target;
        var formDataObj = Util.formData(target, true);
        target.parentNode.style.display = 'block';

        for (key in formDataObj) {

            console.log('before>>>', formDataObj);

            if(formDataObj[key].length > 2){
                App.Action.Api.request('addcontacts', function(response){

                    console.log('response>>>', response);
                    if(response['error']) {

                    } else {

                    }


                }, {
                    key: key,
                    value: formDataObj[key]
                });
                break;
            }
        }

    }

    return _;

})}