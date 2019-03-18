
/**
 * 
 */
export default class FilemanagerTemplate{
    
    /**
     * 
     */
    constructor(parent)
    {
        this.templates = {};
        this.parent = parent;
        this.url = parent.url;
    }
        
    /**
     * Load template
     * 
     * @param {type} name
     * @param {type} callback
     * @returns {Boolean}
     */
    loadTemplate(name, callback = null)
    {
        
        if(this.templates[name] !== undefined){
            if(callback){
                callback(this.templates[name],this);
            }
            if(this.parent.always){
                this.parent.always();
            }
            return true;
        }
        
        $.get(`${this.url}/load/template/${name}`, (result) => {
            this.templates[name] = result;
            
            if(callback){
                callback(result);
            }
            
            if(this.parent.always){
                this.parent.always();
            }
            
        }).fail(() => {
            this.error = true;
            this.parent.message('Oops', `The template '${name}' could not be found`, 'error');
        });
    }
    
    /**
     * Return the template by name
     * 
     * @param {type} type
     * @returns {FileManager.templates}
     */
    getTemplate(type)
    {
        return this.templates[type];
    }
    
    /**
     * Parse the template file
     * 
     * @param {mixed} item
     * @param {mixed} type
     * @param {mixed} appendto
     * @param {mixed} truncate
     * @returns {undefined}
     */
    parseTemplate(item, type, appendto, truncate = false)
    {
        if(truncate){
            $(appendto).html('');
        }
        let element = $(this.getTemplate(type)).appendTo(appendto);
        this.setTemplateDataAttributes(item, element);
        this.setTemplatePlaceAttributes(item, element);
        this.setTemplateSetAttributes(item, element);
        
        $.each(element.find("[lf-data]"), (key, element) => {
            this.setTemplateDataAttributes(item, $(element));
        });
        $.each(element.find("[lf-append]"), (key, element) => {
            this.setTemplatePlaceAttributes(item, $(element));
        });
        $.each(element.find("[lf-attribute]"), (key, element) => {
            this.setTemplateSetAttributes(item, $(element));
        });
        $.each(element.find("[lf-background]"), (key, element) => {
            this.setTemplateBackgroundAttributes(item, $(element));
        });
        
        if(this.parent.always){
            this.parent.always();
        }
    }
    
    /**
     * Set a template attribute
     * 
     * @param {type} source
     * @param {type} element
     * @returns {undefined}
     */
    setTemplateDataAttributes(source, element)
    {
        if(element.attr("lf-data") !== undefined){
            let items = element.attr("lf-data").replace(/\s/g,'').split(';');
            $.each(items, (k, attribute) => {
                element.attr(`data-${attribute}`, source[attribute]).removeAttr(`lf-data`);
            });
        }
    }
    
    /**
     * Set template attributes for appends
     * 
     * @param {type} source
     * @param {type} element
     * @returns {undefined}
     */
    setTemplatePlaceAttributes(source, element)
    {
        if(element.attr("lf-append") !== undefined){
            let items = element.attr("lf-append").replace(/\s/g,'').split(';');
            $.each(items, (k, attribute) => {
                if(element.attr('lf-call')){                   
                    element.append(this[element.attr('lf-call')](source[attribute])).removeAttr(`lf-append`).removeAttr('lf-call');
                }else{
                    element.append(source[attribute]).removeAttr(`lf-append`);
                }
            });
        }
    }

    /**
     * Set template attributes for appends
     * 
     * @param {type} source
     * @param {type} element
     * @returns {undefined}
     */
    setTemplateSetAttributes(source, element)
    {
        if(element.attr("lf-attribute") !== undefined){
            let items = element.attr("lf-attribute").replace(/\s/g,'').split(';');            
            $.each(items, (k, attribute) => {
                let sets = attribute.split(':');
                element.attr(`${sets[0]}`, source[sets[1]]).removeAttr(`lf-attribute`);
            });
        }
    }
    
    /**
     * Set template attribute for background
     * 
     * @param {type} source
     * @param {type} element
     * @returns {undefined}
     */
    setTemplateBackgroundAttributes(source, element)
    {
        if(element.attr("lf-background") !== undefined){
            let attribute = element.attr("lf-background").replace(/\s/g,'');            
            element.css('background-image', `url(${source[attribute]})`).removeAttr('lf-background');
        }
    }
    
    /**
     * Format bytes tyo readable
     * 
     * @param {string} bytes
     * @returns {string} 
     */    
    formatBytes(bytes) 
    {
        if(bytes < 1024) return bytes + " Bytes";
        else if(bytes < 1048576) return(bytes / 1024).toFixed(3) + " KB";
        else if(bytes < 1073741824) return(bytes / 1048576).toFixed(3) + " MB";
        else return(bytes / 1073741824).toFixed(3) + " GB";
    }
    
    /**
     * Pick icon based on file type
     * 
     * @param {string} type
     * @returns {string} 
     */
    pickIcon(type)
    {
        if(type.startsWith('directory')){
            return `<i data-feather="folder"></i>`;
        }
        else if(type.startsWith('text')){
            return `<i data-feather="file-text"></i>`;
        }
        
        return type;
    }
    
    
}