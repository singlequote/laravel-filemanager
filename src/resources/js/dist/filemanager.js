/*!
 * Laravel filemanager - https://github.com/singlequote/laravel-filemanager
 * Version - 1.0.0
 * Licensed under the MIT license - https://github.com/singlequote/laravel-filemanager/blob/master/LICENSE.md
 *
 * Copyright (c) 2019 SingleQuote
 */

import FilemanagerTemplate from './template.js';
import FilemanagerAction from './actions.js';
import Callback from './callback.js';
import Modal from './modal.js';
import Plugin from './plugins.js';

import Swal from './sweetalert';

/**
 * Filemanager class
 * 
 */
class FileManager
{
    /**
     * 
     * @returns {FileManager}
     */
    constructor()
    {
        this.booted = false;
        this.insideWindow = false;
        this.filepicker = false;
        this.setDefaultConfigs();
    }

    
    /**
     * Boot the package
     * 
     * @returns {undefined}
     */
    boot()
    {
        this.setDomElements();
              
        this.loadEvents();
                
        //export classes
        this.loadExtendedClasses();
        
        
        $.getJSON(`${this.url}/load/configs`, (result) => {
            this.url = result.url;
            this.media = result.mediaurl;
            this.config = result;
            this._token = result.token;
            this.addition = '?folder='+result.root;
            this.asset = result.asset;
            this.booted = true;
        });       
                
        return this;
    }
    
    /**
     * Build the default package
     * 
     * @returns {undefined}
     */
    build()
    {
        this.template.loadTemplate('package', (result) => {
            
            $(this.doms.package).html(result);

            this.loadHeader();
            this.loadSidebar();
            this.loadContent();
            this.loadListeners();

        });  
    }
    
    /**
     * Load extended classes
     * 
     * @returns {undefined}
     */
    loadExtendedClasses()
    {
        this.plugin     = new Plugin(this);
        this.template   = new FilemanagerTemplate(this);
        this.modal      = new Modal(this);
        this.return     = new Callback(this);
        this.actions    = new FilemanagerAction(this);
    }
    
    /**
     * Set the dom elements required for the package
     * 
     * @returns {undefined}
     */
    setDomElements()
    {
        this.doms           = {
            package         : '#app',
            sidebar         : '#filemanager-sidebar',
            private         : '#filemanager-private',
            shared          : '#filemanager-shared',
            content         : '#filemanager-content',
            actions         : '#filemanager-actions',
            folder          : '.folder',
            file            : '.file',
            modals          : '#filemanager-modals',
            modalPreview    : '#filemanager-media-preview'
        };
    }    
    
    /**
     * Set the default configs required for the package
     * 
     * @returns {undefined}
     */
    setDefaultConfigs()
    {
        this.url            = "/filemanager";
        this.media          = "/media";
        this.addition       = '?';
        this._token         = null;
        this.always         = null;
        this.livereload     = false;
        this.currentList    = [];
    }
    
    /**
     * Load the triggers
     * Like on click on file or folder
     * 
     * @returns {undefined}
     */
    loadEvents()
    {
        //events
        this.registerContentEvents();
        this.registerModalEvents();
    }  
    
    /**
     * Listen to events
     * 
     * @returns {undefined}
     */
    loadListeners()
    {
        setInterval(() => {
            if($('.activeFolder').length === 0 && $('.activeFile').length === 0){
                this.actions.disable(this.actions.actions);
            }
        }, 500);
        setInterval(() => {
            if(this.error || !this.livereload){
                return false;
            }
            this.loadContent();            
        }, 10000);
    }

    /**
     * Load the sidebar
     * 
     * @returns {undefined}
     */
    loadSidebar()
    {
        $(this.doms.sidebar).html('');
        
        this.template.loadTemplate('sidebar.sidebar', (result) => {
            this.template.parseTemplate({private : this.config.privateprefix, shared : this.config.sharedprefix}, 'sidebar.sidebar', this.doms.sidebar); 
        });
        
        this.template.loadTemplate('sidebar.folder');
        
        $.getJSON(`${this.url}/load/sidebar`, (result) => {
            if(this.config.privatefolder){
                $.each(result.private, (key, item) => {
                    this.template.parseTemplate(item, 'sidebar.folder', this.doms.private);
                });
            }
            if(this.config.sharedfolder){
                $.each(result.public, (key, item) => {
                    this.template.parseTemplate(item, 'sidebar.folder', this.doms.shared);
                });
            }
        });
    }

