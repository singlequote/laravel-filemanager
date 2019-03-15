

export default class Callback
{
        
        /**
         * The constructor
         */
        constructor(parent)
        {
            this.parent = parent;
        }
        
        /**
         * Return the callback
         * 
         */
        callback(response)
        {
            this.parent.doms.package.html(``);
            return this.callback(response);
        }
        
        /**
         * Set the callback
         */
        result(callback)
        {
            this.callback = callback;
            return false;
        }
        
        
}