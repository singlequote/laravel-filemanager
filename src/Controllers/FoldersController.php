<?php
namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Traits\FileFolderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use File;


/**
 * Description of FilesController
 *
 * @author WPruiksma
 */
class FoldersController extends \SingleQuote\FileManager\FileManager
{
    use FileFolderTrait;
    
    /**
     * Load the folders raw
     * 
     * @return array
     */
    private function load() : array
    {
        if (!File::isDirectory($this->getPath())) {
            abort(503);
        }

        $folders = File::directories($this->getPath());
        foreach ($folders as $index => $folder) {
            if(File::exists("$folder.fmc")){
                $folders[$index] = "$folder.fmc";
            }else{
                File::deleteDirectory($folder);
            }
        }

        return array_values($folders);
    }
    
    /**
     * Delete a folder and its contents
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function delete(Request $request) 
    {
        $path   = $this->getPathByDrive($request);
        $config = $this->config('path') . "/$path/$request->item.fmc";

        if (Storage::disk($this->config('disk', 'local'))->exists($config)) {
            $folder = json_decode(Storage::disk($this->config('disk', 'local'))->get($config));
            Storage::disk($this->config('disk', 'local'))->delete($this->config('path') . "/$folder->basepath");
            Storage::disk($this->config('disk', 'local'))->delete($config);

            return response("", 204);
        }

        abort(403);
    }

    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function create(Request $request) 
    {
        $id         = Str::uuid();
        $path       = $this->getPathByDrive($request);
        $folderPath = $this->parseUrl("$path/$id");

        Storage::disk($this->config('disk', 'local'))->makeDirectory("{$this->config('path')}/$folderPath");

        $data = [
            'basepath' => $this->parseUrl($folderPath),
            'path' => $this->parseUrl("$request->path/$id", true),
            'id' => "$id",
            'name' => $request->name,
            'uploader' => $request->user() ? $request->user()->toArray() : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($this->config('disk', 'local'))->put("{$this->config('path')}/$folderPath.fmc", json_encode($data));

        return response("", 204);
    }
    
    /**
     * Return the file details
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function details(Request $request) 
    {       
        $config = $this->parseConfig($this->makePath($request, $request->item));

        if ($config) {
            $config->type = "folder";
            $config->uploader = isset($config->uploader) ? $config->uploader->name : false;
            $config->content = view('laravel-filemanager::types.details')->with(compact('config'))->render();
            return response()->json($config);
        }

        abort(403);
    }
    
    /**
     * Rename the file and return the config
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename(Request $request) 
    {
        $config = $this->parseConfig($this->makePath($request, $request->item));

        if ($config) {
            $config->name = $request->rename;
            $config->updated_at = now()->format('Y-m-d H:i:s');
            
            $this->writeConfig($config);

            return response()->json($config);
        }

        abort(403);
    }

}