    /**
     * Load the header actions
     * 
     * @returns {undefined}
     */
    loadHeader()
    {
        this.template.loadTemplate('header.actions', (result) => {
            $(this.doms.actions).html(result);
        });
    }

    /**
     * Load the content
     * 
     * @returns {undefined}
     */
    loadContent()
    {      
        //load the template
        this.template.loadTemplate('content.folder');
        this.template.loadTemplate('content.image');
        this.template.loadTemplate('content.file');
        //current list of files inside the folder
        this.currentList = [];
        //empty the content when it's filled with cards
        if($(this.doms.content).find('.empty-content').length > 0){
            $(this.doms.content).html(``);
        }
        
        let type = this.return.config.type ? `&type=${this.return.config.type}` : '';
        
        $.getJSON(`${this.url}/load/content/${this.addition}${type}`, (result) => {
            
            if(result.folders.length === 0 && result.files.length === 0){
                return this.emptyContent();
            }
            
            $.each(result.folders, (key, value) => {
                this.currentList[value.id] = value.type;
                if($(`[lf="${value.id}"]`).length === 0){
                    this.template.parseTemplate(value, 'content.folder', this.doms.content);
                }
            });
            
            $.each(result.files, (key, value) => {
                this.currentList[value.id] = value.type;
                if($(`[lf="${value.id}"]`).length === 0){
                    let type = value.mimetype.includes('image') ? 'content.image' : 'content.file';
                    this.template.parseTemplate(value, type, this.doms.content);
                }
            }); 
            
            setTimeout(() => {
                $('[lf]').each((key, value) => {
                    if(this.currentList[$(value).attr('lf')] === undefined){
                        $(value).parent().hide('slow', function(){
                            $(this).remove();
                        });
                    }
                });  
            }, 200);
            
        });
    }
    
    /**
     * Show empty content
     * 
     * @returns {undefined}
     */
    emptyContent()
    {
        this.template.loadTemplate('content.empty', (response) => {
            $(this.doms.content).html(response);
        });
    }

    /**
     * Open the filemanager within a modal
     * 
     * @param {type} callback
     * @returns {undefined}
     */
    window(element, options = {} , callback = null,)
    {
        let returnas = callback;
        
        if(typeof options === 'function'){
            returnas = options;
        }
        
        if(!this.booted){
            return this.message('Oops', 'The package has not been booted! Boot the package before loading the modal', 'error');
        }
        if(!$(element).length > 0){
            return this.message('Oops', `Element ${element} can't be found in the dom`, 'error');
        }
        if(!this._token){
            return this.message('Oops', `The csrf token is required!`, 'error');
        }
        
        this.return.mergeConfig(options);
        
        this.insideWindow = true;
        this.doms.package = element;
        this.build();
        returnas();
        return this.return;
    }  
    
    /**
     * Set the picker to true
     * 
     * @param {type} element
     * @param {type} callback
     * @returns {undefined}
     */
    picker(element, options = {}, callback = null)
    {
       
        this.filepicker = true;
        
        return this.window(element, options, callback);
    }
    
    
    /**
     * Register content events
     * 
     * @returns {undefined}
     */
    registerContentEvents()
    {
        let self = this;

        $(document).on('click', `${this.doms.sidebar} ${this.doms.folder}`, function (e) {
            e.stopPropagation();
            self.triggeredFolder($(this));
        });
        $(document).on('click', `${this.doms.content} ${this.doms.folder}`, function (e) {
            e.stopPropagation();
            self.clickedFolder($(this), e);
        });
        $(document).on('dblclick', `${this.doms.content} ${this.doms.folder}`, function (e) {
            e.stopPropagation();
            self.triggeredFolder($(this));
        });
        $(document).on('dblclick', `${this.doms.content} ${this.doms.file}`, function (e) {
            e.stopPropagation();
            self.triggeredFile($(this));
        });
        $(document).on('click', `${this.doms.content} ${this.doms.file}`, function (e) {
            e.stopPropagation();
            self.clickedFile($(this), e);
        });
        
        $(document).on('click', `body`, function (e) {
            if(!$(e.target).attr('data-action')){
//                $('.activeFile, .activeFolder').removeClass('activeFile activeFolder');
            }
            
        });
        
    }
    
    /**
     * Register modal events
     * 
     * @returns {undefined}
     */
    registerModalEvents()
    {
//        $(document).on('submit', `#action-form`, function (e) {
//            e.preventDefault();
//            $.post($(this).attr('action'), $(this).serialize(), function () {
//                $(self.doms.modalPreview).modal('hide');
//                self.loadContent();
//                self.loadSidebar();
//            });
//        });
//        $(this.doms.modalPreview).on('hidden.bs.modal', function () {
//            $(this).find('.modal-dialog').removeAttr('style');
//        });
    }

