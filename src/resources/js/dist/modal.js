

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

        this.loadTriggers();

        this.codemirror = true;
        this.codeconfig = {
            loaded: {
                main: false,
            },
            theme: "darcula"
        };

        this.setPlugins();

        this.url = parent.url;

        this.template = parent.template;
    }

    /**
     * Set the plugins data
     * 
     * @returns {undefined} 
     */
    setPlugins()
    {
        this.loads = {
            codemirror : {
                loaded      : false,
                callback    : buildEditor,
                css         : {
                    0: `/vendor/laravel-filemanager/css/codemirror/codemirror.css`,
                    1: `/vendor/laravel-filemanager/css/codemirror/darcula.css`
                },
                js          : {
                    0: `/vendor/laravel-filemanager/js/codemirror/codemirror.js`,
                    1: `/vendor/laravel-filemanager/js/codemirror/addon/selection/active-line.js`,
                    2: `/vendor/laravel-filemanager/js/codemirror/addon/edit/matchbrackets.js`,
                    3: `/vendor/laravel-filemanager/js/codemirror/mode/javascript/javascript.js`,
                }
            },
            cropper : {
                loaded      : false,
                callback    : buildCropper,
                css         : {
                    0: `/vendor/laravel-filemanager/css/cropper/doka.css`
                },
                js          : {
                    0: `/vendor/laravel-filemanager/js/cropper/doka.js`
                }
            },
            dropzone : {
                loaded      : false,
                callback    : initDropzone,
                js          : {
                    0 : `/vendor/laravel-filemanager/js/dropzone/dropzone.js`
                },
                css : {
                    0 : `/vendor/laravel-filemanager/css/dropzone/dropzone.css`
                }
            },
            resize : {
                loaded      : false,
                callback    : resizeFile,
                js : {
                    0 : `//code.jquery.com/ui/1.12.1/jquery-ui.js`
                },
                css : {
                    0 : `//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css`
                }
            }
        }
    }
    
    /**
     * Init and parse the modal
     * 
     * @param {string} template
     * @returns {undefined}
     */
    preview(show = false, style = {})
    {
        this.template.loadTemplate('modals.modal-preview', (response) => {
            $(this.parent.doms.modals).html(response);
            if(show) this.modal.show(this.parent.doms.modalPreview, style);
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
                this.active.css('display', 'none');
                this.active.trigger('modal:closed');
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
        this.active.css('display', 'none');
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

    /**
     * Add a plugin to the package
     * 
     * @returns {undefined} 
     */
    addPlugin(name, config)
    {
        this.loads[name] = config;
        this.loads[name].loaded = false;
    }

    /**
     * Load a plugin
     * 
     * @param {string} plugin
     * @param {void} data
     * @returns {boolean} 
     */
    plugin(plugin, data = null)
    {
        if (!this.loads[plugin]) {
            return false;
        }

        this.data = data;
        this.activePlugin = plugin;

        if (!this.loads[plugin].loaded) {
            if(this.loads[plugin].css) this.loadScript('css', this.loads[plugin].css);
            if(this.loads[plugin].js) this.loadScript('js', this.loads[plugin].js, 0, this.callback);
            if(!this.loads[plugin].js) this.callback(this);
            this.loads[plugin].loaded = true;
        } else {
            this.callback(this);
        }

    }

    /**
     * Call the callback function
     * 
     * @returns {void} 
     */
    callback(parent)
    {
        if (typeof parent.loads[parent.activePlugin].callback === 'string') {
            return parent[parent.loads[parent.activePlugin].callback](parent, parent.data);
        }

        if (parent.loads[parent.activePlugin].callback) {
            return parent.loads[parent.activePlugin].callback(parent, parent.data);
        }

        return true;
    }

    /**
     * Load the scripts
     * 
     * @param {string} type 
     * @param {string} scripts 
     * @param {void} index 
     * @returns {boolean} 
     */
    loadScript(type, scripts, index = 0, callback = false)
    {
        if (!scripts[index]) {
            if (callback) {
                return callback(this);
            }
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

/**
 * Plugin 
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function initDropzone(modal, response)
{
    const uploadZone = new Dropzone("#action-form", {url: `${modal.url}/action/upload`});
    
    uploadZone.on("success", function (file) {
        modal.parent.loadContent();
    });
    
    $(document).on('modal:closed', () => {
        uploadZone.disable();
    });

}


/**
 * Default plugin codemirror
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function buildEditor(modal, response)
{
    modal.active.find('.body').html(`<textarea id="code">${response.content}</textarea>`);
    modal.active.find('.footer').html(`<button type="button" id="saveEditorFile">Save file</button>`);

    let editor = CodeMirror.fromTextArea(document.getElementById("code"), {
        lineNumbers: true,
        styleActiveLine: true,
        matchBrackets: true,
        readOnly: false
    });

    $('.CodeMirror').animate({
        minHeight: "800px"
    }, 0);

    editor.setOption("theme", 'darcula');

    $(document).on('click', '#saveEditorFile', () => {
        $.post(`${modal.url}/action/edit`, {content: editor.getValue(), route: response.path, _token: modal.modal._token}, (response) => {
            if (response.status === 'success') {
                modal.hide();
                modal.modal.message('Done', 'The file is saved');
            }
        });
    });
}

/**
 * Build the cropper popup
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function buildCropper(modal, response)
{
    dokaCreate().edit(`${modal.parent.media}/${response.route}`).then((output) => {
        var xhr = new XMLHttpRequest();
        var fd = new FormData();
        xhr.open("POST", `${modal.url}/action/edit`, true);
        fd.append('route', response.route)
        fd.append('_token', modal.parent._token);
        fd.append('crop', output.file);
        xhr.send(fd);
        xhr.onload(() => {
            modal.parent.loadContent();
        });
    });
}

/**
 * Resize a file
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function resizeFile(modal, response)
{
    $( "#image-resize img" ).on('load', () => {
        
        $('#action-form input[name=origin_height]').val($('#image-resize img').height());
        $('#action-form input[name=origin_width]').val($('#image-resize img').width());
        
        $( "#image-resize img" ).resizable({
            maxWidth: $("#image-resize img").width(),
            maxHeight: $("#image-resize img").height(),
            minWidth: 200,
            aspectRatio: true,
            resize: function(e, ui) {
                
                let scale = $('#image-resize img').outerWidth() * 100 / $('#image-resize').innerWidth() + '%';
                
                
                $('.resize-width').html(`${parseInt(ui.size.width)}px`);
                $('.resize-height').html(`${parseInt(ui.size.height)}px`);
                $('#action-form input[name=height]').val(`${parseInt(ui.size.height)}`);
                $('#action-form input[name=width]').val(`${parseInt(ui.size.width)}`);
                $('#action-form input[name=scale]').val(scale);
            }
        });
        
    });
}