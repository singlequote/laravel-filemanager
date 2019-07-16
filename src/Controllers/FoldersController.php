<?php
namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Observers\FolderObserver;
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
    private function load(): array
    {
        if (!File::isDirectory($this->getPath())) {
            abort(503);
        }
        
        return cache()->tags(['laravel-filemanager', 'laravel-filemanager:folders'])->remember('laravel-filemanager:folders-'. Str::slug($this->driver), 3600, function(){
            $items = File::files($this->getPath());
            $folders = [];
            foreach ($items as $item) {
                $content = File::get($item->getPathname(), false);
                $object = json_decode($content);
                if ($object && isset($object->type) && $object->type === 'folder') {
                    $folders[] = $object;
                } elseif ($object && !Str::contains($object->basepath, '.')) {
                    $folders[] = $object;
                }
            }
            
            return $folders;
        });
    }

    /**
     * Delete a folder and its contents
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function delete(Request $request)
    {
        $path = $this->getPathByDrive($request);
        $config = $this->config('path') . "/$path/$request->item.fmc";

        if (Storage::disk($this->config('disk', 'local'))->exists($config)) {
            $folder = json_decode(Storage::disk($this->config('disk', 'local'))->get($config));
            Storage::disk($this->config('disk', 'local'))->delete($this->config('path') . "/$folder->basepath");
            Storage::disk($this->config('disk', 'local'))->delete($config);

            ShareController::delete($folder);
            FolderObserver::delete($folder);
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
        $id = Str::uuid();
        $path = $this->getPathByDrive($request);
        $folderPath = $this->parseUrl("$path/$id");

        Storage::disk($this->config('disk', 'local'))->makeDirectory("{$this->config('path')}/$folderPath");

        $data = [
            'type' => "folder",
            'basepath' => $this->parseUrl($folderPath),
            'path' => $this->parseUrl("$request->path/$id", true),
            'id' => "$id",
            'name' => $request->name,
            'uploader' => $request->user() ? $request->user()->toArray() : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($this->config('disk', 'local'))->put("{$this->config('path')}/$folderPath.fmc", json_encode($data));
        FolderObserver::create((object) $data);
        return response("", 204);
    }

    /**
     * Create directory
     * Return uuid when success
     * 
     * @param string $driver
     * @param string $path
     * @return string
     */
    public static function createDirectory(string $driver, string $path, string $generateUUID = null): string
    {
        $explodePath = explode('/', $path);
        $name = array_pop($explodePath);
        $id = !$generateUUID ? Str::uuid() : $generateUUID;
        $class = new FoldersController;
        $driversPath = $class->pathByDriverName($driver);
        $folderPath = $class->parseUrl("$driversPath/" . implode($explodePath, '/') . "/$id");

        $data = [
            'type' => "folder",
            'basepath' => $class->parseUrl($folderPath),
            'path' => $class->parseUrl(implode($explodePath, '/') . "/$id", true),
            'id' => "$id",
            'name' => $name,
            'uploader' => \Auth::user() ? \Auth::user()->toArray() : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($class->config('disk', 'local'))->put("{$class->config('path')}/$folderPath.fmc", json_encode($data));
        Storage::disk($class->config('disk', 'local'))->makeDirectory("{$class->config('path')}/$folderPath");
        FolderObserver::create((object) $data);
        return $id;
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
            FolderObserver::update((object) $config);
            return response()->json($config);
        }

        abort(403);
    }
    
    /**
     * Check if directory exists
     * 
     * @param string $driver
     * @param string $path
     * @return bool
     */
    public static function exists(string $driver, string $path): bool
    {
        $class = new FoldersController;
        $driversPath = $class->pathByDriverName($driver);
        $folderPath = $class->parseUrl("$driversPath/$path");
        
        return Storage::disk($class->config('disk', 'local'))->exists("{$class->config('path')}/$folderPath.fmc");
    }
}
