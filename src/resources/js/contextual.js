if (typeof $ === "undefined") {
    window.$ = window.jQuery = require('./jquery-3.4.1');
}


/**
 * 
 * @param {type} $
 * @returns {undefined}
 */
(function ($) {
    
    /**
     * 
     * @param {type} options
     * @returns {contextualL#10.$.fn}
     */
    $.fn.contextMenu = function (options = {}) {
        let menu = new contextMenu(options);
        menu.items = this;
        menu.build();
        return this;
    };

}(jQuery));

/**
 * 
 * @param {type} options
 * @returns {_$.contextMenu.menu|window.$.contextMenu|Window.$.contextMenu.menu|window.$.contextMenu.menu|Boolean}
 */
window.$.contextMenu = function(options = {})
{
    if(options.targets){
        let menu = new contextMenu(options);
        menu.items = options.targets;
        menu.build();
        return menu;
    }
    return false;
}


class contextMenu
{
    
    /**
     * 
     * @param {type} options
     * @returns {contextMenu}
     */
    constructor(options)
    {
        this.targets;
        this.activeElement;
        this.settings = $.extend({
            menu: []
        }, options);
    }
    
    /**
     * Built the contect menu
     * 
     * @returns {undefined}
     */
    build()
    {       
        this.buildTemplate((id) => {
            this.buildMenu(id);
            this.element = $(`#${id}`);
            
            this.loadTriggers(id);
        });
    }
    
    /**
     * Build the template
     * 
     * @returns {undefined}
     */
    buildTemplate(callback)
    {
        let id = this.unique();
        
        $(`body`).append(`
            <nav id="${id}" class="contextMenu">
                <ul class="contextMenu-items"></ul>
            </nav>
        `);
        
        callback(id);
    }
    
    /**
     * Build the menu items
     * 
     * @param {type} id
     * @returns {undefined}
     */
    buildMenu(id)
    {
        $.each(this.settings.menu, (index, menu) => {
            $(`#${id} .contextMenu-items`).append(`
                <li data-id="${index}" class="contextMenu-item">
                    <a class="contextMenu-action" data-action="${menu.name}">
                        <i data-feather="${menu.icon}"></i> ${menu.name}
                    </a>
                </li>
            `);
        });
    }
    
    /**
     * Load the triggers
     * 
     * @param {type} id
     * @returns {undefined}
     */
    loadTriggers(id)
    {
        //Set the position of the contextmenu
        $(document).on('contextmenu', this.targets, (e) => {
            e.stopPropagation();
            e.preventDefault();
            this.activeElement = e;
            $('.contextMenu').removeClass('contextMenu-active');
            this.element.addClass('contextMenu-active');
            
            let left = e.pageX;
           
            if(left > $(`#package-content`).width()){
                left = $(`#package-content`).width();
            }
            
            this.element.css({
                top : e.pageY,
                left : left - 20
            });
        });
        
        //When an action is clicked call the callback
        $(document).on('click', `#${id} .contextMenu-items .contextMenu-item`, (e) => {
            let element = $(e.currentTarget);
            this.settings.menu[element.data('id')].callback(
                this.activeElement, 
                this.settings.menu[element.data('id')], 
                element.parent().parent().position()
            );
        });
        
        // Remove the active class when clicked on the document
        $(document).click(() => {
            this.element.removeClass('contextMenu-active');
        });
    }
    
    /**
     * Set the dom elements to trigger
     * 
     * @type string
     */
    set items(targets)
    {
        this.targets = targets;
    }

    /**
     * Generate unique id
     * 
     * @returns {String}
     */
    unique() {
        let s = [];
        let hexDigits = "0123456789abcdef";
        for (let i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
        s[8] = s[13] = s[18] = s[23] = "-";
        return s.join("");
    }

}