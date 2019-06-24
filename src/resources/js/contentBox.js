
/**
 * 
 * @type Class
 */
export default new class ContentBox
{
    /**
     * 
     * @returns {ContentBox}
     */
    constructor()
    {
        $(`#package-filemanager`).append(`
            <div id="editContent">
                <div class="close"><i data-feather="x-square"></i></div>
                <div class="header"></div>
                <div class="content"></div>   
            </div>
        `);
        
        
        
        setTimeout(() => {
            this.content = this.load;
        }, 500);
        
        $(document).on('click', '#editContent .close, #editContent .cancel', (e) => { this.hide(); });
    }
    
    /**
     * Show the content box
     * 
     * @param {type} element
     * @param {type} options
     * @returns {undefined}
     */
    show(element = false, options = {height: 250})
    {
        let left = $('#package-content').width() / 2;
        let top = 200;
        
        if(element){
            left  = element.position().left;
            top = element.position().top;
            
            if(left > $('#package-content').width()){
                left = $('#package-content').width() - 70;
            }
            
        }
        
        $( "#editContent" ).draggable({ handle: ".header" });
        $( "#editContent" ).resizable();

        $('#editContent').css({
            display:'block',
            top: top,
            left: left
        });
        $('#editContent').animate(options,500);
    }
    
    /**
     * Hide the content box
     * 
     * @returns {undefined}
     */
    hide()
    {
        $('#editContent').css({
            "display" : "none", 
            "height" : 0
        });
        $('#editContent').find('.header').html(``);
        $('#editContent').find('.content').html(this.load);
    }
    
    /**
     * Set the content for the box
     * 
     * @type mixed
     */
    set content(content)
    {
        $(`#editContent`).find(`.content`).html(content);
    }
    
    /**
     * Set the header for the box
     * 
     * @type mixed
     */
    set header(header)
    {
        $(`#editContent`).find(`.header`).html(header);
    }
    
    /**
     * Set the loader for the box
     * 
     * @type mixed
     */
    set loader(loader)
    {
        this.load = loader;
    }
    
}