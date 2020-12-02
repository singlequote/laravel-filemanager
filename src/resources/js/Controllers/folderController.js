class FolderController
{
    /**
     * 
     * @param {type} FileManager
     * @returns {FolderController}
     */
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.box = FileManager.box;
//        this.locker = FileManager.locker;
        this.loadTriggers();
        
        this.timer;
    }

    /**
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {       
        $(document).off('folder:details', '.folder, .folder-button');
        $(document).on('folder:details', '.folder, .folder-button', (e) => {
            this.show(e);
        });
        
        $(document).off('folder:open', '.folder, .folder-button');
        $(document).on('folder:open', '.folder, .folder-button', (e) => {
            this.timer = false;
            this.open(e);
        });
        
        $(document).off('folder:edit', '.folder, .folder-button');
        $(document).on('folder:edit', '.folder, .folder-button', (e) => {
            this.edit(e);
        });
        
        $(document).off('folder:delete', '.folder, .folder-button');
        $(document).on('folder:delete', '.folder, .folder-button', (e) => {
            this.delete(e);
        });
        
        $(document).off('dblclick', '.folder');
        $(document).on('dblclick', '.folder', (e) => {
            clearTimeout(this.timer);
            this.timer = false;
            this.open(e);
        });
        
        $(document).off('click', '.folder, .folder-button');
        $(document).on('click', '.folder, .folder-button', (e) => {
            if(this.timer){
                return false;
            }
            let element = $(e.currentTarget);
            this.trigger(e);
            this.timer = setTimeout(() => {
                this.show(false, element);
                this.timer = false;
            },400);
        });
        
        $(document).off('folder:create');
        $(document).on('folder:create', (e) => {
            this.create(e);
        });
        
        $(document).off('submit', '#addFolder');
        $(document).on('submit', '#addFolder', (e) => {
            this.store(e);
        });
        
        $(document).off('submit', '#editFolder');
        $(document).on('submit', '#editFolder', (e) => {
            e.preventDefault();
            this.update(e);
        });
        
        $(document).off('click', '.load-more[data-type="folders"]');
        $(document).on('click', '.load-more[data-type="folders"]', (e) => {  
            $(e.currentTarget).remove();
            this.FileManager.pageFolders = this.FileManager.pageFolders + 1;
            this.FileManager.loadFolders(() => {
                //..
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
     * Open folder when doubleclicked item
     * 
     * @param {type} e
     * @returns {undefined}
     */
    open(e)
    {
        let path = $(e.currentTarget).data('slug');
        $.post(this.FileManager.url('details/folder'), {_token: this.FileManager.config._token, item: $(e.currentTarget).data('id')}, (response) => {
            this.FileManager.currentFolderConfig = response;
        });
        
        this.FileManager.loadContent(this.FileManager.setUrl('load/content', path));

        setTimeout(() => {
            this.FileManager.domDetails.find('.button').remove();
        },500);
    }

    /**
     * 
     * @param {type} e
     * @returns {undefined}
     */
    show(e, el = false)
    {
        let element = e ? $(e.currentTarget) : el;

        $.post(this.FileManager.url('details/folder'), {_token: this.FileManager.config._token, item: element.data('id')}, (response) => {
            this.FileManager.box.title = response.name;
            this.FileManager.box.content = response.content
            
//            this.locker.cannot('edit', response, () => {
//                $(`.details-edit[data-id="${response.id}"]`).remove();
//            });
            
//            this.locker.cannot('delete', response, () => {
//                $(`.details-delete[data-id="${response.id}"]`).remove();
//            });
            
            this.FileManager.box.show();
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
//        this.locker.can('upload', false, () => {
            this.box.title = this.FileManager.trans('new folder');
            this.box.content = `
                <form id="addFolder">
                    <label>${this.FileManager.trans('folder name')}</label>
                    <input autocomplete="off" type="text" name="name" /><br><br>
                    <button class="button button-green button-small">${this.FileManager.trans('add folder')}</button>
                </form>
            `;
            this.box.show();
//        });
    }

    /**
     * Upload the selected files
     * 
     * @param {type} e
     * @returns {undefined}
     */
    store(e)
    {
        e.preventDefault();
        
        let data = {
            _token: this.FileManager.config._token,
            name: $(e.currentTarget).find('input[name="name"]').val()
        };

        $.post(this.FileManager.url('create/folder'), data, () => {
            this.FileManager.domPackage.find(`.folders`).html(``);
            this.box.hide();
            this.FileManager.loadContent();
        });
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
        
//        this.locker.can('edit', element.data('id'), (response) => {
            this.box.title = response.name;
            this.box.content = `
                <form id="editFolder">
                    <input type="hidden" name="item" value="${response.id}">
                    <label>${this.FileManager.trans('filename')}</label>
                    <input type="text" name="rename" data-id="${element.data('id')}" value="${response.name}" /><br><br>
                    <button class="button button-green button-small">${this.FileManager.trans('rename')}</button>
                </form>
            `;
            this.box.show();
//        });
    }
    
    /**
     * Update the folder resource
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

        $.post(this.FileManager.url('rename/folder'), data, (response) => {
            $(`[data-id="${response.id}"]`).find('.label').html(response.name);
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
            
            this.locker.can('delete', element.data('id'), () => {
                $.post(url, {_method: "delete", _token: this.FileManager.config._token, item: element.data('id')}, () => {
                    element.hide('slow', () => {
                        element.remove();
                        if(!$(`#package-content .folder`).length){
                            this.FileManager.loadContent();
                        }
                    });
                });
            });
        });
    }

}

export default FolderController;
