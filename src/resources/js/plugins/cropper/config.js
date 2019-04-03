/**
 * Build the cropper popup
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function buildCropper(plugin)
{
    dokaCreate().edit(`${plugin.modal.parent.media}/${plugin.data.route}`).then((output) => {
        var xhr = new XMLHttpRequest();
        var fd = new FormData();
        xhr.open("POST", `${plugin.modal.url}/action/edit`, true);
        fd.append('route', plugin.data.route)
        fd.append('_token', plugin.modal.parent._token);
        fd.append('crop', output.file);
        xhr.send(fd);
        xhr.onload(() => {
            plugin.modal.parent.loadContent();
        });
    });
}
