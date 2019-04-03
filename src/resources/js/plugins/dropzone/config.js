
/**
 * Plugin 
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function initDropzone(plugin)
{
    const uploadZone = new Dropzone("#action-form", {url: `${plugin.modal.url}/action/upload`});
    
    uploadZone.on("success", function (file) {
        plugin.modal.parent.loadContent();
    });
    
    $(document).on('modal:closed', () => {
        uploadZone.disable();
    });
}