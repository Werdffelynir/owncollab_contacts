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

        if(vc_item.style.display == 'block') {
            vc_item.style.display = 'none';
            return;
        }
        vc_item.style.display = 'block';
        var vc_form = App.query('form', vc_item);
        //console.log(target, vc_item, vc_form);

        vc_form.addEventListener('submit', onVCardSubmit);
    }

    function onVCardSubmit (event) {
        event.preventDefault();
        var target = event.target;
        var formDataObj = Util.formData(target, true);
        var callBtn = App.query('.add_vcontact', target.parentNode.parentNode);
        target.parentNode.style.display = 'block';

        jQuery(callBtn).removeClass('ico_add');
        jQuery(callBtn).addClass('ico_loader');

        for (key in formDataObj) {
            //console.log('before>>>', formDataObj);
            if(formDataObj[key].length > 2){
                App.Action.Api.request('addcontacts', function(response){
                    //console.log('response>>>', response);
                    if(response['error']) {} else {
                        //target.parentNode.style.display = 'none';
                        jQuery(callBtn).removeClass('ico_loader');
                        jQuery(callBtn).addClass('ico_add');
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