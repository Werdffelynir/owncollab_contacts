if(App.namespace) { App.namespace('Action.Sidebar', function(App) {

    /**
     * App.Action.Sidebar.node['boxGroupsUl']
     * @namespace App.Action.Sidebar
     * @type {*}
     */
    var _ = {

        node:{},
        sbbs:{}
    };


    /**
     * @namespace App.Action.Sidebar.init
     */
    _.init = function() {
        var sbBlocks = App.queryAll('.sb_block');
        if(sbBlocks)
            App.each(sbBlocks, function(btn){ createSidebarBtn(btn) });

        // ... ... ...
        _.node['boxGroupsUl'] = App.query('#ads_groups ul');
        _.node['addContactFrom'] = App.query('#add_contact_item form');
        _.node['addGroupFrom'] = App.query('#add_group_item form');
        _.node['exportVcard'] = App.query('#export_vcard');

        _.node['addContactFrom'].addEventListener('submit', App.Action.Contact.onAddContact);
        _.node['addGroupFrom'].addEventListener('submit', App.Action.Contact.onAddGroup);
        _.node['exportVcard'].addEventListener('click', App.Action.Contact.onExportVcard);

        // init address book switcher
        _.node['addressBookInputs'] = App.queryAll('.oneline input');

        bookSwitcher();
    };



    /**
     *
     * @param sbb
     */
    function createSidebarBtn(sbb) {
        var btn = App.query('div:first-child', sbb);
        var field = App.query('div:last-child', sbb);
        field.style.display = 'none';

        btn.addEventListener('click', function(event){
            if (field.style.display == 'none') field.style.display = 'block';
            else field.style.display = 'none';
        });

        _.sbbs[btn.id] = {
            elem: sbb,
            btn: btn,
            field: field
        }
    }

    function bookSwitcher() {
        // All address books is be checked
        jQuery(_.node['addressBookInputs']).each(function(i, item){item.checked = true;});
        jQuery(_.node['addressBookInputs']).change(function(event){

            var idBook = event.target.getAttribute('data-id');

            if(event.target.checked) {
                App.Action.List.activeAddressBook[idBook] = App.provide.contacts[idBook];
            } else {

                App.Action.List.activeAddressBook[idBook] = false;
            }

            // Refresh views in Sidebar and list of contacts
            App.Action.List.refreshList();
        });

    }







    return _;

})}