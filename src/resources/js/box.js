
/**
 * 
 * @type Class
 */
class Box
{
    /**
     * 
     * @returns {ContentBox}
     */
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.element = $('#package-details');
    }
    
    /**
     * 
     * @param {type} title
     * @returns {undefined}
     */
    set title(title)
    {
        this.element.find('#title').html(title);
    }
    
    /**
     * 
     * @param {type} content
     * @returns {undefined}
     */
    set content(content)
    {
        this.element.find('#content').html(content);        
    }
    
    /**
     * 
     * @returns {undefined}
     */
    show()
    {
        this.element.animate({
            opacity:1
        },300);
    }
    
    /**
     * 
     * @returns {undefined}
     */
    hide()
    {
        this.element.animate({
            opacity:0
        },300);
    }
    
}

export default Box;