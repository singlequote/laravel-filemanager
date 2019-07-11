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
        $this->item  = FilesController::getConfig($this->path);
    }
    
    /**
     * Share file with the selected users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function file(Request $request)
    {
        $this->buildData($request);

        foreach ($this->users as $user) {
            $sharedPath = "{$this->config('path', 'media')}/shared-drive/" . md5($user->id);
            $newPath = Storage::disk($this->config('disk', 'local'))->path($sharedPath);

            File::link("$this->path.fmc","$newPath/{$this->item->id}.fmc");
                
            $shared = isset($this->item->shared) ? (array) $this->item->shared : [];
            
            $shared[$user->id] = [
                'user' => $user,
                'path' => "$newPath/{$this->item->id}",
                'shared_on' => now()->format('Y-m-d H:i:s')
            ];

            $this->item->shared = $shared;
            
            FilesController::writeToConfig($this->item, "$sharedPath/{$this->item->id}.fmc");
        }
        
        return response("", 204);
    }
    
    /**
     * Share folder with the selected users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function folder(Request $request)
    {
        $this->buildData($request);
        
        foreach ($this->users as $user) {
            $sharedPath = "{$this->config('path', 'media')}/shared-drive/" . md5($user->id);
            $newPath = Storage::disk($this->config('disk', 'local'))->path($sharedPath);

            File::link("$this->path.fmc","$newPath/{$this->item->id}.fmc");
            File::link("$this->path","$newPath/{$this->item->id}.");
                
            $shared = isset($this->item->shared) ? (array) $this->item->shared : [];
            
            $shared[$user->id] = [
                'user' => $user,
                'path' => "$newPath/{$this->item->id}",
                'shared_on' => now()->format('Y-m-d H:i:s')
            ];

            $this->item->shared = $shared;
            
            FilesController::writeToConfig($this->item, "$sharedPath/{$this->item->id}.fmc");
        }
        
        return response("", 204);
    }
    
    /**
     * Delete all the shared conenctions of an item
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function deleteSharedItems(Request $request)
    {        
        $path  = $this->makePath($request, $request->item);
        $item  = FilesController::getConfig($path);
        
        self::delete($item);
        
        return response("", 204);
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
            File::deleteDirectory("$shared->path");
        }
        
        unset($config->shared);
        
        FilesController::writeToConfig($config);
        
        return true;
    }
    
}
