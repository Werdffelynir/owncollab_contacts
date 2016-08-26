(function () {

    /**
     * @namespace
     */
    var __instances = {},
        __construct = function (type) {
            type = type.toLowerCase();
            type = !!__construct.listEvents[type] ? type : 'keyup';

            if (!(this instanceof EventKeyManager))
                return new EventKeyManager(type);

            this.eventType = type;

            __construct.addListener(this.eventType)
        };

    ///////////////////////////  Static methods  ///////////////////////////

    /**
     *
     * @type {boolean}
     */
    __construct.debug = true;
    __construct.keyCodeBinding = [];

    /**
     *
     * @type {string[]}
     */
    __construct.availableEventsNames = ['keyup', 'keydown'];

    /**
     *
     * @type {{keyup: Array, keydown: Array}}
     */
    __construct.listEvents = {keyup: [], keydown: []};

    /**
     *
     * @param type
     * @returns {*|boolean}
     */
    __construct.hasEvent = function (type) {

        return this.listEvents[type] && !isNaN(this.listEvents[type].length);
    };

    /**
     *
     * @param type
     * @param id
     * @param key_code
     * @param callback
     * @param overwrite
     */
    __construct.addEvent = function (type, id, key_code, callback, overwrite) {

        if (typeof type !== 'string' || typeof id !== 'string' || isNaN(key_code) || typeof callback !== 'function')
            throw new TypeError('Key Event Error of arguments');

        if (!__construct.getEvent(type, id) || !!overwrite) {

            __construct.listEvents[type].push({id: id, keyCode: key_code, callback: callback, active: true});
            __construct.keyCodeBinding.push(key_code);

        } else if (__construct.debug)
            throw new ReferenceError('ID "' + id + '" is exists!');
    };


    /**
     *
     * <pre>
     * getEvent('*')
     * getEvent('keyup')
     * getEvent('keyup', 'id_name')
     * getEvent('*', 'id_name')
     * </pre>
     *
     * @param type
     * @param id
     * @returns {null|object|array}
     */
    __construct.getEvent = function (type, id) {

        var result = [], stack = [], i;

        type = type.trim().toLowerCase();

        if (type === '*') {
            var _key, _event;
            for (_key in __construct.listEvents) {
                _event = __construct.listEvents[_key];
                stack = stack.concat(_event.map(function(item){item['eventType'] = _key; return item}));
            }
        } else if (!this.hasEvent(type)) {
            throw new ReferenceError('Key Event Name "' + type + '" is not exists! available is the (keyup, keydown)');
        } else
            stack = __construct.listEvents[type];

        if (id === undefined)
            return stack;

        for (i = 0; i < stack.length; i++) {
            if (typeof stack[i] === 'object' && stack[i]['id'] === id) {
                stack[i].index = i;
                result.push(stack[i])
            }
        }

        return ( type === '*' )
            ? (result.length > 0 ? result : null)
            : (result.length > 0 ? result[0] : null);

    };

    /**
     *
     * @param type
     * @param id
     * @returns {Array.<T>}
     */
    __construct.removeEvent = function (type, id) {
        var _event;
        if(_event = __construct.getEvent(type, id)) {
            __construct.keyCodeBinding.splice(__construct.keyCodeBinding.indexOf(_event.keyCode), 1);
            return __construct.listEvents[type].splice(_event.index, 1);
        }
    };

    __construct.removeListener = function (type) {
        type = type.trim().toLowerCase();
        if(__construct.listEvents[type]) {
            window.removeEventListener(type, __construct.eventBackgroundHandler);
        }
    };

    __construct.addListener = function (type) {
        if(__instances[type]) return true;
        type = type.trim().toLowerCase();
        if(__construct.listEvents[type]) {
            __instances[type] = true;
            window.addEventListener(type, __construct.eventBackgroundHandler);
        }
    };

    /**
     *
     * @param event
     */
    __construct.eventBackgroundHandler =  function (event) {
        var eventsObjects, i;

        if( typeof __construct.listEvents[event.type] === 'object' &&
            typeof __construct.listEvents[event.type]['listenCallback'] === 'function')
            __construct.listEvents[event.type]['listenCallback'].call({}, event);

        if(__construct.keyCodeBinding.indexOf(event.keyCode) !== -1) {
            if(eventsObjects = __construct.getEvent(event.type)) {
                for (i = 0; i < eventsObjects.length; i ++ ) {
                    if(typeof eventsObjects[i] === 'object' &&
                        eventsObjects[i].active && eventsObjects[i]['keyCode'] === event.keyCode ) {
                        if(typeof eventsObjects[i]['callback'] === 'function')
                            eventsObjects[i]['callback'].call({}, event, eventsObjects[i]['id']);
                    }
                }
            }
        }
    };

    /////////////////////// Dynamic prototype methods ///////////////////////


    /**
     *
     * @param id
     * @param key_code
     * @param callback
     * @param overwrite
     * @returns {boolean}
     */
    __construct.prototype.add = function (id, key_code, callback, overwrite) {
        if (__construct.hasEvent(this.eventType)) {
            __construct.addEvent(this.eventType, id, key_code, callback, overwrite);
        }
    };

    __construct.prototype.remove = function (id) {
        if (__construct.hasEvent(this.eventType)) {
            __construct.removeEvent(this.eventType, id);
        }
    };
    __construct.prototype.active = function (id, status) {
        if (__construct.hasEvent(this.eventType)) {
            var eventObject = __construct.getEvent(this.eventType, id);
            if(typeof eventObject === 'object') {
                return eventObject.active = status === undefined ? true : !!status;
            }
        }
    };
    __construct.prototype.enable = function (id) { return this.active(true); };
    __construct.prototype.disable = function (id) { return this.active(false); };
    __construct.prototype.isDisable = function (id) {
        if (__construct.hasEvent(this.eventType)) {
            var eventObject = __construct.getEvent(this.eventType, id);
            if(typeof eventObject === 'object') {
                return eventObject.active;
            }
        }
    };
    __construct.prototype.foreachEvent = function (callback) {
        if (__construct.hasEvent(this.eventType)) {
            var eventsObjects = __construct.getEvent(this.eventType);
            if(eventsObjects) {
                return eventsObjects.map(callback);
            }
        }
    };
    __construct.prototype.listen = function (callback) {
        if(typeof callback === 'function')
            __construct.listEvents[this.eventType]['listenCallback'] = callback;
    };

    __construct.prototype.getById = function (id) {
        return __construct.getEvent(this.eventType, id);
    };
    __construct.prototype.getAllByKeyCode = function (keyCode) {
        var stack =__construct.getEvent(this.eventType);
        for (i = 0; i < stack.length; i++){
            if ( stack[i]['keyCode'] == keyCode ) 
                return stack[i];
        }
    };

    __construct.charToCode = function(letter){
        return (letter||'').charCodeAt(0);
    };
    __construct.codeToChart = function(code){
        return String.fromCharCode(code);
    };

    window.EventKeyManager = __construct;
    window.EventKeyManager.version = '0.2.0';
})();