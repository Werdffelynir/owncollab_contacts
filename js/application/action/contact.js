if(App.namespace) { App.namespace('Action.Contact', function(App) {

    /**
     * @namespace App.Action.Contact
     * @type {*}
     */
    var _ = {
        node:{},
        fieldsTemplate:{}
    };

    /**
     * @namespace App.Action.Contact.init
     */
    _.init = function() {
        var addField = App.query('#add_field');

        if(!addField) return;

        _.node['addField'] = addField;
        _.node['addFieldList'] = App.query('#add_fields_list');


        jQuery(addField).click(function(e){
            jQuery(_.node['addFieldList']).slideToggle(400);
        });
        jQuery(_.node['addFieldList']).click(function(e){
            console.log(e.target);
        });

    };

    return _;

})}