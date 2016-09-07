if(App.namespace) { App.namespace('Action.Contact', function(App) {

    /**
     * @namespace App.Action.Contact
     * @type {*}
     */
    var _ = {
        node:{},
        currentUid:null,
        currentIdBook:null,
        currentBookName:null,
        currentIdGroup:null,
        currentGroupName:null,
        currentContactId:null,
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
        _.node['inputIdBook'] = App.query('#id_book');
        _.node['inputIdBookList'] = App.query('#id_book_list');
        _.node['inputIdGroup'] = App.query('#id_group');
        _.node['inputIdGroupList'] = App.query('#id_group_list');

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
     * book, contact, uid
     * @namespace App.Action.Contact.display
     * @param book
     * @param contact
     */
    /**
     *
     * @param uid
     * @param nameBook
     * @param nameGroup
     * @param idContact
     */
    _.display = function(uid, nameBook, nameGroup, idContact) {

        var contentBox = App.Controller.Page.node['contentBox'],
            contentWrapper = App.Controller.Page.node['contentWrapper'],
            loader = Util.createElement('div', {class: 'ico_loader contact_loader'}, '&nbsp;');

        contentWrapper.style.display = 'none';
        contentBox.style.display = 'block';
        contentBox.appendChild(loader);

        App.Action.Api.request('getcontact',function(data){
            contentBox.innerHTML = data;

            _.initAfterLoaded();

            // avatar color
            _.colorByUid(uid, _.node['avatar']);

            // SELECT Addressbook &  SELECT Group
            _.selectFieldsBook(nameBook);
            _.selectFieldsGroup(nameBook, nameGroup);

        },{book: nameBook, contact: idContact});
    };

    _.selectFieldsBook = function(nameBook) {
        _.node['inputIdBook'].value = App.provide.contacts[nameBook].book.name;
        _.node['inputIdBook'].setAttribute('data-id', App.provide.contacts[nameBook].book.id_book);
        _.node['inputIdBook'].classList.add('lis_disable');
    };

    _.selectFieldsGroup = function(nameBook, nameGroup) {
        jQuery(_.node['inputIdGroup']).click(function(){jQuery(_.node['inputIdGroupList']).toggle()});
        var addressbook = null,
            gList = App.query('ul', _.node['inputIdGroupList']);

        if(nameBook !== 'project_contacts'){

            if(App.provide.contacts[nameBook])
                addressbook = App.provide.contacts[nameBook];
            else
                addressbook = App.provide.contacts['project_contacts'];

            App.each(addressbook.groups, function(item) {

                if(item.name == nameGroup) {
                    _.node['inputIdGroup'].value = nameGroup;
                    addressbook.groups.map(function(group){
                        if(group.name == nameGroup)
                            _.node['inputIdGroup'].setAttribute('data-id', group.id_group);
                    });
                }

                var li = Util.createElement('li', {
                    'data-id-book': item.id_book,
                    'data-id-group': item.id_group,
                    'data-is_private': item.is_private,
                    'data-name': item.name
                }, item.name);

                li.addEventListener('click', _.onSelectGroup);
                gList.appendChild(li);
            });

        } else {

            _.node['inputIdGroup'].classList.add('lis_disable');
            _.node['inputIdGroup'].value = nameGroup;

            if(nameGroup) {
                var id_group = '';
                App.provide.contacts['project_contacts'].groups.map(function(item){
                    if(item.name == nameGroup)
                        id_group = item.id_group;
                });
                _.node['inputIdGroup'].setAttribute('data-id', id_group);
            }
        }
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
            savebtn = App.query('#ads_btn_save'),
            savebtnIco = App.query('#ads_btn_save span'),
            inputs = App.queryAll('.ads_contact_center input');



        if(Util.isIterated(inputs)) {

            App.each(inputs, function(f){

                if(f.type == 'text' && f.value.length > 1) {
                    if(f.name == 'id_contact')
                        id_contact = f.value;
                    else
                        fields[f.name] = f.value;
                }

                if(f.type == 'button'){
                    if (f.name == 'id_book')
                        id_book = f.getAttribute('data-id');
                    else if (f.name == 'id_group')
                        id_group = f.getAttribute('data-id');
                }

            });


            if(Util.objLen(fields) >= 1) {

                var sendData = {
                    fields: JSON.stringify(fields),
                    id_book: id_book,
                    id_group: id_group,
                    id_contact: Util.isNum(id_contact) ? id_contact : '',
                    is_private: true
                };

                savebtnIco.classList.remove('btn_save');
                savebtnIco.classList.add('btn_save_loading');

                App.query('input[name="id_group"][type="button"]').style.outline = '';
                if(Util.isEmpty(sendData.id_group)) {
                    App.query('input[name="id_group"][type="button"]').style.outline = '3px solid #F00';

                    savebtnIco.classList.remove('btn_save_loading');
                    savebtnIco.classList.add('btn_save');
                    return;
                }

                App.Action.Api.request('savecontact', function(response){
                    var book = App.provide.contacts[id_book] ? App.provide.contacts[id_book] : App.provide.contacts['project_contacts'];
                    var contacts = book.contacts;
                    savebtnIco.classList.remove('btn_save_loading');
                    savebtnIco.classList.add('btn_save');

                    if(sendData.id_contact === '' && response['insert_id']) {

                        // Add
                        var groupName = (function(){
                            var ig,
                                groups =  book.groups;
                            for(ig = 0; ig < groups.length; ig++){
                                if(groups[ig]['id_group'] == id_group) {
                                    return groups[ig]['name'];
                                }
                            }
                        })();
                        var contactData = {
                            fields: response['fields'],
                            groupname: groupName,
                            id_contact: response['insert_id'],
                            is_private: 1,
                            uid: response['uid']
                        };
                        contacts[groupName].push(contactData);

                    }
                    else {

                        // Change
                        if(contacts) {
                            var g, i;
                            for (g in contacts){
                                if(Util.isArr(contacts[g])) {
                                    for (i = 0; i < contacts[g].length; i ++) {
                                        if(contacts[g][i]['id_contact'] === id_contact) {
                                            Util.objMergeOnlyExists(contacts[g][i]['fields'], fields);
                                        }
                                    }
                                }
                            }
                            //App.Action.List.activeAddressBook[id_book] = App.provide.contacts[id_book];
                        }
                    }

                    App.Action.List.refreshList();

                }, sendData);
            }
        }

    };

    _.addNewContact = function(contentBox, display_name) {

        // todo: Только одна адресная книга предполагается
        var k, id_book, name_book, id_group = null;

        for (k in App.provide.contacts) {
            if(k !== 'project_contacts'){
                id_book = App.provide.contacts[k].book.id_book;
                name_book = k;

                break;
            }
        }


/*        var idBookInput = App.query('input[name="book"]', contentBox);
        idBookInput.value = name_book;*/

        var nameInput = App.query('input[name="display_name"]', contentBox);
        nameInput.value = display_name;

        _.currentBookName = name_book;

        _.selectFieldsBook(name_book);
        _.selectFieldsGroup(name_book, name_book);

        // avatar color
        _.colorByUid(display_name ,_.node['avatar']);

    };



    _.onSelectGroup = function(event) {
        var t = event.target,
            name = t.getAttribute('data-name'),
            id_book = t.getAttribute('data-id-book'),
            id_group = t.getAttribute('data-id-group'),
            is_private = t.getAttribute('data-is_private');

        _.node['inputIdGroup'].value = name;
        _.node['inputIdGroup'].setAttribute('data-id', id_group);

        jQuery(_.node['inputIdGroupList']).toggle();
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
            _.addNewContact(contentBox, App.query('input', event.target).value);
        },{});

    };

    /**
     * @namespace App.Action.Contact.onAddGroup
     * @param event
     */
    _.onAddGroup = function(event) {
        event.preventDefault();
        var form = event.target,
            input = App.query('input', form);

        if(input && input.value.length > 2) {
            var k,
                id_book = (function(){
                                for (k in App.provide.contacts) {
                                    if(k !== 'project_contacts') {
                                        return App.provide.contacts[k].book.id_book;
                                    }
                                }
                            })();

            var inputIbBook = Util.createElement('input', {
                name: 'id_book',
                value: id_book,
                hidden: 'hidden'
            });

            input.name = 'name_group';

            form.method = "POST";
            form.action = App.url + "/savegroup";
            form.appendChild(inputIbBook);
            form.submit();

        }

    };

    /**
     * @namespace App.Action.Contact.onExportVcard
     * @param event
     */
    _.onExportVcard = function (event) {

    };

    /**
     * @namespace App.Action.Contact.colorByUid
     * @param node
     * @param uid
     */
    _.colorByUid = function (uid, node) {

        uid = uid || "UID";

        var colorStyle,
            hash = md5(uid),
            maxRange = parseInt('ffffffffffffffffffffffffffffffff', 16),
            hue = parseInt(hash, 16) / maxRange * 256;

        colorStyle = 'hsl(' + hue + ', 90%, 65%)';

        if(typeof node === 'object' && node.nodeType === Node.ELEMENT_NODE) {
            node.style.backgroundColor = colorStyle;
            var label = Util.createElement('div', {class:'ads_avatar_label'}, uid.slice(0,1));
            node.innerHTML = '';

            if(typeof label === 'object')
                node.appendChild(label)
        }

        return colorStyle;
    };

    return _;

})}




