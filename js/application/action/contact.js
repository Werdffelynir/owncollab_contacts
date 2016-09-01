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

    };

    _.initAfterLoaded = function() {
        var addField = App.query('#add_field');
        if(!addField)
            return;

        _.node['addField'] = addField;
        _.node['addFieldList'] = App.query('#add_fields_list');
        _.node['avatar'] = App.query('div.ads_avatar');
        _.node['dynamicFields'] = App.query('#ads_dynamic_fields');
        _.node['contentBox'] = App.query('#app-content-dox');
        _.node['contentBoxClose'] = App.query('#app-content-dox-close');

        // actions
        jQuery(addField).click(function(e){
            jQuery(_.node['addFieldList']).toggle();
        });

        jQuery(_.node['addFieldList']).click(function(e){
            _.appendField(e.target.textContent, e.target.getAttribute('data-id'));
            e.target.parentNode.removeChild(e.target);
            jQuery(_.node['addFieldList']).toggle();
        });

        jQuery(_.node['contentBoxClose']).click(_.close);
        jQuery('#ads_btn_save').click(_.actionSave);
        jQuery('#ads_btn_delete').click(_.actionDelete);
    };

    /**
     *
     * @namespace App.Action.Contact.display
     * @param book
     * @param contact
     */
    _.display = function(book, contact, uid) {

        var contentBox = App.Controller.Page.node['contentBox'],
            contentWrapper = App.Controller.Page.node['contentWrapper'],
            loader = Util.createElement('div', {class: 'ico_loader contact_loader'}, '&nbsp;');

        contentWrapper.style.display = 'none';
        contentBox.style.display = 'block';
        contentBox.appendChild(loader);

        App.Action.Api.request('getcontact',function(data){
            contentBox.innerHTML = data;

            _.initAfterLoaded();
            _.colorByUid(uid, _.node['avatar']);
        },{book: book, contact: contact});
    };

    _.close = function() {
        var contentBox = App.Controller.Page.node['contentBox'],
            contentWrapper = App.Controller.Page.node['contentWrapper'];

        contentWrapper.style.display = 'block';
        contentBox.style.display = 'none';
        contentBox.textContent = '';
    };


    /**
     * Contact view
     * @param name
     * @param label
     */
    _.appendField = function(label, name) {
        var div = document.createElement('div'), html = '';
        html += '<label for="find_'+name+'">'+label+'</label>';
        html += '<input id="find_'+name+'" name="'+name+'" type="text" value="">';

        div.innerHTML = html;
        div.className = 'ads_field';
        _.node['dynamicFields'].appendChild(div);
    };


    _.actionSave = function() {

        var id_contact = false,
            id_book = false,
            id_group = false,
            fields = {},
            savebtn = App.query('span.btn_save'),
            inputs = App.queryAll('.ads_contact_center input');

        if(Util.isIterated(inputs)) {
            App.each(inputs, function(f){
                if(f.type == 'text' && f.value.length > 1) {
                    if(f.name == 'id')
                        id_contact = f.value;
                    else if (f.name == 'book')
                        id_book = f.value;
                    else if (f.name == 'groupname')
                        id_group = f.value;
                    else
                        fields[f.name] = f.value;
                }
            });

            if(Util.objLen(fields) > 1) {

                var sendData = {
                    fields: JSON.stringify(fields),
                    id_contact: Util.isNum(id_contact) ? id_contact : '',
                    is_private: true
                };

                savebtn.classList.remove('btn_save');
                savebtn.classList.add('btn_save_loading');

                App.Action.Api.request('savecontact',function(data){

                    savebtn.classList.remove('btn_save_loading');
                    savebtn.classList.add('btn_save');

                    if(App.provide.contacts[id_book] && App.provide.contacts[id_book].contacts) {

                        var g, i, book = App.provide.contacts[id_book].contacts;
                        for (g in book){
                            if(Util.isArr(book[g])) {
                                for (i = 0; i < book[g].length; i ++) {
                                    if(book[g][i]['id_contact'] === id_contact) {
                                        Util.objMergeOnlyExists(book[g][i]['fields'], fields);
                                    }
                                }

                            }
                        }
                        //App.Action.List.activeAddressBook[id_book] = App.provide.contacts[id_book];
                    }

                    App.Action.List.refreshList();

                },sendData);
            }
        }

    };


    _.actionDelete = function() {
        console.log('actionDelete');
    };

    /**
     * @namespace App.Action.Contact.onAddContact
     * @param event
     */
    _.onAddContact = function(event) {
        event.preventDefault();
        var contentBox = App.Controller.Page.node['contentBox'],
            contentWrapper = App.Controller.Page.node['contentWrapper'],
            loader = Util.createElement('div', {class: 'ico_loader contact_loader'}, '&nbsp;');

        contentWrapper.style.display = 'none';
        contentBox.style.display = 'block';
        contentBox.appendChild(loader);


        App.Action.Api.request('getcontacttpl',function(data){
            contentBox.innerHTML = data;
            _.initAfterLoaded();
        },{});

        console.log(event.target);
    };

    /**
     * @namespace App.Action.Contact.onAddGroup
     * @param event
     */
    _.onAddGroup = function(event) {
        event.preventDefault();

        console.log(event.target);
    };


    /**
     * @namespace App.Action.Contact.colorByUid
     * @param node
     * @param uid
     */
    _.colorByUid = function (uid, node) {

        var colorStyle,
            hash = md5(uid),
            maxRange = parseInt('ffffffffffffffffffffffffffffffff', 16),
            hue = parseInt(hash, 16) / maxRange * 256;

        colorStyle = 'hsl(' + hue + ', 90%, 65%)';
        console.log(node, colorStyle);
        if(typeof node === 'object' && node.nodeType === Node.ELEMENT_NODE) {
            node.style.backgroundColor = colorStyle;
            var label = Util.createElement('div', {class:'ads_avatar_label'}, uid.slice(0,1));
            node.innerHTML = '';
            node.append(label)
        }

        return colorStyle;
    };

    return _;

})}




