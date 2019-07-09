if(typeof $ === "undefined"){
    window.$ = window.jQuery = require('./jquery-3.4.1');
}

import contextMenu from './contextual';
import Box from './box';
import FileController from './fileController';
import FolderController from './folderController';
import ShareController from './shareController';

/**
 * FIlemanager class for laravel
 * 
 * @type {class}
 */
class FileManager
{
    /**
     * 
     * @returns {FileManager}
     */
    constructor()
    {
        this.currentPath;
        
        
        this.pageFolders = 1;
        this.pageFiles = 1;
        
        this.loadRequiredPlugins(() => {
            this.setElements();
            this.modal = this.domPackage.data('modal');

            this.loadConfig(() => {
                this.build(() => {
                    this.box    = new Box(this);
                    this.file   = new FileController(this);
                    this.share  = new ShareController(this);
                    this.folder = new FolderController(this);
                                        
                    this.replaceSize();
                });
            });
        });
    }
    
    /**
     * Build the package
     * 
     * @returns {undefined}
     */
    build(callback = false)
    {
        if($('#package-filemanager').length === 0){
            return false;
        }
        
        $('#package-filemanager').disableSelection();
        if($('.sidebar-button.active').length === 0){
            $('.sidebar-button').first().addClass('active');
        }
        
        this.loadContent();
        this.loadContextMenus();
        this.loadTriggers();
        
        if(callback){
            callback();
        }
    }
    
    /**
     * Check the sizes of the grid layout
     * 
     * @returns {undefined}
     */
    checkSizes()
    {
        if(this.modal){
            this.domContent.find('.files').css('grid-template-columns', 'repeat(4, 25%)');
            this.domContent.find('.folders').css('grid-template-columns', 'repeat(4, 25%)');
        }
    }
        
    /**
     * Load the config file
     * 
     * @returns {undefined}
     */
    loadConfig(callback)
    {      
        $.getJSON(this.url('config'), (response) => {
            this.config = response;
            callback();
        });
    }
    
    /**
     * Load triggers
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        //Change the driver
        $(document).on('click', '#package-filemanager .sidebar-button.drive', (e) => { this.changeDrive(e); });
        
        //Change the active folder and reload the content
        $(document).on('click', '#package-content .breadcrumb li a', (e) => {
            e.preventDefault();
            let element = $(e.currentTarget);
            this.loadContent(this.setUrl('load/content', element.attr('href')));
        });
        
        //Reload the disk size
        $(document).on('click', '#diskSize', () => {
            this.reloadDiskSize();
        });
                
//        $(document).on('submit', '#renameContent', (e) => { this.submitRenameContent(e); });
//        $(document).on('submit', '#addFolder', (e) => { this.submitAddFolder(e); });
//        $(document).on('submit', '#shareContent', (e) => { this.submitShareContent(e); });
        

//                
//        $(document).on('click', '.selectOnClick', (e) => {   this.copyToClipboard(e.currentTarget); });
//        
//        $(document).on('click', '.load-more', (e) => {  
//            let type = $(e.currentTarget).data('type');
//            $(e.currentTarget).remove();
//            if(type === 'files'){
//                this.pageFiles = this.pageFiles + 1;
//                this.loadFiles(() => {
//                    this.setContentPlugins();
//                });
//            }else{
//                this.pageFolders = this.pageFolders + 1;
//                this.loadFolders(() => {
//                    this.setContentPlugins();
//                });
//            }
//        });
        
        
    }
    
    /**
     * Replace sizes to readable sizes
     * 
     * @returns {undefined}
     */
    replaceSize()
    {
        $('.size').each((i, size) => {
            $(size).removeClass('size').html(this.formatBytes($(size).html()));
        });
    }
    
    /**
     * Reload the disk size
     * 
     * @returns {undefined}
     */
    reloadDiskSize()
    {
        $(`.diskSize`).load(`${this.url(``)} #diskSize`, () => {
            this.replaceSize();
            feather.replace();
        });
    }
    
    /**
     * Copy text to clipboard
     * 
     * @param {type} element
     * @returns {undefined}
     */
    copyToClipboard(element) {
        $(element).css("background", "yellow");
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }
    
    /**
     * Change the current drive to selected slug
     * 
     * @param {type} e
     * @returns {undefined}
     */
    changeDrive(e)
    {
        let element = $(e.currentTarget);
        $('#package-filemanager .sidebar-button.drive.active').removeClass('active');
        element.addClass('active');
        this.currentPath = $('.sidebar-button.active').data('slug');
        this.loadContent();
        
        window.history.pushState('filemanager', 'SingleQuote', `${location.origin}${location.pathname}?driver=${this.currentPath}`);
    }
    
    /**
     * Set the contect menu items
     * 
     * @returns {undefined}
     */
    loadContextMenus()
    {
        
        $.contextMenu({
            targets: '#package-content .file, #package-content .folder',
            menu: [
                {
                    name: this.trans('rename'),
                    icon: `edit`,
                    callback : (e) => {
                        let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                        $(e.currentTarget).trigger(`${type}:edit`);
                    }
                },
                {
                    name: this.trans('details'),
                    icon: `clipboard`,
                    callback : (e) => {
                        let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                        $(e.currentTarget).trigger(`${type}:details`);
                    }
                },
                {
                    name: this.trans('share'),
                    icon: `share-2`,
                    callback : (e) => {
                        let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                        $(e.currentTarget).trigger(`${type}:share`);
                    }
                },
                {
                    name: this.trans('delete'),
                    icon: `trash`,
                    callback : (e) => {
                        let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                        $(e.currentTarget).trigger(`${type}:delete`);
                    }
                }
                
            ]
            
        });
        
        $.contextMenu({
            targets: '#package-content',
            menu: [
                {
                    name: this.trans('upload files'),
                    icon: `upload`,
                    callback : () => {
                        $(document).trigger('file:upload');
                    }
                },{
                    name: this.trans('new folder'),
                    icon: `folder-plus`,
                    callback : (e, menu, position) => {
                        $(document).trigger('folder:create');
                    }
                }
            ]
            
        });
    }
    
