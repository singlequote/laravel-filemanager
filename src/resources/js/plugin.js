(function ( $ ) {
    $.fn.filemanager = function( options ) {
        return new Plugin(this, $.extend({
            path : false,
            driver : false,
            loadOnStartUp : true,
            components : {
                header : true,
                sidebar : true
            }
        }, options ));
    };
}( jQuery ));

/**
 * Filemanager Helper plugin
 * 
 * @type Object
 */
class Plugin
{
    /**
     * Filemanager Helper plugin
     * 
     * @param {type} element
     * @param {type} settings
     * @returns {Plugin}
     */
    constructor(element, settings)
    {
        this.settings = settings;
        this.element = element;
        this.initialize();
        this.callback = false;
    }    
    /**
     * Initialize the plugin
     * 
     * @returns {undefined}
     */
    initialize()
    {
        let url = `/filemanager/modal?load-on-startup=${this.settings.loadOnStartUp}`;
        
        if(!this.settings.components.header){
            url += `&header=false`;
        }
        
        if(!this.settings.components.sidebar){
            url += `&sidebar=false`;
        }
        
        if(this.settings.path){
            url += `&startContent=${this.settings.path}`;
        }
        
        if(this.settings.driver){
            url += `&startDriver=${this.settings.driver}`;
        }

        this.element.load(url);
    }
    
    /**
     * Set the done method
     * Call callback when init is completed
     * 
     * @param {Object} callback
     * @returns {Plugin}
     */
    done(callback)
    {
        $(document).on('laravel-filemanager:loaded', () => {
            callback();
        });

        return this;
    }
    
    /**
     * Set the pick method
     * When a file is double clicked, the callback will be triggered
     * 
     * @param {object} callback
     * @returns {Plugin}
     */
    pick(callback)
    {
        $(document).on('laravel-filemanager:select', (e, file) => {
            callback(file, e);
        });
        
        return this;
    }
    
    /**
     * Load by path and driver
     * 
     * @param {type} path
     * @param {type} driver
     * @returns {Plugin}
     */
    load(path, driver)
    {
        this.retry(() => {
            filemanager.load(path, driver);
        });
        
        return this;
    }
    
    /**
     * 
     * @param {mixed} closure
     * @param {int} duration
     * @param {int} counter
     * @returns {void}
     */
    retry(closure, duration = 400, counter = 0)
    {
        try {
            closure();
        } catch (error) {
            if (counter > 10) {
                console.error(`the ${closure} could not be loaded after 10 retries.`);
                return;
            }
            setTimeout(() => {
                retry(closure, duration, counter + 1);
            }, duration);
        }
    }
}

