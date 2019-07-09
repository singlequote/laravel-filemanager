<?php

namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\ShareController;
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
class FilesController extends \SingleQuote\FileManager\FileManager 
{

    use FileFolderTrait;

    /**
     * Load the folders raw
     * 
     * @return array
     */
    private function load(): array 
    {
        if (!File::isDirectory($this->getPath())) {
            abort(503);
        }

        $files = File::files($this->getPath());

        foreach ($files as $index => $file) {
            if (!Str::endsWith($file, '.fmc') || File::isDirectory(Str::before($file->getPathName(), '.fmc'))) {
                unset($files[$index]);
            }
        }

        return array_values($files);
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
            $config->filename = $request->rename;
            $config->updated_at = now()->format('Y-m-d H:i:s');
            
            $this->writeConfig($config);

            return response()->json($config);
        }

        abort(403);
    }

    /**
     * Upload a new file
     * Create a new config file
     * 
     * @param \SingleQuote\FileManager\Controllers\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function upload(Request $request)
    {
        $path = $this->getPathByDrive($request);
        $file = $request->file('file');
        $id = Str::uuid();

        $request->file('file')->storeAs(
                $this->config('path', 'media') . "/$path", $id . "." . $file->getClientOriginalExtension(), $this->config('disk', 'local')
        );

        $fileConfig = [
            'basepath' => str_replace("//", "/", "$path/$id." . $file->getClientOriginalExtension()),
            'id' => "$id",
            'filename' => Str::before($file->getClientOriginalName(), ".{$file->getClientOriginalExtension()}"),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'mimetype' => $file->getMimeType(),
            'image' => Str::startsWith($file->getMimeType(), "image"),
            'uploader' => $request->user() ? ['id' => encrypt($request->user()->id), 'name' => $request->user()->name] : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($this->config('disk', 'local'))->put($this->config('path', 'media') . "/$path/$id.fmc", json_encode($fileConfig));

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

            $config->uploader = isset($config->uploader) ? $config->uploader->name : false;
            $config->content = view('laravel-filemanager::types.details')->with(compact('config'))->render();
            return response()->json($config);
        }

        abort(403);
    }

    /**
     * Delete a file and it's config
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function delete(Request $request) 
    {
        $path = $this->getPathByDrive($request);
        $file = $this->config('path') . "/$path/$request->item.fmc";

        if (Storage::disk($this->config('disk', 'local'))->exists($file)) {
            
            $config = json_decode(Storage::disk($this->config('disk', 'local'))->get($file));
            
            Storage::disk($this->config('disk', 'local'))->delete($this->config('path') . "/$config->basepath");
            Storage::disk($this->config('disk', 'local'))->delete($file);
            
            ShareController::delete($config);

            return response("", 204);
        }

        abort(403);
    }

}
