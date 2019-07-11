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
        this.loadTriggers();
    }

    /**
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        $(document).on('click', '.folder', '.folder, .folder-button', (e) => {
            this.show(e);
            this.trigger(e);
        });
        
        $(document).on('folder:details', '.folder, .folder-button', (e) => {
            this.show(e);
        });
        
        $(document).on('folder:edit', '.folder, .folder-button', (e) => {
            this.edit(e);
        });
        
        $(document).on('folder:delete', '.folder, .folder-button', (e) => {
            this.delete(e);
        });
        
        $(document).on('dblclick', '.folder', (e) => {
            let path = $(e.currentTarget).data('slug');
            this.FileManager.loadContent(this.FileManager.setUrl('load/content', path));
            setTimeout(() => {
                this.FileManager.domDetails.find('.button').remove();
            },500);
            
        });
        
        $(document).on('folder:create', (e) => {
            this.create(e);
        });

        $(document).on('submit', '#addFolder', (e) => {
            this.store(e);
        });
        
        $(document).on('submit', '#editFolder', (e) => {
            e.preventDefault();
            this.update(e);
        });
        
        $(document).on('click', '.load-more[data-type="folders"]', (e) => {  
            $(e.currentTarget).remove();
            this.FileManager.pageFolders = this.FileManager.pageFolders + 1;
            this.FileManager.loadFolders(() => {
//                this.FileManager.setContentPlugins();
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
     * @returns {undefined}
     */
    show(e, el = false)
    {
        let element = e ? $(e.currentTarget) : el;

        $.post(this.FileManager.url('details/folder'), {_token: this.FileManager.config._token, item: element.data('id')}, (response) => {
            this.FileManager.box.title = response.name;

            this.FileManager.box.content = response.content
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
        this.box.title = this.FileManager.trans('new folder');
        this.box.content = `
            <form id="addFolder">
                <label>${this.FileManager.trans('folder name')}</label>
                <input autocomplete="off" type="text" name="name" /><br><br>
                <!--<button class="cancel button button-default button-small" type="button">${this.FileManager.trans('cancel')}</button>-->
                <button class="button button-green button-small">${this.FileManager.trans('add folder')}</button>
            </form>
        `;
        this.box.show();
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

        $.post(this.FileManager.url('details/folder'), {_token: this.FileManager.config._token, item: element.data('id')}, (response) => {
            this.box.title = response.name;
            this.box.content = `
                <form id="editFolder">
                    <input type="hidden" name="item" value="${response.id}">
                    <label>${this.FileManager.trans('filename')}</label>
                    <input type="text" name="rename" data-id="${element.data('id')}" value="${response.name}" /><br><br>
                    <!--<button class="cancel button button-default button-small" type="button">${this.FileManager.trans('cancel')}</button>-->
                    <button class="button button-green button-small">${this.FileManager.trans('rename')}</button>
                </form>
            `;
            this.box.show();
        });
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

            $.post(url, {_method: "delete", _token: this.FileManager.config._token, item: element.data('id')}, () => {
                element.hide('slow', () => {
                    element.remove();
                });
            });
        });
    }

}

export default FolderController;