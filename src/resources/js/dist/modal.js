

/**
 * Modal class
 * 
 * @returns {Modal}
 */
export default class Modal
{

    /**
     * Constructor
     * 
     * @returns {Modal}
     */
    constructor(parent)
    {
        this.active = false;

        this.parent = parent;

        this.plugin = parent.plugin;

        this.loadTriggers();

        this.setDefaultPlugins();
        
        this.url = parent.url;

        this.template = parent.template;
    }
    
    /**
     * Set the default plugins
     * 
     * @returns {undefined}
     */
    setDefaultPlugins()
    {
        this.plugin.register('codemirror').setCallback('buildEditor')
                .addScript(`/vendor/laravel-filemanager/js/codemirror/config.js`)
                .addScript(`/vendor/laravel-filemanager/js/codemirror/codemirror.js`)
                .addScript(`/vendor/laravel-filemanager/js/codemirror/addon/selection/active-line.js`)
                .addScript(`/vendor/laravel-filemanager/js/codemirror/addon/edit/matchbrackets.js`)
                .addScript(`/vendor/laravel-filemanager/js/codemirror/mode/javascript/javascript.js`)
                .addStyle(`/vendor/laravel-filemanager/css/codemirror/codemirror.css`)
                .addStyle(`/vendor/laravel-filemanager/css/codemirror/darcula.css`);
        
        this.plugin.register('cropper').setCallback('buildCropper')
                .addScript(`/vendor/laravel-filemanager/js/cropper/config.js`)
                .addScript(`/vendor/laravel-filemanager/js/cropper/doka.js`)
                .addStyle(`/vendor/laravel-filemanager/css/cropper/doka.css`);
        
        this.plugin.register('dropzone').setCallback('initDropzone')
                .addScript(`/vendor/laravel-filemanager/js/dropzone/config.js`)
                .addScript(`/vendor/laravel-filemanager/js/dropzone/dropzone.js`)
                .addStyle(`/vendor/laravel-filemanager/css/dropzone/dropzone.css`);
        
        this.plugin.register('resize').setCallback('resizeFile')
                .addScript(`//code.jquery.com/ui/1.12.1/jquery-ui.js`)
                .addScript(`/vendor/laravel-filemanager/js/resizer/config.js`)
                .addStyle(`//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css`)
                .addStyle(`/vendor/laravel-filemanager/css/resizer/resizer.css`);
    }
    
    /**
     * Fire a default plugin
     * 
     * @param {type} plugin
     * @param {type} data
     * @returns {undefined}
     */
    firePlugin(plugin, data)
    {
        this.plugin.run(plugin, data);
    }
    
    /**
     * Init and parse the modal
     * 
     * @param {string} template
     * @returns {undefined}
     */
    preview(show = false, style = {"text-align" : "center"})
    {
        this.template.loadTemplate('modals.modal-preview', (response) => {
            $(this.parent.doms.modals).html(response);
            if(show) this.show(this.parent.doms.modalPreview, style);
        });
    }

    /**
     * Load triggers
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        // When the user clicks anywhere outside of the modal, close it
        $(document).on('click', '.modal', (event) => {
            if ($(event.target).hasClass('modal')) {
                $('body').trigger('modal:closed');
                this.hide();
            }
        });
    }

    /**
     * Open up the modal
     * 
     * @returns {undefined}
     */
    show(element, styling = {})
    {
        this.active = $(element);
        $(element).css('display', 'block');
        $(element).find('.content').css(styling);
    }

    /**
     * Hide the active modal
     * 
     * @param {type} parent
     * @param {type} response
     * @returns {undefined}
     */
    hide()
    {
        if(!this.active){
            return $(this.parent.doms.modals).html(``);
        }
        this.active.remove();
        this.active = null;
    }

    /**
     * Destroy all the modals
     * 
     * @param {type} modal
     * @param {type} response
     * @returns {undefined}
     */
    destroy()
    {
        this.active.css('display', 'none', () => {
            $('.modal').remove();
        });
        this.active = null;
    }

}
