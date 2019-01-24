/**
 * Filemanager class
 * 
 */
class FileManager
{
    /**
     * 
     * @returns {FileManager}
     */
    constructor()
    {
        this.doms       = {
            sidebar         : '#filemanager-sidebar',
            content         : '#filemanager-content',
            folder          : '.folder',
            file            : '.file',
            modalPreview    : '#filemanager-media-preview'
        };
        this.url        = location.href.replace('#', '');
        this.addition   = '';
        this._token     = null;
        this.routes     = {
            active      : null,
            load        : {
                sidebar : '/get/sidebar/',
                content : '/get/content/'
            },
            actions     : {
                edit    : '/action/edit',
                delete  : '/action/delete',
                upload  : '/action/upload',
                new     : '/action/new'
            }
        };
        this.actions    = ['edit', 'delete', 'crop'];
        this.loadSidebar();
        this.loadTriggers();
    }

    /**
     * Load the sidebar
     * 
     * @returns {undefined}
     */
    loadSidebar()
    {
        let self = this;
        $(this.doms.sidebar).html(`<div class="loader"></div>`);
        $.get(this.url + this.routes.load.sidebar + this.addition, function (response) {
            $(self.doms.sidebar).html(response);
        });
    }

    /**
     * Load the content
     * 
     * @returns {undefined}
     */
    loadContent()
    {
        let self = this;
        $(this.doms.content).html(`<div class="loader"></div>`);
        $.get(this.url + this.routes.load.content + this.addition, function (response) {
            $(self.doms.content).html(response);
        });
    }

    /**
     * Load the triggers
     * Like on click on file or folder
     * 
     * @returns {undefined}
     */
    loadTriggers()
    {
        let self = this;
        $(document).on('click', `${this.doms.sidebar} ${this.doms.folder}`, function (e) {
            e.preventDefault();
            self.triggeredFolder($(this));
        });
        $(document).on('dblclick', `${this.doms.content} ${this.doms.folder}`, function (e) {
            e.preventDefault();
            self.triggeredFolder($(this));
        });
        $(document).on('dblclick', `${this.doms.content} ${this.doms.file}`, function (e) {
            e.preventDefault();
            self.triggeredFile($(this));
        });
        $(document).on('click', `${this.doms.content} ${this.doms.folder}`, function (e) {
            e.preventDefault();
            self.clickedFolder($(this));
        });
        $(document).on('click', `${this.doms.content} ${this.doms.file}`, function (e) {
            e.preventDefault();
            self.clickedFile($(this));
        });
        //actions
        $(document).on('submit', `#action-form`, function (e) {
            e.preventDefault();
            $.post($(this).attr('action'), $(this).serialize(), function () {
                $(self.doms.modalPreview).modal('hide');
                self.loadContent();
                self.loadSidebar();
            });
        });
        $(document).on('click', `${this.doms.content} [data-action='edit']`, function (e) {
            self.actionEdit(e, $(this));
        });
        $(document).on('click', `${this.doms.content} [data-action='delete']`, function (e) {
            self.actionDelete(e, $(this));
        });
        $(document).on('click', `${this.doms.content} [data-action='upload']`, function (e) {
            self.actionUpload(e, $(this));
        });
        $(document).on('click', `${this.doms.content} [data-action='new']`, function (e) {
            self.actionNewFolder(e, $(this));
        });
    }

    /**
     * Trigger the folder click
     * Reload the content with the new route
     * 
     * @param {type} element
     * @returns {undefined}
     */
    triggeredFolder(element)
    {
        this.addition = `?folder=${element.data('route')}`;
        $(this.doms.folder).removeClass('active');
        $(`a[data-route="${element.data('route')}"]`).addClass('active');
        this.loadContent();
    }

    /**
     * Open a file
     * 
     * @param {type} element
     * @returns {undefined}
     */
    triggeredFile(element)
    {
        this.addition = `?file=${element.data('route')}`;
        $(this.doms.modalPreview).find('.modal-body').html(`<div class="loader"></div>`);
        $(this.doms.modalPreview).find('.modal-body').html(`
            <img src="${this.url}${this.addition}" class="img-fluid">
        `);
        $(this.doms.modalPreview).modal('show');
    }

    /**
     * Set folder active
     * 
     * @param {type} element
     * @returns {undefined}
     */
    clickedFolder(element)
    {
        $('.activeFolder').removeClass('activeFolder');
        $('.activeFile').removeClass('activeFile');
        element.addClass('activeFolder');
        this.enableAction('edit', 'delete');
    }

    /**
     * Set file active
     * 
     * @param {type} element
     * @returns {undefined}
     */
    clickedFile(element)
    {
        $('.activeFile').removeClass('activeFile');
        $('.activeFolder').removeClass('activeFolder');
        element.addClass('activeFile');
        this.enableAction('edit', 'delete', 'crop');
    }