    /**
     * Trigger the folder click
     * Reload the content with the new route
     * 
     * @param {type} element
     * @returns {undefined}
     */
    triggeredFolder(element)
    {
        this.addition = `?folder=${element.data('route')}`;
        $(this.doms.folder).removeClass('active');
        $(`a[data-route="${element.data('route')}"]`).addClass('active');
        this.loadContent();
    }

    /**
     * Open a file
     * 
     * @param {type} element
     * @returns {undefined}
     */
    triggeredFile(element)
    {
        let url = `${this.url}/${this.addition}`;
        let mark = url.includes('?') ? '&' : '?';
        
        if(this.filepicker){
            $.get(`${url}${mark}file=${element.data('route')}`, (response) => {
                return this.return.callback(response);
            });
            return false;
        }
        
        this.template.loadTemplate('modals.modal-preview', () => {
            $.get(`${url}${mark}file=${element.data('route')}`, (response) => {
            
                this.template.parseTemplate(response, 'modals.modal-preview', this.doms.modals, true);     
                this.modal.show(this.doms.modalPreview);

                if(response.type.startsWith('image')){
                    $(this.doms.modalPreview).find('.body').html(`
                        <img src="${response.route}">
                    `);
                    $(this.doms.modalPreview).find('.footer').html(`
                        <a style="color:white;" target="_blank" href="${response.route}">Open original</a>
                    `);
                }else if(response.type.startsWith('text')){
                    this.modal.firePlugin(`codemirror`, response);
                }
            });
        });
    }

    /**
     * Set folder to active
     * 
     * @param {mixed} element
     * @param {mixed} event
     * @returns {Boolean}
     */
    clickedFolder(element, event)
    {
        if (!event.ctrlKey){
            $('.activeFolder, .activeFile').removeClass('activeFolder activeFile');
        }
        if(element.attr('type') === 'root'){
            this.actions.disable(this.actions);
            return false;
        }
        element.addClass('activeFolder');
        this.actions.enable('edit', 'delete');
    }

    /**
     * Set the file to active
     * 
     * @param {mixed} element
     * @param {mixed} event
     * @returns {undefined}
     */
    clickedFile(element, event)
    {
        if (!event.ctrlKey){
            $('.activeFolder, .activeFile').removeClass('activeFolder activeFile');
        }
        
        element.addClass('activeFile');
        if(element.attr('type') === 'image'){
            return this.actions.enable('edit', 'delete', 'crop', 'resize');
        }
        return this.actions.enable('edit', 'delete');
    }

    /**
     * Set the root url
     * 
     * @param {string} url 
     */
    set root(url)
    {
        this.addition = `?folder=${url}`;
    }

    /**
     * Set the package dom element
     * 
     * @param {string} item
     */
    set package(item)
    {
        this.doms.package = item;
    }

    /**
     * Set the base url
     * 
     * @param {string} url 
     */
    set baseUrl(url)
    {
        this.url = url;
    }

    /**
     * Set the media url
     * 
     * @param {string} url 
     */
    set mediaUrl(url)
    {
        this.media = url;
    }

    /**
     * set the sidebar dom element
     * 
     * @param {mixed} sidebar 
     */
    set sidebar(sidebar)
    {
        this.doms.sidebar = sidebar;
    }

    /**
     * set the content dom element
     * 
     * @param {mixed} content 
     */
    set content(content)
    {
        this.doms.content = content;
    }

    /**
     * set the token dom element
     * 
     * @param {mixed} token 
     */
    set token(token)
    {
        this._token = token;
    }
    
    /**
     * Set the callback function
     * 
     * @param {function} callback
     */
    set callback(callback)
    {
        this.always = callback;
    }
    
    /**
     * Set to true or false
     * To use codemirror
     * default true
     * 
     * @param {boolean} boolean
     */
    set useCodemirror(boolean)
    {
        this.codemirror = boolean;
    }
    
    /**
     * Register a plugin
     * Wrapper for plugin.register
     * 
     * @param {string} name
     * @returns {Plugin}
     */
    registerPlugin(name)
    {
        return this.plugin.register(name);
    }

    /**
     * Show swal notification
     * 
     * @param {string} title
     * @param {string} message
     * @param {string} type
     * @returns {undefined}
     */
    message(title,message='', type='success')
    {
        Swal(
            title,
            message,
            type
        );
    }
    
}

window.filemanager = new FileManager;