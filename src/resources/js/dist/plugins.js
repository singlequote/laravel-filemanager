
/**
 * 
 * @type class
 */
export default class Plugin
{
    /**
     * 
     * @param {type} parent
     * @returns {Plugin}
     */
    constructor(parent)
    {
        this.parent = parent;
        
        this.plugins = {};
        
        this.current = null;
        this.execute = null;
    }
    
    /**
     * Register the plugin
     * 
     * @param {type} name
     * @returns {Plugin}
     */
    register(name)
    {
        this.plugins[name] = {
            loaded      : false,
            callback    : null,
            css         : [],
            js          : []
        };
        this.current = this.plugins[name];
        return this;
    }
    
    /**
     * Add script to the plugin
     * 
     * @param {type} script
     * @returns {Plugin}
     */
    addScript(script)
    {
        this.current.js.push(script);
        return this;
    }
    
    /**
     * Add style to the plugin
     * 
     * @param {type} style
     * @returns {Plugin}
     */
    addStyle(style)
    {
        this.current.css.push(style);
        return this;
    }
    
    /**
     * Add a callback
     * 
     * @param {type} callback
     * @returns {Plugin}
     */
    setCallback(callback)
    {
        this.current.callback = callback;
        return this;
    }
    
    /**
     * Execute the plugin
     * 
     * @param {type} name
     * @param {type} data
     * @returns {Boolean}
     */
    run(name, data = null)
    {
        if (!this.plugins[name]) {
            console.error(`There is no plugin registerd witht he name ${name}`);
            return false;
        }
        
        this.execute = {
            name    : name,
            data    : data,
            plugin  : this.plugins[name],
            modal   : this.parent.modal
        };
        
        if (this.plugins[name].loaded === false) {
            if(this.plugins[name].css) this.loadScript('css', this.plugins[name].css);
            if(this.plugins[name].js) this.loadScript('js', this.plugins[name].js, 0, true);
            if(!this.plugins[name].js) this.callReturn();
            this.plugins[name].loaded = true;
        } else {
            this.callReturn();
        }
        
    }
    
    /**
     * Call the callback function
     * 
     * @returns {Boolean}
     */
    callReturn()
    {
        let plugin = this.plugins[this.execute.name];
       
        if (typeof plugin.callback === 'string') {
            var fn = window[plugin.callback];
            if(typeof fn === 'function') {
                return fn(this.execute);
            }
            console.error(`${fn} is not a function or can't be found!`);
            return false;
        }
        if (plugin.callback) {
            return plugin.callback(this.execute);
        }

        return false;
    }
    
    /**
     * Load the scripts
     * 
     * @param {type} type
     * @param {type} scripts
     * @param {type} index
     * @param {type} callback
     * @returns {Boolean}
     */
    loadScript(type, scripts, index = 0, callback = false)
    {
        if (!scripts[index]) {
            if (callback) return this.callReturn();
            return true;
        }

        if (type === 'css') {
            $('<link/>', {rel: 'stylesheet', type: 'text/css', href: scripts[index]}).appendTo('head');
            this.loadScript(type, scripts, index + 1);
            return true;
        }

        $.getScript(scripts[index], () => {
            this.loadScript(type, scripts, index + 1, callback);
        });
        
        return true;
    }
    
    
    
    
}