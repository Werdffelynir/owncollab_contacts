if(App.namespace) { App.namespace('Action.Editable', function(App) {

    /**
     * @namespace App.Action.Editable
     * @type {*}
     */
    var _ = {
        form:null,
        formInp:null,
        lastTarget:null,
        lastTargetContent:null
    };

    /**
     * @namespace App.Action.Editable.init
     */
    _.init = function() {

        jQuery('.contacteditable').click(wrapField);

        _.form = Util.createElement('form', {name:'editfield'});
        _.formInp = Util.createElement('input', {name: '', type: 'text', value: ''});
        _.form.appendChild(_.formInp);
        _.form.addEventListener('submit', formSubmit);
    };

    function wrapField (event) {
        if(event.target.classList.contains ('contacteditable')) {

            var target = event.target;
            var content = target.textContent;
            target.textContent = '';

            if(App.query('form[name=editfield]')) {
                _.lastTarget.textContent = _.lastTargetContent;
            }

            _.lastTarget = target;
            _.lastTargetContent = content;

            _.formInp.value = content;
            _.formInp.name = target.getAttribute('data-key');
            _.form.setAttribute('data-uid', target.parentNode.getAttribute('data-uid'));
            target.appendChild(_.form);

        }

    }

    function formSubmit (event) {
        event.preventDefault();
        var fd = Util.formData(event.target, true);
        var sendData = {key:null, value:null};
        for (var key in fd){
            sendData.key = key;
            sendData.value = fd[key];
            sendData.uid = event.target.getAttribute('data-uid');
        }

        //console.log(fd, sendData);
        jQuery(event.target.parentNode).addClass('ico_loader');
        App.Action.Api.request('addcontacts', function(response) {
            //console.log('response>>>', response);
            if(!response['error']) {
                jQuery(event.target.parentNode).removeClass('ico_loader');
                event.target.parentNode.textContent = sendData.value;
            }

        }, sendData);

    }

    return _;

})}