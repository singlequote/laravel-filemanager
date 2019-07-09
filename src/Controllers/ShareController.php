<?php

namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use File;

/**
 * Description of FilesController
 *
 * @author WPruiksma
 */
class ShareController extends \SingleQuote\FileManager\FileManager 
{
    
    /**
     * Build the required data to share content
     * 
     * @param Request $request
     */
    private function buildData(Request $request)
    {
        if (!$request->get('email', false) || strlen($request->email) === 0) {
            abort(403);
        }

        $email = explode(',', str_replace(' ', '', $request->email));
        $model = new $this->userModel;
        
        $this->path  = $this->makePath($request, $request->item);
        $this->users = $model->whereIn('email', $email)->get();
        $this->file  = FilesController::getConfig($this->path);
        
    }
    
    /**
     * Share a file with the given users resource
     * 
     * @param Request $request
     */
    public function file(Request $request)
    {
        $this->buildData($request);

        foreach ($this->users as $user) {
            $sharedPath = "{$this->config('path', 'media')}/shared-drive/" . md5($user->id);
            $newPath = Storage::disk($this->config('disk', 'local'))->path($sharedPath);

            File::link("$this->path.fmc","$newPath/{$this->file->id}.fmc");
                
            $shared = isset($this->file->shared) ? (array) $this->file->shared : [];
            
            $shared[$user->id] = [
                'user' => $user,
                'path' => "$newPath/{$this->file->id}",
                'shared_on' => now()->format('Y-m-d H:i:s')
            ];

            $this->file->shared = $shared;
            
            FilesController::writeToConfig($this->file, "$sharedPath/{$this->file->id}.fmc");
        }
    }
    
    public function folder()
    {
        
    }
    
    /**
     * Remove the shared config files
     * 
     * @param object $config
     * @return boolean
     */
    public static function delete(object $config)
    {
        if(!isset($config->shared)){
            return true;
        }
        
        foreach($config->shared as $shared){
            File::delete("$shared->path.fmc");
        }
        
        return true;
    }
    
}
