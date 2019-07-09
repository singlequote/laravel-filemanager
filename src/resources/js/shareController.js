//    let element = $(e.currentTarget);
//    let type = element.hasClass('folder') ? 'folder' : 'file';
//    ContentBox.show(element, {height:400});
//    ContentBox.header = `${this.trans('share '+type)}`;
//
//    $.post(this.url('details/file'), {_token : this.config._token, item : element.data('id'), type : type}, (response) => {
//
//        ContentBox.content = `
//            <form id="shareContent">
//                <input type="hidden" name="item" value="${response.id}">
//                <input type="hidden" name="type" value="${type}">
//                <label>${this.trans('enter email of the user(s)')}</label>
//                <input autocomplete="off" type="text" name="email" />
//                <button class="cancel button button-default button-small" type="button">${this.trans('cancel')}</button>
//                <button class="button button-green button-small">${this.trans('share')}</button>
//                <br><br>
//                ${this.trans('or anyone with this link')}<br><br>
//                <span class="selectOnClick">${this.config.mediaUrl}/${response.basepath}</span>
//            </form>
//        `;            
//    });


class ShareController
{
    
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.box = FileManager.box;
        this.loadTriggers();
    }
    
    loadTriggers()
    {
        $(document).on('file:share', ".file, .file-button", (e) => {
            this.shareFile(e);
        });
        
        $(document).on('submit', "#shareContent", (e) => {
            e.preventDefault();
            this.pushSharedContent(e);
        });
        
        $(document).on('file:delete-shared', ".file, .file-button, .folder, .folder-button", (e) => {
            this.deleteShared(e);
        });
    }
    
    
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
                    <!--<button class="cancel button button-default button-small" type="button">${this.FileManager.trans('cancel')}</button>-->
                    <button class="button button-green button-small">${this.FileManager.trans('share')}</button>
                    <br><br>
                    ${this.FileManager.trans('or anyone with this link')}<br><br>
                    <span class="selectOnClick">${this.FileManager.config.mediaUrl}/${response.basepath}</span>
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
    
    deleteShared(e)
    {
        let type = $(e.currentTarget).hasClass('.file-button') ? "file" : "folder";
        
        $.post(this.FileManager.url('shared/'))
//        $.post(this.FileManager.url('details/'+type), {_token : this.FileManager.config._token, item : $(e.currentTarget).data('id')}, (response) => {
//            
//        });
    }
    
}

export default ShareController;