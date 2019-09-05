class FileController
{
    /**
     * 
     * @param {type} FileManager
     * @returns {FileController}
     */
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.locker = FileManager.locker;
        this.box = FileManager.box;
        this.loadTriggers();
    }

    /**
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        $(document).on('click', '.file', (e) => {
            this.show(e);
            this.trigger(e);
        });
        
        $(document).on('file:details', '.file, .file-button', (e) => {
            this.show(e);
        });
        
        $(document).on('file:open', '.file, .file-button', (e) => {
            this.open(e);
        });
        
        $(document).on('file:edit', '.file, .file-button', (e) => {
            this.edit(e);
        });
        
        $(document).on('file:delete', '.file, .file-button', (e) => {
            this.delete(e);
        });
        
        $(document).on('dblclick', '.file', (e) => {
            if(!this.FileManager.modal){
                return this.show(e);
            }
            return this.getConfig(e);
        });
        
        $(document).on('file:upload', (e) => {
            this.create(e);
        });

        $(document).on('change', 'input[type="file"].filemanager-files', (e) => {
            this.store(e);
        });

        $(document).on('submit', '#editFile', (e) => {
            e.preventDefault();
            this.update(e);
        });
        
        $(document).on('click', '.load-more[data-type="files"]', (e) => {  
            $(e.currentTarget).remove();
            this.FileManager.pageFiles = this.FileManager.pageFiles + 1;
            this.FileManager.loadFiles(() => {
                this.FileManager.setContentPlugins();
            });
        });
    }

    /**
     * 
     * @param {type} e
     * @returns {undefined}
     */
    trigger(e)
    {
        if (!e.ctrlKey) {
            $('#package-content .file, #package-content .folder').removeClass('active');
        }

        let element = $(e.currentTarget);

        element.toggleClass('active');
    }
    
    /**
     * 
     * @param {type} e
     * @returns {jqXHR}
     */
    getConfig(e)
    {
        $.post(this.FileManager.url('details/file'), {_token: this.FileManager.config._token, item: $(e.currentTarget).data('id')}, (response) => {
            $(document).trigger('laravel-filemanager:select', response);
        });
    }
    
    /**
     * 
     * @param {type} e
     * @param {type} el
     * @returns {undefined}
     */
    open(e, el = false)
    {
        let element = e ? $(e.currentTarget) : el;
        this.locker.can('open', element.data('id'), (config) => {
            window.open(`${this.FileManager.config.mediaUrl}/${config.basepath}`, '_blank');
        });
    }

    /**
     * 
     * @param {type} e
     * @returns {undefined}
     */
    show(e, el = false)
    {
        let element = e ? $(e.currentTarget) : el;

        $.post(this.FileManager.url('details/file'), {_token: this.FileManager.config._token, item: element.data('id')}, (response) => {
            this.box.title = response.filename;

            this.box.content = response.content
            
            this.locker.cannot('edit', response, () => {
                $(`.details-edit[data-id="${response.id}"]`).remove();
            });
            this.locker.cannot('delete', response, () => {
                $(`.details-delete[data-id="${response.id}"]`).remove();
            });
            
            this.box.show();
            feather.replace();
            this.FileManager.replaceSize();
        });
    }

    /**
     * 
     * @param {type} e
     * @returns {undefined}
     */
    create(e)
    {     
        $('.filemanager-upload-filedialog').remove();

        let id = this.FileManager.unique();

        $(`body`).append(`
            <form style="display:none;" class="filemanager-upload-filedialog" id="${id}">
                <input class="filemanager-files" data-id="${id}" type="file" name="files" multiple>
            </form>
        `);

        $(`#${id}`).find('input[type="file"]').trigger('click');
    }

    /**
     * Upload the selected files
     * 
     * @param {type} e
     * @returns {undefined}
     */
    store(e)
    {
        let element = $(e.currentTarget);
        $(`#package-content .uploader`).remove();
        $('#package-content').append(`
            <div class="uploader">
                <div class="header">
                    ${element[0].files.length} ${this.FileManager.trans('files uploading')}
                </div>
                <div class="uploads"></div>
            </div>
        `);

        $.each(element[0].files, (index, file) => {
            let id = this.FileManager.unique();
            $('#package-content .uploader .uploads').append(`
                <div class="file-list" data-id="${id}"><div class="file-name">${file.name} | ${file.type}</div><div class="file-progress">0 %</div></div>
            `);
            setTimeout(() => {
                this.upload(id, element, index, file);
            }, 500);
''        });
    }
    
    /**
     * Show the edit content
     * 
     * @param {type} e
     * @param {type} el
     * @returns {undefined}
     */
    edit(e, el = false)
    {
        let element = e ? $(e.currentTarget) : el;

        this.locker.can('edit', element.data('id'), (response) => {
            
            this.box.title = response.filename;
            this.box.content = `
                <form id="editFile">
                    <input type="hidden" name="item" value="${response.id}">
                    <label>${this.FileManager.trans('filename')}</label>
                    <input type="text" name="rename" data-id="${element.data('id')}" value="${response.filename}" /><br><br>
                    <!--<button class="cancel button button-default button-small" type="button">${this.FileManager.trans('cancel')}</button>-->
                    <button class="button button-green button-small">${this.FileManager.trans('rename')}</button>
                </form>
            `;
            this.box.show();
            
        });
    }
    
    /**
     * Upload the file to the server
     * 
     * @param {type} id
     * @param {type} element
     * @param {type} index
     * @param {type} file
     * @returns {undefined}
     */
    upload(id, element, index, file)
    {
        let fd = new FormData();
        fd.append('file', file);
        fd.append('_token', this.FileManager.config._token);
        
        $.ajax({
            url: this.FileManager.url('upload/files'),
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            xhr: function () {
                let xhr = $.ajaxSettings.xhr();
                xhr.upload.onprogress = function (e) {
                    $(`div[data-id="${id}"] .file-progress`).html(Math.floor(e.loaded / e.total * 100) + '%');
                };
                return xhr;
            },
            success: (response) => {
                if (index === (element[0].files.length - 1)) {
                    this.FileManager.loadContent();
                }
            }
        }).fail((response) => {
            $(`div[data-id="${id}"]`).css('color', '#ff0000').find('.file-progress').html(response.responseJSON.message);
        });
    }
    
    /**
     * Update the file
     * 
     * @param {type} e
     * @returns {undefined}
     */
    update(e)
    {       
        let data = {
            _token: this.FileManager.config._token,
            _method: "put",
            item: $(e.currentTarget).find('input[name="item"]').val(),
            rename: $(e.currentTarget).find('input[name="rename"]').val()
        };

        $.post(this.FileManager.url('rename/file'), data, (response) => {
            $(`[data-id="${response.id}"]`).find('.label').html(response.filename);
            this.show(false, $(`[data-id="${response.id}"]`));
        });
    }

    /**
     * Delete selected content
     * 
     * @param {type} e
     * @returns {undefined}
     */
    delete(e)
    {
        $(e.currentTarget).addClass('active');
        $(`#package-content .file.active, #package-content .folder.active`).each((index, active) => {
            let element = $(active);
            let url = element.hasClass('folder') ? this.FileManager.url('delete/folder') : this.FileManager.url('delete/file');

            $.post(url, {_method: "delete", _token: this.FileManager.config._token, item: element.data('id')}, () => {
                element.hide('slow', () => {
                    element.remove();
                    if(!$(`#package-content .file`).length){
                        this.FileManager.loadContent();
                    }
                });
            });
        });
    }

}

export default FileController;