    /**
     * enable actions
     * 
     * @param {array} actions
     * @returns {undefined}
     */
    enableAction(...actions)
    {
        this.disableAction(this.actions);
        $.each(actions, function (key, action) {
            $(`[data-action="${action}"]`).prop('disabled', false);
        });
    }

    /**
     * disable actions
     * 
     * @param {array} actions
     * @returns {undefined}
     */
    disableAction(actions)
    {
        $.each(actions, function (key, action) {
            $(`[data-action="${action}"]`).prop('disabled', true);
        });
    }

    /**
     * Set the root url
     * 
     * @param {string} url 
     */
    set root(url)
    {
        this.addition = `?folder=${url}`;
        this.loadContent();
    }

    /**
     * Set the base url
     * 
     * @param {string} url 
     */
    set baseUrl(url)
    {
        this.url = url;
    }

    /**
     * set the sidebar dom element
     * 
     * @param {mixed} sidebar 
     */
    set sidebar(sidebar)
    {
        this.doms.sidebar = sidebar;
    }

    /**
     * set the content dom element
     * 
     * @param {mixed} content 
     */
    set content(content)
    {
        this.doms.content = content;
    }

    /**
     * set the token dom element
     * 
     * @param {mixed} token 
     */
    set token(token)
    {
        this._token = token;
    }

    /**
     * Open the modal for renaming content items
     * 
     * @param {type} event
     * @returns {undefined}
     */
    actionEdit(event)
    {
        event.preventDefault();
        let model = this.doms.modalPreview;
        let type = $('.activeFile').length > 0 ? 'file' : 'folder';
        let active = $('.activeFile').length > 0 ? 'activeFile' : 'activeFolder';
        $(this.doms.modalPreview).find('.modal-body').html(`
            <br>
            <form id="action-form" method="post" action="${this.url}${this.routes.actions.edit}">
                <input type="hidden" name="type" value="${type}">
                <input type="hidden" name="route" value="${$('.' + active).data('route')}">
                <input type="hidden" name="_token" value="${this._token}">
                <div class="row">
                    <div class="col-12">
                        <input type="text" name="rename" placeholder="Rename..." required="true" class="form-control" value="${$('.' + active).find(`.${type}-name`).html()}">
                    </div>
                    <div class="col-12">
                        <br> 
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        `);
        $(model).modal('show');
    }
    
    /**
     * Remove an item fron the content
     * 
     * @param {type} event
     * @param {type} element
     * @returns {undefined}
     */
    actionDelete(event, element)
    {
        event.preventDefault();
        let self = this;
        swal({
            title: 'Are you sure?',
            type: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.value) {
                let active = $('.activeFile').length > 0 ? $('.activeFile') : $('.activeFolder');
                let type = $('.activeFile').length > 0 ? 'file' : 'folder';
                $.post(self.url+self.routes.actions.delete, {type:type, _token: self._token, route: active.data('route'), _method: 'delete'}, function () {
                    self.loadContent();
                    self.loadSidebar();
                    self.actionFinished('Deleted', 'Your file has been deleted.');
                });

            }
        });
    }
    
    /**
     * Upload new files
     * 
     * @param {type} event
     * @param {type} element
     * @returns {undefined}
     */
    actionUpload(event, element)
    {
        event.preventDefault();
        let self = this;
        $(this.doms.modalPreview).find('.modal-body').html(`
            <form class="dropzone" id="fileUploadForm"><input type="hidden" name="_token" value="${this._token}"><input type="hidden" name="folder" value="${this.addition}"></form>
        `);
        var uploadZone = new Dropzone("#fileUploadForm", { url: this.url+this.routes.actions.upload });
        Dropzone.options.fileUploadForm = {
            init: function () {
                this.on("complete", function (file) {
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        self.loadContent();
                    }
                });
            }
        };
        $(this.doms.modalPreview).modal('show');
    }
    
    
    actionNewFolder()
    {
        $(this.doms.modalPreview).find('.modal-body').html(`
            <br>
            <form id="action-form" method="post" action="${this.url}${this.routes.actions.new}">
                <input type="hidden" name="folder" value="${this.addition}">
                <input type="hidden" name="_token" value="${this._token}">
                <div class="row">
                    <div class="col-12">
                        <input type="text" name="name" placeholder="Folder..." required="true" class="form-control">
                    </div>
                    <div class="col-12">
                        <br> 
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        `);
        $(this.doms.modalPreview).modal('show');
    }
    
    
    /**
     * Show swal notification
     * 
     * @param {string} title
     * @param {string} message
     * @param {string} type
     * @returns {undefined}
     */
    actionFinished(title,message='', type='success')
    {
        swal(
            title,
            message,
            type
        );
    }

}