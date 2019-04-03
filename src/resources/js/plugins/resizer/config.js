/**
 * Resize a file
 * 
 * @param {Modal} modal
 * @param {mixed} response
 * @returns {undefined}
 */
function resizeFile(plugin)
{
    
    //jquery ui is slow to load in the dom. 
    //Check if it is loaded every 0.5 seconds
    if (typeof jQuery.ui === 'undefined'){
        setTimeout(() => {
            console.log('reload plugin');
            resizeFile(plugin);
        }, 500);
        return false;
    }
    
               
//    $("#image-resize img").on('load', () => {
        let currentheight = $('#image-resize img').height();
        let currentwidth = $('#image-resize img').width();
        
        $('#action-form input[name=origin_height]').val(currentheight);
        $('#action-form input[name=origin_width]').val(currentwidth);
        
        $('.resize-height').html(`${parseInt(currentheight)}px`);
        $('.resize-width').html(`${parseInt(currentwidth)}px`);

        $('#preview-frame').css({height : currentheight, width: currentwidth});

        $("#image-resize img").resizable({
            alsoResize: "#preview-frame",
            maxWidth: $("#image-resize img").width(),
            maxHeight: $("#image-resize img").height(),
            minWidth: 200,
            aspectRatio: true,
            resize: function (e, ui) {
                let scale = $('#image-resize img').outerWidth() * 100 / $('#image-resize').innerWidth() + '%';
                $('.resize-width').html(`${parseInt(ui.size.width)}px`);
                $('.resize-height').html(`${parseInt(ui.size.height)}px`);
                $('#action-form input[name=height]').val(`${parseInt(ui.size.height)}`);
                $('#action-form input[name=width]').val(`${parseInt(ui.size.width)}`);
                $('#action-form input[name=scale]').val(scale);
            }
        });


//    });

}