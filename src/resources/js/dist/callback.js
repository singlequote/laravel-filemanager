
/**
 * Callback class
 */
export default class Callback
{

    /**
     * constructor
     * 
     * @param {type} parent
     * @returns {Callback}
     */
    constructor(parent)
    {
        this.parent = parent;
        this.modal = parent.modal;
        this.template = parent.template;
    }

    /**
     * Set the callback
     * 
     * @param {type} response
     * @returns {Callback@call;returnback|Boolean}
     */
    callback(response)
    {
        if (this.config.picksize === true) {
            return this.pickSize(response);
        }
        this.modal.hide();
        return this.returnback(response);
    }

    /**
     * Call the pick size method
     * 
     * @param {type} file
     * @returns {Callback@call;returnback|Boolean}
     */
    pickSize(file)
    {

        if (!file.type.startsWith('image')) {
            this.modal.hide();
            return this.returnback(file);
        }

        this.modal.preview(true, {width: "40%"});

        let uid = 'button_' + Math.random().toString(36).substr(2, 9);

        this.template.loadTemplate(`forms.pick-size`, () => {
            this.template.parseTemplate({
                uid: uid
            }, 'forms.pick-size', `${this.parent.doms.modalPreview} .body`, true);
        });

        $(document).on('click', '#' + uid, () => {
            return this.returnPickSize(file);
        });
        return true;
    }
    
    /**
     * Return the image cached route
     * 
     * @param {type} file
     * @returns {Callback@call;returnback}
     */
    returnPickSize(file)
    {
        let height = $('#pick-size').find('input[name="height"]').val();
        let width = $('#pick-size').find('input[name="width"]').val();

        $.get(`${this.parent.media}/${height}/${width}/${file.path}`);

        file.route = `${this.parent.media}/${height}/${width}/${file.path}`;
        this.modal.hide();
        return this.returnback(file);
    }

    /**
     * Call the callback
     * 
     * @param {type} callback
     * @returns {unresolved}
     */
    result(callback)
    {
        return this.returnback = callback;
    }

    /**
     * Merge the configs
     * 
     * @param {object} options
     * @returns {undefined}
     */
    mergeConfig(options = {})
    {
        if (typeof options !== 'object') {
            options = {};
        }

        this.config = {
            picksize: false,
            type: false
        };

        this.options = $.extend(this.config, options);
    }

}