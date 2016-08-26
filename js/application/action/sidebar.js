if(App.namespace) { App.namespace('Action.Sidebar', function(App) {

    /**
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
        if(sbBlocks) App.each(sbBlocks, function(btn){ createSidebarBtn(btn) });

        //console.log(_.sbbs);

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
            if(field.style.display == 'none') field.style.display = 'block';
            else field.style.display = 'none';
        });
        _.sbbs[btn.id] = {
            elem: sbb,
            btn: btn,
            field: field
        }
    }

    return _;

})}