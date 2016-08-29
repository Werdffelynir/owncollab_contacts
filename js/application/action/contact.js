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
        _.node['dynamicFields'] = App.query('#ads_dynamic_fields');


        jQuery(addField).click(function(e){
            jQuery(_.node['addFieldList']).slideToggle(400);
        });
        jQuery(_.node['addFieldList']).click(function(e){
            console.log(e.target);
            _.appendField(e.target.textContent, e.target.getAttribute('data-id'));
        });

    };
    _.appendField = function(name, label) {
        var div = document.createElement('div'), html = '';
        html += '<label for="'+label+'">'+name+'</label>';
        html += '<input id="'+label+'" type="text" value="">';

        div.innerHTML = html;
        div.className = 'ads_field';
        _.node['dynamicFields'].appendChild(div);
    };

    return _;

})}




