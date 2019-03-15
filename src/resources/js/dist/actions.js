import Swal from './sweetalert';

/**
 * 
 */
export default class FilemanagerAction{
    
    /**
     * 
     * return FilemanagerAction
     */
    constructor(parent)
    {
        this.parent     = parent;
        this.template   = parent.template;
        this.modal      = parent.modal;
        this.loadEvents();
        
        this.actions    = ['edit', 'delete', 'crop'];
    }
    
    /**
     * enable actions
     * 
     * @param {array} actions
     * @returns {undefined}
     */
    enable(...actions)
    {
        this.disable(this.actions);
        $.each(actions, (key, action) =>  {
            $(`[data-action="${action}"]`).removeAttr('disabled');
        });
    }

    /**
     * disable actions
     * 
     * @param {array} actions
     * @returns {undefined}
     */
    disable(actions)
    {
        $.each(actions, (key, action) => {
            $(`[data-action="${action}"]`).attr('disabled', 'disabled');
        });
    }
    
    /**
     * Load the triggers
     * 
     */
    loadEvents()
    {
        let self = this;
        $(document).on('click', `[data-action='cache']:not([disabled])`, function (e) {
            e.preventDefault();
            $.post(`${self.parent.url}/action/clear`, {_token : self.parent._token}, () => self.parent.message('cache cleared'));
        });
        $(document).on('click', `[data-action='upload']:not([disabled])`, function (e) {
            e.preventDefault();
            self.upload(e, $(this));
        });
        $(document).on('click', `[data-action='new']:not([disabled])`, function (e) {
            e.preventDefault();
            self.create(e, $(this));
        });
        $(document).on('click', `[data-action='edit']:not([disabled])`, function (e) {
            e.preventDefault();
            self.edit(e, $(this));
        });
        $(document).on('click', `[data-action='delete']:not([disabled])`, function (e) {
            e.preventDefault();
            self.delete(e, $(this));
        });
        $(document).on('click', `[data-action='crop']:not([disabled])`, function (e) {
            e.preventDefault();
            self.crop(e, $(this));
        });
        $(document).on('submit', `${this.parent.doms.modalPreview} .body form`, (e) => {
            e.preventDefault();
            self.submitEditForm(e);
        });
    }
    
    /**
     * Upload new files
     * 
     * @param {type} event
     * @param {type} element
     * @returns {undefined}
     */
    upload(event, element)
    {
//        event.preventDefault();
//        let self = this;
//        $(this.doms.modalPreview).find('.modal-body').html(`
//            <form class="dropzone" id="fileUploadForm"><input type="hidden" name="_token" value="${this._token}"><input type="hidden" name="folder" value="${this.addition}"></form>
//        `);
//        var uploadZone = new Dropzone("#fileUploadForm", { url: this.url+this.routes.actions.upload });
//        Dropzone.options.fileUploadForm = {
//            init: function () {
//                this.on("complete", function (file) {
//                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
//                        self.loadContent();
//                    }
//                });
//            }
//        };
//        $(this.doms.modalPreview).modal('show');
    }
    
        /**
     * Create a new folder
     * 
     * @returns {undefined}
     */
    create(event, element)
    {
//        $(this.doms.modalPreview).find('.body').html(`
//            <br>
//            <form id="action-form" method="post" action="${this.url}${this.routes.actions.new}">
//                <input type="hidden" name="folder" value="${this.addition}">
//                <input type="hidden" name="_token" value="${this._token}">
//                <div class="row">
//                    <div class="col-12">
//                        <input type="text" name="name" placeholder="Folder..." required="true" class="form-control">
//                    </div>
//                    <div class="col-12">
//                        <br> 
//                        <button type="submit" class="btn btn-success">Save</button>
//                    </div>
//                </div>
//            </form>
//        `);
//        $(this.doms.modalPreview).modal('show');
    }
    
    crop(event, element)
    {
//        this.template.loadTemplate('modals.modal-preview', (response) => {
//            this.template.parseTemplate({filename : $('.activeFile').find('.label').html()}, 'modals.modal-preview', this.parent.doms.modals, true);
            this.modal.plugin('cropper', {route : $('.activeFile').data('route'), filename : $('.activeFile').find('.label').html()});
//            this.modal.show(this.parent.doms.modalPreview, {width:'50%'});
//        });
        
    }
    
    /**
     * Remove an item fron the content
     * 
     * @param {mixed} event
     * @param {mixed} element
     * @returns {undefined}
     */
    delete(event, element)
    {
        Swal({
            title: 'Are you sure?',
            type: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.value) {
                this.removeFolders();
                this.removeFiles();
            }
        });
    }
    
    /**
     * Remove selected folders
     * 
     * @returns {undefined}
     */
    removeFolders()
    {
        $('.activeFolder').each((key, active) => {
            $.post(`${this.parent.url}/action/delete`, {
                type:'folder',
                _token: this.parent._token,
                route: $(active).data('route'),
                _method: 'delete'
            }, () =>  {
                $(`[lf="${$(active).attr('lf')}"]`).parent().hide('slow', function() {
                    $(this).remove();
                });
                if(key+1 === $('.activeFolder').length){
                    this.parent.message('Deleted', 'Folder removed');
                }
            });
        });
    }
    
    /**
     * Remove selected files
     * 
     * @returns {undefined}
     */
    removeFiles()
    {
        $('.activeFile').each((key, active) => {
            $.post(`${this.parent.url}/action/delete`, {
                type:'file',
                _token: this.parent._token,
                route: $(active).data('route'),
                _method: 'delete'
            }, () =>  {
                $(`[lf="${$(active).attr('lf')}"]`).parent().hide('slow', function() {
                    $(this).remove();
                });
                if(key+1 === $('.activeFile').length){
                    this.parent.message('Deleted', 'File removed');
                }
            });
        });
    }
    
    /**
     * Open the modal for renaming content items
     * 
     * @param {mixed} event
     * @param {mixed} element
     * @returns {undefined}
     */
    edit(event, element)
    {
        let item = $('.activeFile').length > 0 ? $('.activeFile') : $('.activeFolder');
        let type = $('.activeFile').length > 0 ? 'file' : 'folder';
        this.template.loadTemplate(`modals.modal-preview`, () => {
            this.template.parseTemplate({filename : item.find('.label').html()}, 'modals.modal-preview', this.parent.doms.modals);
            this.modal.show(this.parent.doms.modalPreview, {width : "40%"});
        });
        
        this.template.loadTemplate(`forms.edit-name`, () => {
            let mark = this.parent.url.includes('?') ? '&' : '?';
            $.get(`${this.parent.url}${mark}${type}=${item.data('route')}`, (response) => {
                this.template.parseTemplate({
                    type : type,
                    route : response.path,
                    _token : this.parent._token,
                    action : `${this.parent.url}/action/edit`,
                    filename : response.filename
                }, 'forms.edit-name', `${this.parent.doms.modalPreview} .body`, true);
            });
        });
    }
    
    /**
     * Submit the edit form
     * 
     */
    submitEditForm(event)
    {
        let form = $(event.target);
        $.post( form.attr('action'), form.serialize() , (response) => {
            this.parent.loadContent();
            this.modal.hide();
        });
    }
    
    
    
}