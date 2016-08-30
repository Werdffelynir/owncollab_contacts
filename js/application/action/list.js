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
     * @namespace App.Action.List.appendContact
     * @param contact
     */
    _.appendContact = function(contact) {

        var div = document.createElement('div'),
            field = contact['fields'],
            html = '';

        html += ' <div class="tbl_cell " data-key="display_name">' + field['display_name'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="email1">' + field['email1'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="office_tel">' + field['office_tel'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="work_address">' + field['work_address'] + '&nbsp;</div>';
        html += ' <div class="tbl_cell " data-key="groups"><strong>' + contact['groupname'] + '&nbsp;</strong></div>';

        div.innerHTML = html;
        div.className = 'tbl ul_item';
        div.setAttribute('data-id', contact['id_contact']);
        div.setAttribute('data-uid', contact['uid']);

        _.node['listContacts'].appendChild(div);
    };


/*    <div class="tbl ul_item" data-uid="">
 <div class="tbl_cell " data-key="displayname">&nbsp;</div>
 <div class="tbl_cell " data-key="email">&nbsp;</div>
 <div class="tbl_cell " data-key="office_tel">&nbsp;</div>
 <div class="tbl_cell " data-key="address">&nbsp;</div>
 <div class="tbl_cell"><strong>&nbsp;</strong></div>
 </div>*/

    /**
     * @namespace App.Action.List.refreshList
     */
    _.refreshList = function() {

        _.node['listContacts'].textContent = '';

        App.each(_.activeAddressBook, function(obj, key) {
            //console.log(obj, key);
            if(Util.isIterated(obj.contacts)) {
                App.each(obj.contacts, function(contact, group){

                    App.each(contact, _.appendContact);
                });
            }
            //                    console.log(contact);
        });




    };


    return _;

})}