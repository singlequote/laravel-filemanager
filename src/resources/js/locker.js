
/**
 * Locker class
 * Used to lock the filemanager with permissions
 * 
 * @type Class
 */
class Locker
{
    /**
     * 
     * @param {Object} FileManager
     * @returns {Locker}
     */
    constructor(FileManager)
    {
        this.FileManager = FileManager;
        this.permissions = {
            open : 1,
            edit : 0,
            delete : 0,
            upload : 0,
            share : 0
        };
        this.permissionsTrue = {
            open : 1,
            edit : 1,
            delete : 1,
            upload : 1,
            share : 1
        };
    }
    
    /**
     * Get the config of an element
     * When config parameter is object, return object without executing the request
     * 
     * @param {type} item
     * @param {type} callback
     * @returns {jqXHR}
     */
    getConfig(item, callback)
    {
        if(typeof item === "object"){
            return callback(item);
        }
        
        return $.post(this.FileManager.url('details/file'), {_token: this.FileManager.config._token, item: item}, (response) => {
            return callback(response);
        });
    } 
    
    /**
     * Determine if the user can do a specific action
     * 
     * @param {type} permission
     * @param {type} item
     * @param {type} callback
     * @returns {undefined|Boolean}
     */
    can(permission, item, callback)
    {
        if(!item){
            if(this.FileManager.currentPath.startsWith('public') || this.FileManager.currentPath.startsWith('drive')){
                return callback();
            }
            
            return false;
        }
        
        return this.getPermissionsByDriver(item, (config, permissions) => {
            if(callback && permissions[permission]){
                return callback(config);
            }
        });
    }
    
    /**
     * Determine if the user cannot do a specific action
     * 
     * @param {type} permission
     * @param {type} item
     * @param {type} callback
     * @returns {undefined|Boolean}
     */
    cannot(permission, item, callback)
    {
        if(!item){
            if(this.FileManager.currentPath.startsWith('shared')){
                return callback();
            }
            
            return false;
        }
       
        return this.getPermissionsByDriver(item, (config, permissions) => {
            if(callback && !permissions[permission]){
                return callback(config);
            }
        });
    }
    
    /**
     * Get the permissions by driver
     * Determine if the user can access a driver or if the config is valid
     * 
     * @param {type} item
     * @param {type} callback
     * @returns {undefined}
     */
    getPermissionsByDriver(item, callback)
    {
        this.getConfig(item, (config) => {
            
            if(this.FileManager.currentPath.startsWith('drive')){
                return callback(config, this.permissionsTrue);
            }
            
            if(this.FileManager.currentPath.startsWith('shared')){
                if(!config.shared || !config.shared[this.FileManager.config.user.id].permissions){
                    return callback(config, this.permissions);
                }
                return callback(config, config.shared[this.FileManager.config.user.id].permissions);
            }
            
            if(this.FileManager.currentPath.startsWith('public')){
                if(config.isOwner || !config.uploader){
                    return callback(config, this.permissionsTrue);
                }
                return callback(config, this.permissions);
            }
        });
    }
    
    
}

export default Locker;