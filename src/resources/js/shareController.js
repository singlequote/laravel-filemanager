class ShareController
{
    /**
     * 
     * @param {type} FileManager
     * @returns {ShareController}
     */
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.box = FileManager.box;
        this.loadTriggers();
    }
    
    /**
     * Load the shared triggers
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        $(document).on('file:share', ".file, .file-button", (e) => {
            this.shareFile(e);
        });
        
        $(document).on('folder:share', ".folder, .folder-button", (e) => {
            this.shareFolder(e);
        });
        
        $(document).on('submit', "#shareContent", (e) => {
            e.preventDefault();
            this.pushSharedContent(e);
        });
        
        $(document).on('file:delete-shared', ".file, .file-button", (e) => {
            this.deleteShared(e);
        });
        
        $(document).on('folder:delete-shared', ".folder, .folder-button", (e) => {
            this.deleteShared(e);
        });
    }
    
    /**
     * Share a file
     * 
     * @param {type} e
     * @returns {undefined}
     */
    shareFile(e)
    {
        this.box.title = this.FileManager.trans('share file');
        
        $.post(this.FileManager.url('details/file'), {_token : this.FileManager.config._token, item : $(e.currentTarget).data('id')}, (response) => {
            this.box.content = `
                <form id="shareContent">
                    <input type="hidden" name="item" value="${response.id}">
                    <input type="hidden" name="type" value="file">
                    <label>${this.FileManager.trans('enter email of the user(s)')}</label>
                    <input placeholder="foo@bar.com" autocomplete="off" type="text" name="email" /><br><br>
                    <button class="button button-green button-small">${this.FileManager.trans('share')}</button>
                    <br><br>
                    <!--${this.FileManager.trans('or anyone with this link')}<br><br>
                    <span class="selectOnClick">${this.FileManager.config.mediaUrl}/${response.basepath}</span>-->
                </form>
            `;
        });
    }
    
    /**
     * Share a file
     * 
     * @param {type} e
     * @returns {undefined}
     */
    shareFolder(e)
    {
        this.box.title = this.FileManager.trans('share folder');
        
        $.post(this.FileManager.url('details/folder'), {_token : this.FileManager.config._token, item : $(e.currentTarget).data('id')}, (response) => {
            this.box.content = `
                <form id="shareContent">
                    <input type="hidden" name="item" value="${response.id}">
                    <input type="hidden" name="type" value="folder">
                    <label>${this.FileManager.trans('enter email of the user(s)')}</label>
                    <input placeholder="foo@bar.com" autocomplete="off" type="text" name="email" /><br><br>
                    <button class="button button-green button-small">${this.FileManager.trans('share')}</button>
                    <br><br>
                    <!--${this.FileManager.trans('or anyone with this link')}<br><br>
                    <span class="selectOnClick">${this.FileManager.config.mediaUrl}/${response.basepath}</span>-->
                </form>
            `;
        });
    }
   
    /**
     * Submit and post the shared files and folders
     * 
     * @param {type} e
     * @returns {undefined}
     */
    pushSharedContent(e)
    {
        let type = $(e.currentTarget).find('input[name="type"]').val();
        
        let data = {
            _token: this.FileManager.config._token,
            item: $(e.currentTarget).find('input[name="item"]').val(),
            email: $(e.currentTarget).find('input[name="email"]').val()
        };
        
        $.post(this.FileManager.url('share/'+type), data, (response) => {
            this.box.hide();
            this.FileManager.loadContent();
        });
    }
    
    /**
     * Delete shared content
     * 
     * @param {type} e
     * @returns {undefined}
     */
    deleteShared(e)
    {
        let type = $(e.currentTarget).hasClass('.file-button') ? "file" : "folder";
        
//        
        $.post(this.FileManager.url('details/'+type), {_token : this.FileManager.config._token, item : $(e.currentTarget).data('id')}, (response) => {
            $.post(this.FileManager.url('shared'), {_method:"delete", _token : this.FileManager.config._token, item : response.id}, () => {
                $(e.currentTarget).remove();
            });
        });
    }
    
}

export default ShareController;