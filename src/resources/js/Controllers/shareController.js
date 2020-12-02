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
//        this.locker = FileManager.locker;
        this.loadTriggers();
    }
    
    /**
     * Load the shared triggers
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        $(document).off('file:share', ".file, .file-button");
        $(document).on('file:share', ".file, .file-button", (e) => {
            this.shareFile(e);
        });
        
        $(document).off('folder:share', ".folder, .folder-button");
        $(document).on('folder:share', ".folder, .folder-button", (e) => {
            this.shareFolder(e);
        });
        
        $(document).off('submit', "#shareContent");
        $(document).on('submit', "#shareContent", (e) => {
            e.preventDefault();
            this.pushSharedContent(e);
        });
        
        $(document).off('file:delete-shared', ".file, .file-button");
        $(document).on('file:delete-shared', ".file, .file-button", (e) => {
            this.deleteShared(e);
        });
        
        $(document).off('folder:delete-shared', ".folder, .folder-button");
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
        
        $.post(this.FileManager.url('details/file'), {_token: this.FileManager.config._token, item: element.data('id')}, (config) => {
            this.box.content = `
                <form id="shareContent">
                    <input type="hidden" name="item" value="${response.id}">
                    <input type="hidden" name="type" value="file">
            
                    <label>${this.FileManager.trans('enter email of the user(s)')}</label>
                    <input placeholder="foo@bar.com" autocomplete="off" type="text" name="email" /><br><br>
                    <button class="button button-green button-small">${this.FileManager.trans('share')}</button>
            
                    <br><br>
                    
                    <label>${this.FileManager.trans('permissions')}</label>
                    <div class="inputGroup">
                        <input disabled value="1" id="openOption" checked name="open" type="checkbox"/>
                        <label for="openOption">${this.FileManager.trans('can open')}</label>
                    </div>
                    <div class="inputGroup">
                        <input value="1" id="editOption" name="edit" type="checkbox"/>
                        <label for="editOption">${this.FileManager.trans('can edit')}</label>
                    </div>
                    <div class="inputGroup">
                        <input value="1" id="deleteOption" name="delete" type="checkbox"/>
                        <label for="deleteOption">${this.FileManager.trans('can delete')}</label>
                    </div>
            
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
                    
                    <label>${this.FileManager.trans('permissions')}</label>
                    <div class="inputGroup">
                        <input disabled value="1" id="openOption" checked name="open" type="checkbox"/>
                        <label for="openOption">${this.FileManager.trans('can open')}</label>
                    </div>
                    <div class="inputGroup">
                        <input value="1" id="editOption" name="edit" type="checkbox"/>
                        <label for="editOption">${this.FileManager.trans('can edit')}</label>
                    </div>
                    <div class="inputGroup">
                        <input value="1" id="deleteOption" name="delete" type="checkbox"/>
                        <label for="deleteOption">${this.FileManager.trans('can delete')}</label>
                    </div>
                    <div class="inputGroup">
                        <input value="1" id="uploadOption" name="upload" type="checkbox"/>
                        <label for="uploadOption">${this.FileManager.trans('can upload')}</label>
                    </div>
            
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
        let data = $(e.currentTarget).serializeArray();
        
        data[data.length] = {name : "_token" , value : this.FileManager.config._token};
 
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
        
        $.post(this.FileManager.url('details/'+type), {_token : this.FileManager.config._token, item : $(e.currentTarget).data('id')}, (response) => {
            $.post(this.FileManager.url('shared'), {_method:"delete", _token : this.FileManager.config._token, item : response.id}, () => {
                $(e.currentTarget).remove();
            });
        });
    }
    
}

export default ShareController;
