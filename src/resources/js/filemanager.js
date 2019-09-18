if (typeof $ === "undefined") {
    window.$ = window.jQuery = require('./jquery-3.4.1');
}

import Box from './box';
import Locker from './locker';
import contextMenu from './contextual';
import FileController from './Controllers/fileController';
import ShareController from './Controllers/shareController';
import FolderController from './Controllers/folderController';


/**
 * Filemanager class for laravel
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
        this.initialize();
    }
    
    /**
     * load
     * 
     * @returns {undefined}
     */
    initialize()
    {
        this.currentPath;
        this.pageFiles = 1;
        this.pageFolders = 1;
        this.currentFolderConfig = false;

        this.loadRequiredPlugins(() => {
            this.setElements();
            this.modal = this.domPackage.data('modal');

            this.loadConfig(() => {
                this.build();
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
        if ($('#package-filemanager').length === 0) {
            return false;
        }

        $('#package-filemanager').disableSelection();
        if ($('.sidebar-button.active').length === 0) {
            $('.sidebar-button').first().addClass('active');
        }
        if (typeof this.domPackage.data('start') === "undefined" || (this.domPackage.data('start') && this.domPackage.data('start') === true)) {
            if(this.domPackage.data('start-content')){
                this.load(this.domPackage.data('start-content'), this.domPackage.data('start-driver'));
            }else{
                this.loadContent();
            }            
        }        
        
        this.loadTriggers();

        this.locker = new Locker(this);
        this.box = new Box(this);
        this.file = new FileController(this);
        this.share = new ShareController(this);
        this.folder = new FolderController(this);
        
        this.replaceSize();
        
        $(document).trigger('laravel-filemanager:loaded');
    }

    /**
     * Check the sizes of the grid layout
     * 
     * @returns {undefined}
     */
    checkSizes()
    {
        if (this.modal) {
            this.domPackage.css({'grid-template-columns': '20% 55% 25%', 'font-size': '12px'});
            this.domContent.find('.files').css('grid-template-columns', 'repeat(4, 25%)');
            this.domContent.find('.folders').css('grid-template-columns', 'repeat(4, 25%)');
        }
        if(!this.domSidebar.length){
            this.domPackage.css('grid-template-columns', '75% 25%');
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
        $(document).off('click', '#package-filemanager .sidebar-button.drive');
        $(document).on('click', '#package-filemanager .sidebar-button.drive', (e, reload = true) => {
            this.changeDrive(e, reload);
        });

        //Change the active folder and reload the content
        $(document).off('click', '#package-content .breadcrumb li a');
        $(document).on('click', '#package-content .breadcrumb li a', (e) => {
            e.preventDefault();
            let element = $(e.currentTarget);
            this.loadContent(this.setUrl('load/content', element.attr('href')));
        });

        //Reload the disk size
        $(document).off('click', '#diskSize');
        $(document).on('click', '#diskSize', () => {
            this.reloadDiskSize();
        });
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
    changeDrive(e, reload)
    {
        let element = $(e.currentTarget);
        $('#package-filemanager .sidebar-button.drive.active').removeClass('active');
        element.addClass('active');
        this.currentPath = $('.sidebar-button.active').data('slug');
        if (typeof this.domPackage.data('start') === "undefined" || (this.domPackage.data('start') && this.domPackage.data('start') === true)) {
            this.loadContent();
        }

        window.history.pushState('filemanager', 'SingleQuote', `${location.origin}${location.pathname}?driver=${this.currentPath}`);
    }

    /**
     * Set the contect menu items
     * 
     * @returns {undefined}
     */
    loadContextMenus(destroy = false)
    {
        if(destroy){
           $('.contextMenu').unbind().remove(); 
        }
        
        this.elementMenu();
        this.contentMenu();
    }
    
    /**
     * Set the elements context menu
     * 
     * @returns {undefined}
     */
    elementMenu()
    {        
        let menu = $.contextMenu({
            targets: '#package-content .file, #package-content .folder',
            menu: [{
                name: this.trans('open'),
                icon: `eye`,
                callback: (e) => {
                    let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                    $(e.currentTarget).trigger(`${type}:open`);
                }
            },{
                name: this.trans('rename'),
                icon: `edit`,
                callback: (e) => {
                    let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                    $(e.currentTarget).trigger(`${type}:edit`);
                }
            },{
                name: this.trans('share'),
                icon: `share-2`,
                callback: (e) => {
                    let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                    $(e.currentTarget).trigger(`${type}:share`);
                }
            },{
                name: this.trans('delete'),
                icon: `trash`,
                callback: (e) => {
                    let type = $(e.currentTarget).hasClass('file') ? 'file' : 'folder';
                    $(e.currentTarget).trigger(`${type}:delete`);
                }
            }]
        });
        
        this.locker.cannot('open', this.currentFolderConfig, () => {
            menu.element.find('[data-id="0"]').remove();
        });
        this.locker.cannot('edit', this.currentFolderConfig, () => {
            menu.element.find('[data-id="1"]').remove();
        });
        this.locker.cannot('share', this.currentFolderConfig, () => {
            menu.element.find('[data-id="2"]').remove();
        });
        this.locker.cannot('delete', this.currentFolderConfig, () => {
            menu.element.find('[data-id="3"]').remove();
        });
    }
    
    /**
     * 
     * @returns {undefined}
     */
    contentMenu()
    {        
        if(this.currentPath === 'shared'){
            return;
        }
        
        let menu = $.contextMenu({
            targets: '#package-content',
            menu: [
                {
                    name: this.trans('new folder'),
                    icon: `folder-plus`,
                    callback: (e, menu, position) => {
                        $(document).trigger('folder:create');
                    }
                },{
                    name: this.trans('upload files'),
                    icon: `upload`,
                    callback: () => {
                        $(document).trigger('file:upload');
                    }
                }
            ]
        });
        
        this.locker.cannot('upload', this.currentFolderConfig, () => {
            menu.element.find('[data-id="0"]').remove();
            menu.element.find('[data-id="1"]').remove();
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
        if (typeof feather === "undefined") {
            $.getScript('https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js', () => {
                feather.replace();
            });
        }
        if(typeof toastme === "undefined"){
            $('<link/>', {rel: 'stylesheet',type: 'text/css',href: 'https://unpkg.com/toastmejs@latest/dist/css/toastme.css'}).appendTo('head');
            $.getScript('https://unpkg.com/toastmejs@1.2.2/dist/js/toastme.min.js', () => {
                window.toastme = toastme;
            });
        }

        if (typeof jQuery.ui === "undefined") {
            $.getScript('https://code.jquery.com/ui/1.12.1/jquery-ui.js', () => {
                callback();
            });
        } else {
            callback();
        }

    }

    /**
     * Load the content
     * 
     * @returns {undefined}
     */
    loadContent(setUrl = false, retries = 0)
    {
        let url = setUrl ? setUrl : this.url(`load/content`);
        this.pageFolders = 1;
        this.pageFiles = 1;
        this.domContent.html(this.loader());
        $.get(url, (response) => {

            this.domContent.html(response);
            if(!this.domPackage.data('load-header')){
                this.domContent.find('.breadcrumb').remove();
            }
            
            this.domDetails.find('.button').remove();
            
            this.loadFolders(() => {
                this.loadFiles(() => {
                    this.setContentPlugins();
                });
            });

        }).fail(() => {
            if (retries < 2) {
                this.loadContent(setUrl, retries + 1);
            }
        });
        ;
    }

    /**
     * Load the content with new path
     * 
     * @param {type} path
     * @param {type} driver
     * @returns {undefined}
     */
    load(path = "", driver = false)
    {
        if (driver) {
            $('.drive.active').removeClass('active');
            $(`.drive[data-slug="${driver}"]`).addClass('active');
        }
        
        this.setElements();
        this.domPackage.data('start', true);
            
        this.loadContent(
            this.setUrl('load/content', path)
        );
    }

    /**
     * Load the folders
     * 
     * @returns {undefined}
     */
    loadFolders(callback = null, retries = 0)
    {
        this.domPackage.find('.folders').append(this.loader());

        $.get(this.url('get/folders'), (response) => {
            this.domPackage.find('.folders').append(response);
            $('.folders .breeding-rhombus-spinner').remove();
            this.checkSizes();
            if (callback) {
                return callback();
            }
        }).fail(() => {
            if (retries < 2) {
                this.loadFolders(callback, retries + 1);
            }
        });
    }

    /**
     * Load the files
     * 
     * @returns {undefined}
     */
    loadFiles(callback = null, retries = 0)
    {
        this.domPackage.find('.files').append(this.loader());
        $.get(this.url('get/files'), (response) => {
            this.domPackage.find('.files').append(response);
            $('.files .breeding-rhombus-spinner').remove();
            this.checkSizes();
            if (callback) {
                return callback();
            }
        }).fail(() => {
            if (retries < 2) {
                this.loadFiles(callback, retries + 1);
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
        this.loadContextMenus('destroy');
        $('.nu-context-menu').not(':first').remove();

        feather.replace();

        $('#package-content .files').selectable({
            filter: ".file,.folder,.load-more",
            cancel: ".file,.folder,.load-more",
            start: () => {
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
        this.domDetails = $('#package-details');
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
        let currentPath = this.currentPath ? this.currentPath : $('.drive.active').data('slug');

        this.currentPath = addition ? currentPath + "/" + addition : currentPath;

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
        if (this.config.trans[key]) {
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
        if (bytes === 0)
            return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    /**
     * Set a checker. Retry every {duration} till condition is true
     * 
     * @type @var;check|Boolean
     */
    check(condition = false, closure, duration = 400, counter = 0)
    {
        $(document).ready(() => {
            if (condition) {
                closure();
            } else {
                if (counter > 10) {
                    console.error(`the ${closure} could not be loaded after 10 retries.`);
                    return;
                }
                setTimeout(() => {
                    this.check(condition, closure, duration, counter + 1);
                }, duration);
            }
        });
    }
}

window.filemanager = new FileManager;
export default filemanager;