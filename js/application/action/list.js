if(App.namespace) { App.namespace('Action.List', function(App) {

    /**
     * @namespace App.Action.List
     * @type {*}
     */
    var _ = {
        node:{}
    };
    /**
     * @namespace App.Action.List.activeAddressBook
     * @type {{}}
     */
    _.activeAddressBook = {};
    _.idAddressBookContactProject = null;

    /**
     * @namespace App.Action.List.init
     */
    _.init = function() {

        _.node['listContacts'] = App.query('#list_contacts');

        // List view
        if(Util.isObj(App.provide.contacts)) {

            App.each(App.provide.contacts, function(contact, key) {
                _.activeAddressBook[key] = contact;
            });

        }

        // Show data contacts
        _.refreshList();

    };


    /**
     * @namespace App.Controller.List.activeTableRowActions
     */
    _.activeTableRowActions = function() {
        jQuery('.ul_item').click(function(e){
            Util.eachParent(e.target, function(parent){

                if(parent.getAttribute('data-id-book')) {
                    var attrUId = parent.getAttribute('data-uid');
                    var attrNameBook = parent.getAttribute('data-id-book');
                    var attrNameGroup = parent.getAttribute('data-id-group');
                    var attrIdContact = parent.getAttribute('data-id-contact');

                    App.Action.Contact.currentUid = attrUId;
                    App.Action.Contact.currentBookName = attrNameBook;
                    App.Action.Contact.currentGroupName = attrNameGroup;
                    App.Action.Contact.currentContact = attrIdContact;

                    App.Action.Contact.display.apply(App.Action.Contact, [attrUId, attrNameBook, attrNameGroup, attrIdContact]);

                    return false;
                }
            },5);
        });
    };

    /**
     * @namespace App.Action.List.appendContact
     * @param contact
     */
    _.appendContact = function(contactItem, addressBookName, groupName) {

        if(typeof contactItem !== 'object' || typeof contactItem['fields'] !== 'object') return;

        var div = document.createElement('div'),
            field = contactItem['fields'],
            html = '';

        html += ' <div class="tbl_cell " data-key="display_name">' + field['display_name'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="email1">' + field['email1'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="office_tel">' + field['office_tel'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="work_address">' + field['work_address'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="groups"><strong>' + contactItem['groupname'] + '&nbsp;</strong></div>';

        div.innerHTML = html;
        div.className = 'tbl ul_item';
        div.setAttribute('data-uid', contactItem['uid']);
        div.setAttribute('data-id-book', addressBookName);
        div.setAttribute('data-id-group', groupName);
        div.setAttribute('data-id-contact', contactItem['id_contact']);

        _.node['listContacts'].appendChild(div);
    };


    /**
     * @namespace App.Action.List.refreshList
     */
    _.refreshList = function() {
        _.node['listContacts'].textContent = '';
        App.Action.Sidebar.node['boxGroupsUl'].textContent = '';

        _.appendCategory({id_group:'all',id_book:'',is_private:'',name: 'Everyone'}, '0');

        App.each(_.activeAddressBook, function(obj, addressBookName) {

            if(typeof obj !== 'object') return;

            var id_book = obj.book['id_book'];
            var id_group = null;
            var groupsArray = obj.groups;

            if(Util.isIterated(obj.contacts)) {
                App.each(obj.contacts, function(contact, groupName) {


                    if(Util.isIterated(groupsArray)){
                        var ig1, ig2;
                        for(ig1 = 0; ig1 < groupsArray.length; ig1++){
                            if(groupsArray[ig1]['name'] == groupName)
                                id_group = groupsArray[ig1]['id_group'];
                        }
                    }

                    if(_.fiterFor['id_book'] !== null && _.fiterFor['id_group'] !== null) {

                        if(_.fiterFor['id_book'] === id_book && _.fiterFor['id_group'] === id_group){
                            // show all
                            App.each(contact, function(contactItem){
                                _.appendContact(contactItem, addressBookName, groupName)
                            });
                        }


                    } else {
                        // show all
                        App.each(contact, function(contactItem){
                            _.appendContact(contactItem, addressBookName, groupName)
                        });
                    }


                });
            }

            if (!_.idAddressBookContactProject && obj.book.is_project == "1") {
                _.idAddressBookContactProject = obj.book.id_book;
            }

            if(Util.isIterated(obj.groups)) {
                App.each(obj.groups, function(group){
                    _.appendCategory(group, obj.book.is_project);
                });
            }

        });
        _.activeTableRowActions();
    };


    /**
     * cat = Object { id_group: "33", id_book: "17", name: "without_group", is_private: "0" }
     * @param cat
     * @param is_project
     */
    _.appendCategory = function(cat, is_project) {
        var elem = Util.createElement('li',
            {
                'data-id-group': cat.id_group,
                'data-id-book': cat.id_book,
                'data-is-project': is_project,
                'data-is-private': cat.is_private
            }, cat.name);

        elem.addEventListener('click', _.onFilterByCategory);

        App.Action.Sidebar.node['boxGroupsUl'].appendChild(elem);
    };

    _.fiterFor = {id_book: null, id_group: null};

    _.onFilterByCategory= function(event) {
        var t = event.target,
            id_group = t.getAttribute('data-id-group'),
            id_book = t.getAttribute('data-id-book'),
            is_private = t.getAttribute('data-is-private'),
            is_project = t.getAttribute('data-is-project');

        if(id_group == 'all')
            _.fiterFor = {id_book: null, id_group: null};
        else {
            _.fiterFor = {id_book: id_book, id_group: id_group};
        }

        _.refreshList();
    };


    return _;

})}