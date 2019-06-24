if(typeof $ === "undefined"){
    window.$ = window.jQuery = require('./jquery-3.4.1');
}

import contextMenu from './contextual';
import ContentBox from './contentBox';

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
        
        ContentBox.loader = this.loader();
        
        this.loadRequiredPlugins(() => {
            this.setElements();
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
    build()
    {
        $('#package-filemanager').disableSelection();
        if($('.sidebar-button.active').length === 0){
            $('.sidebar-button').first().addClass('active');
        }
        
        this.loadContent();
        this.loadContextMenus();
        this.loadTriggers();
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
        $(document).on('submit', '#renameContent', (e) => { this.submitRenameContent(e); });
        $(document).on('submit', '#addFolder', (e) => { this.submitAddFolder(e); });
        $(document).on('submit', '#shareContent', (e) => { this.submitShareContent(e); });
        
        $(document).on('change', 'input[type="file"].filemanager-files', (e) => { this.uploadFiles(e); });
        
        $(document).on('click', '#package-filemanager .sidebar-button.drive', (e) => { this.changeDrive(e); });
        $(document).on('click', '#package-content .file, #package-content .folder', (e) => { this.clickFile(e); });
        $(document).on('click', '#package-content .breadcrumb li a', (e) => {
            e.preventDefault();
            let element = $(e.currentTarget);
            this.loadContent(this.setUrl('load/content', element.attr('href')));
        });
                
        $(document).on('click', '.selectOnClick', (e) => {   this.copyToClipboard(e.currentTarget); });
                
        $(document).on('dblclick', '#package-content .file, #package-content .folder', (e) => { this.doubleClickFile(e); });
        
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
                        this.renameContent(e);
                    }
                },
                {
                    name: this.trans('details'),
                    icon: `clipboard`,
                    callback : (e) => {
                        this.getContentDetails(e);
                    }
                },
                {
                    name: this.trans('share'),
                    icon: `share-2`,
                    callback : (e) => {
                        this.shareContent(e);
                    }
                },
                {
                    name: this.trans('delete'),
                    icon: `trash`,
                    callback : (e) => {
                        $(e.currentTarget).addClass('active');
                        this.deleteContent();
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
                        this.showUploadDialog();
                    }
                },{
                    name: this.trans('new folder'),
                    icon: `folder-plus`,
                    callback : (e, menu, position) => {
                        this.showCreateFolderDialog(position);
                    }
                }
            ]
            
        });
    }
    
    /**
     * Delete selected content
     * 
     * @param {type} e
     * @returns {undefined}
     */
    deleteContent()
    {
        $(`#package-content .file.active, #package-content .folder.active`).each((index, active) => {
            let element = $(active);
            let url = element.hasClass('folder') ? this.url('delete/folder') : this.url('delete/file');

            $.post(url, {_method:"delete", _token : this.config._token, item : element.data('id')}, () => {
                element.hide('slow', () => {
                    element.remove();
                });
            });
        });
    }
    
    /**
     * Show the dialog for renaming content
     * 
     * @param {type} e
     * @returns {undefined}
     */
    renameContent(e)
    {   
        let element = $(e.currentTarget);

        ContentBox.header = `<b>${this.trans('rename')}</b> ${element.find('.label').html()}`;
                
        ContentBox.show(element);
        
        $.post(this.url('details/file'), {_token : this.config._token, item : element.data('id')}, (response) => {
            ContentBox.content = `
                <form id="renameContent">
                    <input type="hidden" name="item" value="${response.id}">
                    <label>${this.trans('filename')}</label>
                    <input type="text" name="rename" data-id="${element.data('id')}" value="${response.filename}" />
                    <button class="cancel button button-default button-small" type="button">${this.trans('cancel')}</button>
                    <button class="button button-green button-small">${this.trans('rename')}</button>
                </form>
            `;
        });
    }
    
    /**
     * Submit the new filename
     * 
     * @param {type} e
     * @returns {undefined}
     */
    submitRenameContent(e)
    {
        e.preventDefault();
        
        let data = {
            _token: this.config._token,
            _method: "put",
            item: $(e.currentTarget).find('input[name="item"]').val(),
            rename: $(e.currentTarget).find('input[name="rename"]').val()
        };
        
        $.post(this.url('rename/file'), data, (response) => {
           $(`[data-id="${response.id}"]`).find('.label').html(response.filename);
           ContentBox.hide();
        });
    }
    
    /**
     * Get the content info off the file
     * 
     * @param {type} e
     * @returns {undefined}
     */
    getContentDetails(e)
    {
        let element = $(e.currentTarget);
        
        ContentBox.show(element, {height : 380});
        
        $.post(this.url('details/file'), {_token : this.config._token, item : element.data('id')}, (response) => {
            
            ContentBox.header = response.filename;
            
            ContentBox.content = `
                <table class="table filemanager-table">
                    <tr>
                        <td>${this.trans('filename')}</td>
                        <td>${response.filename}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('extension')}</td>
                        <td>${response.extension}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('size')}</td>
                        <td>${this.formatBytes(response.size)}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('mimetype')}</td>
                        <td>${response.mimetype}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('created at')}</td>
                        <td>${response.created_at}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('last updated')}</td>
                        <td>${response.updated_at}</td>
                    </tr>
                    <tr>
                        <td>${this.trans('uploader')}</td>
                        <td>${response.uploader ? response.uploader : this.trans('unknown')}</td>
                    </tr>
                </table>
            `;
        });
    }
    
    shareContent(e)
    {
        let element = $(e.currentTarget);
        let type = element.hasClass('folder') ? 'folder' : 'file';
        ContentBox.show(element, {height:400});
        ContentBox.header = `${this.trans('share '+type)}`;
        
        $.post(this.url('details/file'), {_token : this.config._token, item : element.data('id'), type : type}, (response) => {
            
            ContentBox.content = `
                <form id="shareContent">
                    <input type="hidden" name="item" value="${response.id}">
                    <input type="hidden" name="type" value="${type}">
                    <label>${this.trans('enter email of the user(s)')}</label>
                    <input autocomplete="off" type="text" name="email" />
                    <button class="cancel button button-default button-small" type="button">${this.trans('cancel')}</button>
                    <button class="button button-green button-small">${this.trans('share')}</button>
                    <br><br>
                    ${this.trans('or anyone with this link')}<br><br>
                    <span class="selectOnClick">${this.config.mediaUrl}/${response.basepath}</span>
                </form>
            `;            
        });
    } 
    
    /**
     * Submit and psot the shared files and folders
     * 
     * @param {type} e
     * @returns {undefined}
     */
    submitShareContent(e)
    {
        e.preventDefault();
        
        let data = {
            _token: this.config._token,
            item: $(e.currentTarget).find('input[name="item"]').val(),
            type: $(e.currentTarget).find('input[name="type"]').val(),
            email: $(e.currentTarget).find('input[name="email"]').val()
        };
        
        $.post(this.url('share/content'), data, (response) => {
            ContentBox.hide();
            this.loadContent();
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
            $.getScript('https://code.jquery.com/ui/1.12.1/jquery-ui.js', () =>{
                callback();
            });
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

        this.domContent.html(this.loader());
        
        $.get(url, (response) => {
            this.domContent.html(response);
            $('.nu-context-menu').not(':first').remove();
            feather.replace();
            
            $('#package-content .files').selectable({
                filter: ".file,.folder",
                cancel: ".file,.folder",
                start : () => {
                    $(document).trigger('click');
                },
                classes: {
                    "ui-selected": "active"
                }
            });
            
        });
    }
    
    /**
     * Upload the selected files
     * 
     * @param {type} e
     * @returns {undefined}
     */
    uploadFiles(e)
    {
        let element = $(e.currentTarget);
        $(`#package-content .uploader`).remove();
        $('#package-content').append(`
            <div class="uploader">
                <div class="header">
                    ${element[0].files.length} ${this.trans('files uploading')}
                </div>
                <div class="uploads"></div>
            </div>
        `);
        
        $.each(element[0].files, (index, file) => {
            let id = this.unique();
            $('#package-content .uploader .uploads').append(`
                <div class="file-list" data-id="${id}"><div class="file-name">${file.name} | ${file.type}</div><div class="file-progress">0 %</div></div>
            `);
                        
            let fd = new FormData(); 
            fd.append('file', file); 
            fd.append('_token', this.config._token);
            
            $.ajax({
                url: this.url('upload/files'),
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                xhr: function() {
                    let xhr = $.ajaxSettings.xhr();
                    xhr.upload.onprogress = function(e) {                       
                        $(`div[data-id="${id}"] .file-progress`).html(Math.floor(e.loaded / e.total *100) + '%');
                    };
                    return xhr;
                },
                success: (response) => {
                    if(index === (element[0].files.length - 1)){
                        this.loadContent();
                    }
                }
            });
        });        
    }
    
    /**
     * Set the file to be selected
     * 
     * @param {type} e
     * @returns {undefined}
     */
    clickFile(e)
    {
        if(!e.ctrlKey){
            $('#package-content .file, #package-content .folder').removeClass('active');
        }
        let element = $(e.currentTarget);
        element.toggleClass('active');
    }
    
    /**
     * DOuble click file or folder
     * 
     * @param {type} e
     * @returns {Boolean}
     */
    doubleClickFile(e)
    {
        let element = $(e.currentTarget);
        
        if(element.hasClass('folder')){
            let path = element.data('slug');
            this.loadContent(this.setUrl('load/content', path));
            return true;
        }
    }
    
    /**
     * Show to dailog to create a new folder
     * 
     * @param {type} position
     * @returns {undefined}
     */
    showCreateFolderDialog(position)
    {
        
        ContentBox.header = `${this.trans('add folder')}`;
        ContentBox.show();
        ContentBox.content = `
            <form id="addFolder">
                <label>${this.trans('folder name')}</label>
                <input autocomplete="off" type="text" name="name" />
                <button class="cancel button button-default button-small" type="button">${this.trans('cancel')}</button>
                <button class="button button-green button-small">${this.trans('add folder')}</button>
            </form>
        `;
    }
    
    /**
     * Submit to add a new folder
     * 
     * @param {type} e
     * @returns {undefined}
     */
    submitAddFolder(e)
    {
        e.preventDefault();
        
        let data = {
            _token: this.config._token,
            name: $(e.currentTarget).find('input[name="name"]').val()
        };
        
        $.post(this.url('create/folder'), data, () => {
            ContentBox.hide();
            this.loadContent();
        });
    }
        
    
    /**
     * Set the important dom elements
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
     * Set the Url
     * 
     */
    url(path, addition = false)
    {     
        let currentPath = this.currentPath ? this.currentPath : $('.sidebar-button.active').data('slug');
        
        this.currentPath = addition ? currentPath+"/"+addition : currentPath;
        
        let url = location.href.replace('#!', '').replace('#', '');

        let split = url.split('?');
        
        url = `${split[0]}/${path}${split.length > 1 ? `?${split[1]}` : ``}`

        let mark = url.includes('?') ? '&' : '?';
                
        return `${url}${mark}path=${this.currentPath}`;
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
     * Trigger the upload dialog
     * 
     * @returns {undefined}
     */
    showUploadDialog()
    {
        $('.filemanager-upload-filedialog').remove();
        let id = this.unique();
        $(`body`).append(`
            <form style="display:none;" class="filemanager-upload-filedialog" id="${id}">
                <input class="filemanager-files" data-id="${id}" type="file" name="files" multiple>
            </form>
        `);
        
        $(`#${id}`).find('input[type="file"]').trigger('click');
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


window.filemanager = new FileManager;