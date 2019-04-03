/**
 * Default plugin codemirror
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function buildEditor(plugin)
{
    plugin.modal.active.find('.body').html(`<textarea id="code">${plugin.data.content}</textarea>`);
    plugin.modal.active.find('.footer').html(`<button type="button" id="saveEditorFile">Save file</button>`);

    let editor = CodeMirror.fromTextArea(document.getElementById("code"), {
        lineNumbers: true,
        styleActiveLine: true,
        matchBrackets: true,
        readOnly: false
    });

    $('.CodeMirror').animate({
        minHeight: "800px"
    }, 0);

    editor.setOption("theme", 'darcula');

    $(document).on('click', '#saveEditorFile', () => {
        $.post(`${plugin.modal.url}/action/edit`, {content: editor.getValue(), route: plugin.data.path, _token: plugin.modal.parent._token}, (response) => {
            if (response.status === 'success') {
                plugin.modal.hide();
                plugin.modal.parent.message('Done', 'The file is saved');
            }
        });
    });
}