    /**
     * Load required plugins like JQuery
     * 
     * @param {type} callback
     * @returns {undefined}
     */
    loadRequiredPlugins(callback)
    {
        if(typeof feather === "undefined"){
            $.getScript('https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js', () =>{
                feather.replace();
            });
        }
        
        if (typeof jQuery.ui === "undefined"){
            $.getScript('https://code.jquery.com/ui/1.12.1/jquery-ui.js', () =>{
                callback();
            });
        }else{
            callback();
        }
        
    }
    
    /**
     * Load the content
     * 
     * @returns {undefined}
     */
    loadContent(setUrl = false)
    {       
        let url = setUrl ? setUrl : this.url(`load/content`);
        this.pageFolders = 1;
        this.pageFiles = 1;
        this.domContent.html(this.loader());
        $.get(url, (response) => {
            
            this.domContent.html(response);
            this.loadFolders(() => {
                this.loadFiles(() => {
                    this.setContentPlugins();
                });
            });
            
        });
    }
    
    /**
     * Load the folders
     * 
     * @returns {undefined}
     */
    loadFolders(callback = null)
    {
        this.domPackage.find('.folders').append(this.loader());
        
        $.get(this.url('get/folders'), (response) => {
           this.domPackage.find('.folders').append(response); 
           $('.folders .breeding-rhombus-spinner').remove();
           this.checkSizes();
           if(callback){
               return callback();
           }
        });
    }
    
    /**
     * Load the files
     * 
     * @returns {undefined}
     */
    loadFiles(callback = null)
    {
        this.domPackage.find('.files').append(this.loader());
        $.get(this.url('get/files'), (response) => {
           this.domPackage.find('.files').append(response); 
           $('.files .breeding-rhombus-spinner').remove();
           this.checkSizes();
           if(callback){
               return callback();
           }
        });
    }
    
    /**
     * Set the plugins when the content is loaded
     * 
     * @returns {undefined}
     */
    setContentPlugins()
    {
        $('.nu-context-menu').not(':first').remove();
        
        feather.replace();

        $('#package-content .files').selectable({
            filter: ".file,.folder,.load-more",
            cancel: ".file,.folder,.load-more",
            start : () => {
                $(document).trigger('click');
            },
            classes: {
                "ui-selected": "active"
            }
        });
    }        
    
    /**
     * Set the required dom elements
     * 
     * @returns {undefined}
     */
    setElements()
    {
        this.domPackage = $('#package-filemanager');
        this.domSidebar = $('#package-sidebar');
        this.domContent = $('#package-content');
        this.domInfo    = $('#package-info');
    }
    
    /**
     * Returns the full url
     * 
     * @param {type} path
     * @param {type} addition
     * @returns {String}
     */
    url(path, addition = false)
    {     
        let currentPath = this.currentPath ? this.currentPath : $('.sidebar-button.active').data('slug');
        
        this.currentPath = addition ? currentPath+"/"+addition : currentPath;
        
        let url = this.domPackage.data('url') ? this.domPackage.data('url') : location.href;
        
        let split = url.split('?');
        
        url = `${split[0]}/${path}${split.length > 1 ? `?${split[1]}` : ``}`;

        let mark = url.includes('?') ? '&' : '?';
                
        return `${url}${mark}path=${this.currentPath}&pageFolders=${this.pageFolders}&pageFiles=${this.pageFiles}`;
    }
    
    /**
     * Reset the url
     * 
     * @param {type} path
     * @param {type} addition
     * @returns {String}
     */
    setUrl(path, addition = false)
    {
        this.currentPath = $('.sidebar-button.active').data('slug');
        
        return this.url(path, addition);
    }
    
    /**
     * Return a new loader
     * 
     * @returns {String}
     */
    loader()
    {
        return `
        <div class="breeding-rhombus-spinner">
            <div class="rhombus child-1"></div><div class="rhombus child-2"></div><div class="rhombus child-3"></div><div class="rhombus child-4"></div>
            <div class="rhombus child-5"></div>
            <div class="rhombus child-6"></div>
            <div class="rhombus child-7"></div>
            <div class="rhombus child-8"></div>
            <div class="rhombus big"></div>
        </div>
        `;
    }
    
    /**
     * Find translation key
     * 
     * @param {type} key
     * @returns {FileManager.config.trans}
     */
    trans(key)
    {
        if(this.config.trans[key]){
            return this.config.trans[key];
        }
        return key;
    }
    
    /**
     * Generate unique id
     * 
     * @returns {String}
     */
    unique() {
        let s = [];
        let hexDigits = "0123456789abcdef";
        for (let i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
        s[8] = s[13] = s[18] = s[23] = "-";
        return s.join("");
    }
    
    /**
     * Format butes to read
     * 
     * @param {type} bytes
     * @param {type} decimals
     * @returns {String}
     */
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
}

window.FileManager = new FileManager;

export default FileManager;