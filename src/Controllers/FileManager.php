<?php

namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\MediaController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;
use Storage;
use Auth;
use File;
use Cache;
/**
 * Init the cart controller
 *
 */
class FileManager extends Controller
{
    /**
     * The default cachefolder
     *
     */
    protected $cachefolder = 'cached';

    /**
     * 
     */
    public function __construct()
    {
        $this->cachefolder = config('laravel-filemanager.media.hyperlink_path', $this->cachefolder);
    }


    /**
     * Init the application
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if($request->filled('file')){
            $file = config('laravel-filemanager.encrypted') ? decrypt($request->file) : $request->file;
            return (new MediaController)->getFile($request, $file, null, null, 'json');
        }elseif($request->filled('folder')){
            return $this->getFolderPath($request);
        }
        return view('laravel-filemanager::index');
    }

    /**
     * Return the folder path info
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    private function getFolderPath(Request $request)
    {
        $disk = config('laravel-filemanager.disk');

        $path = Storage::disk($disk)->path($request->folder);

        abort_unless(is_dir($path), 404);

        return response()->json([
            'filename' => basename($path),
            'path' => $request->folder,
            'type' => 'folder',
            'content' => null
        ]);

    }

    /**
     * Load the configs
     *
     * @return response json
     */
    public function loadConfigs()
    {
        $encrypt       = config('laravel-filemanager.encrypted');
        $privatePrefix = Auth::check() ? config('laravel-filemanager.auth.private_prefix').Auth::id() : "emptyhere";
        $sharedprefix  = config('laravel-filemanager.auth.shared_prefix');
        return response()->json([
            'asset'         => asset(''),
            'root'          => config('laravel-filemanager.auth.private_folder') && \Auth::check() ? $encrypt ? encrypt($privatePrefix) : $privatePrefix : $sharedprefix,
            'url'           => url(config('laravel-filemanager.prefix')),
            'mediaurl'      => route(config('laravel-filemanager.media.prefix')),
            'privatefolder' => config('laravel-filemanager.auth.private_folder'),
            'privateprefix' => $encrypt ? encrypt($privatePrefix) : $privatePrefix,
            'sharedfolder'  => config('laravel-filemanager.auth.shared_folder'),
            'sharedprefix'  => $encrypt ? encrypt($sharedprefix) : $sharedprefix
        ]);
    }

    /**
     * Load a template
     *
     * @param string $template
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function loadTemplate(string $template)
    {
        if(view()->exists("laravel-filemanager::templates.$template")){
            return view("laravel-filemanager::templates.$template");
        }
        abort(404, 'Template not found');
    }

    /**
     * Load the content
     *
     * @param Request $request
     * @return json
     */
    public function loadContent(Request $request)
    {
        $view = $request->get('view', 'thumb');
        $directory = config('laravel-filemanager.encrypted') ? decrypt($request->folder) : $request->folder;
        $root = $this->isRoot($directory ? $directory : '');
        $previous = $this->getPrevious($directory ? $directory : '');
        $disk = config('laravel-filemanager.disk');

        $files = $this->createContentItems(Storage::disk($disk)->files($directory), $directory, true);
        $folders = $this->createContentItems(Storage::disk($disk)->directories($directory), $directory, $root);
        return response()->json(compact('directory', 'files', 'folders', 'root', 'previous','view'));
    }

    /**
     * get the sidebar
     *
     * @param Request $request
     * @return view
     */
    public function getSidebar()
    {
        $private = $this->getPrivateFolders();

        $public = $this->getPublicFolders();

        return response()->json(compact('private', 'public'));
    }

    /**
     * Get the private folders
     *
     * @return Collection
     */
    private function getPrivateFolders(){
        if(!config('laravel-filemanager.auth.private_folder')){
            return collect([]);
        }
        $currentRoute = Auth::check() ? Auth::user()->id.'/'.config('laravel-filemanager.auth.private_prefix') : 'emptyhere';
        return $this->createPaths($currentRoute, $currentRoute);
    }

    /**
     * Get the shared folders
     *
     * @return Collection
     */
    private function getPublicFolders()
    {
        if(!config('laravel-filemanager.auth.shared_folder')){
            return collect([]);
        }
        $currentRoute = config('laravel-filemanager.auth.shared_prefix');
        return $this->createPaths($currentRoute, $currentRoute);
    }

    /**
     * Create a structure tree for the given path
     *
     * @param string $currentRoute
     * @param string $replace
     * @return Collection
     */
    private function createPaths(string $currentRoute, string $replace = '1')
    {
        $route = str_replace('//', '/', $currentRoute);
        $disk = config('laravel-filemanager.disk');
        $folders = Storage::disk($disk)->directories($route);
        $structure = [];
        foreach($folders as $folder){
            $name = explode('/', $folder);
            $structure[] = (object) [
                'route'     => config('laravel-filemanager.encrypted') ? encrypt($folder) : $folder,
                'name'      => end($name),
                'children'  => $this->createPaths($route.'/'. end($name), $replace),
                'id' => base64_encode(config('laravel-filemanager.encrypted') ? encrypt($folder) : $folder)
            ];
        }
        return collect($structure);
    }

    /**
     * Check if directory is the root
     *
     * @param string $directory
     * @return boolean
     */
    protected function isRoot(string $directory = '')
    {
        return count(explode('/',rtrim($directory, '/'))) === 1;
    }

    /**
     * Get the previous folder
     *
     * @param string $directory
     * @return string
     */
    protected function getPrevious(string $directory)
    {
        $explode = explode('/',$directory);
        array_pop($explode);
        return collect($explode)->implode('/');
    }

    /**
     * Create content items
     *
     * @param array $files
     * @param string $directory
     * @return string
     */
    private function createContentItems(array $files, $directory, bool $root = false)
    {
        $items = $this->addRootItem($root, $directory);
        foreach($files as $file){
			if($file === "default.png"){
				continue;
			}
            $type = Storage::disk(config('laravel-filemanager.disk'))->mimeType($file);
            $exploded = explode('/', $type);
            $name = explode('/', $file);
            $src = $this->getFileSource($file, 300);

            $route = config('laravel-filemanager.encrypted') ? encrypt($directory.'/'.end($name)) : $directory.'/'.end($name);
            $items[] = (object) [
                'size' => Storage::disk(config('laravel-filemanager.disk'))->size($file),
                'name' => end($name),
                'route' => ltrim($route, '/'),
                'mimetype' => $type,
                'type' => (isset($exploded[0]))?$exploded[0]:$type,
                'src' => $src,
                'id' => base64_encode($route),
                'cached' => $type !== 'directory' ? (new MediaController)->findCachedFiles($route, end($name)) : []
            ];
        }

        return collect($items);
    }

    /**
     * Get the resource path for the files.
     * This also returns the cached files when present
     *
     * @param string $file
     * @param int $height
     * @param int $width
     * @return string
     */
    private function getFileSource(string $file, int $height = null, int $width = null) : string
    {
        $filename = Str::after($file, '/');
        $path = "cached/".Str::before($file, $filename);

        $height = $height ?? null;
        $width  = $width  ?? $height;

        if(file_exists(public_path("$path$height-$width-$filename"))){
            $src = url("$path$height-$width-$filename");
        }else{
            $src = route(config('laravel-filemanager.media.prefix'), [$height, $width, $file]);
        }
        return config('laravel-filemanager.encrypted') ? encrypt($src) : $src;
    }

    /**
     * Add root item for returning back to previous item
     *
     * @param bool $root
     * @param string $directory
     * @return array
     */
    private function addRootItem(bool $root, $directory) : array
    {
        $items = [];
        $previous = $this->getPrevious($directory ? $directory : '');
        $name = explode('/', $previous);
        $id = Auth::check() ? Auth::id() : 'emptyhere';
        $folderName = end($name);
        if(starts_with($previous, $id)){
            $folderName = Auth::user()->name;
        }
        if(!$root){
            $items[] = (object) [
                'name' => "<< $folderName",
                'route' => config('laravel-filemanager.encrypted') ? encrypt($previous) : $previous,
                'mimetype' => 'directory',
                'type' => 'folder',
                'src' => '',
                'id' => 'root'
            ];
        }
        return $items;
    }

    /**
     * Rename a folder or file
     *
     * @param Request $request
     * @return json
     */
    public function editItem(Request $request)
    {
        try{
            $disk = config('laravel-filemanager.disk');
            $route = config('laravel-filemanager.encrypted') ? decrypt($request->route) : $request->route;

            if($request->file('crop')){
                $this->cropImage($disk, $route, $request);
            }
            if($request->filled('content')){
                $this->editContentFile($disk, $route, $request);
            }
            if($request->filled('rename') &&  $request->type !== 'folder'){
                $this->renameFile($disk, $route, $request);
            }
            if($request->filled('rename') &&  $request->type === 'folder'){
                $this->renameFolder($disk, $route, $request);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Save cropped image
     *
     * @param string $disk
     * @param string $route
     * @param Request $request
     * @return boolean
     */
    private function cropImage(string $disk, string $route, Request $request)
    {
        $path = Storage::disk($disk)->path($route);
        $filename = basename($path);
        $path = $request->file('crop')->storeAs(
            str_before($route, $filename), str_before($filename, '.')."-cropped.".str_after($filename, '.'), $disk
        );
        return true;
    }

    /**
     * Rename file or folder
     *
     * @param string $disk
     * @param string $route
     * @param Request $request
     * @return boolean
     */
    private function renameFile(string $disk, string $route, Request $request)
    {
        $explode = explode('/', $route);
        $extension = explode('.', $request->rename);
        array_pop($explode);
        return Storage::disk($disk)->move(
            $route,
            collect($explode)->implode('/').'/'.
            $this->createFileSlug($request->rename, end($extension))
        );
    }

    /**
     * Rename folder
     *
     * @param string $disk
     * @param string $route
     * @param Request $request
     */
    private function renameFolder(string $disk, string $route, Request $request)
    {
        $path = Storage::disk($disk)->path($route);
        Storage::disk($disk)->move("$route", str_before($route, basename($path)).Str::slug($request->rename));
    }

    /**
     * Edit the content of a file
     *
     * @param string $disk
     * @param string $route
     * @param Request $request
     * @return type
     */
    private function editContentFile(string $disk, string $route, Request $request)
    {
        return Storage::disk($disk)->put($route, $request->content);
    }

    /**
     * Create a new directory
     *
     * @param Request $request
     * @return json
     */
    public function newItem(Request $request)
    {
        try{
            $folder = $this->folder($request->path);
            $disk = config('laravel-filemanager.disk');
            Storage::disk($disk)->makeDirectory($folder.'/'.str_slug($request->name));
            return response()->json(['status' => 'success']);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Delete a file or directory
     * return a json response
     *
     * @param Request $request
     * @return json
     */
    public function deleteItem(Request $request)
    {
        try{
            $disk = config('laravel-filemanager.disk');
            $route = config('laravel-filemanager.encrypted') ? decrypt($request->route) : $request->route;
			if(in_array($route, config('laravel-filemanager.protected_folders', []))){
				return response()->json(['status' => 'error', 'message' => "Folder is protected!"], 500);
			}
			
			if($request->type === 'file'){
                Storage::disk($disk)->delete($route);
                $this->deleteCacheFiles($route);
            }else{
                Storage::disk($disk)->deleteDirectory($route);
            }
            return response()->json('', 204);
        } catch (Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * Delete the cache files when a file is deleted or edited
     *
     * @param string $route
     * @return boolean
     */
    private function deleteCacheFiles(string $route) : bool
    {
        $disk       = config('laravel-filemanager.disk');
        $path       = Storage::disk($disk)->path($route);
        $filename   = basename($path);
        $cacheroute = str_before($route, $filename);
        
        $files = File::files(public_path("$this->cachefolder/$cacheroute"));

        foreach($files as $file){
            if(Str::endsWith($file->getFilename(), $filename)){
                File::delete($file);
            }
        }

        return true;
    }

    /**
     * Upload new file
     *
     * @param Request $request
     * @return json
     */
    public function uploadItem(Request $request)
    {
        $disk = config('laravel-filemanager.disk');
        $filename = $request->file('file')->getClientOriginalName();
        $extension = $request->file('file')->getClientOriginalExtension();
        $path = Str::after($request->directory, '=');

        $file = str_slug(Str::before($filename, '.'));

        $route = $request->file->storeAs($path, "$file.$extension", $disk);

        if($request->has('thumb')){
            $driver = (new MediaController)->driver;
            foreach($request->thumb as $size => $value){
                $image      = Image::make(Storage::disk($disk)->path($route))->orientate();
                $image->{config('laravel-filemanager.media.driver', $driver)}($size, $size, function($constraint){
                    $constraint->upsize();
                    $constraint->aspectRatio();
                })->encode(null, $request->get('q', 100));
                (new MediaController)->cacheImageResponse($image, $route, "$file.$extension", $size, $size);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     *
     */
    public function clearCache()
    {
        $cache = (new MediaController)->cachefolder;
        array_map('unlink', glob(public_path($cache)));
        Cache::flush();
    }

    /**
     * Return the folder from a parameter
     *
     * @param string $folder
     * @return string
     */
    private function folder(string $folder) : string
    {
        $param = Str::after($folder, 'folder=');
        $path = config('laravel-filemanager.encrypted') ? decrypt($param) : $param;
        return $path;
    }

    /**
     * Create file slug of filename
     *
     *
     * @param mixed $file
     * @return string
     */
    private function createFileSlug(string $clientName, string $extension) : string
    {
        $slug = str_slug($clientName);
        return str_replace($extension, ".$extension", $slug);
    }

    
    public function resize(Request $request)
    {
        if(!$request->width || !$request->height){
            return response("", 204);
        }
        
        $disk = config('laravel-filemanager.disk');
        $path = Storage::disk($disk)->path($request->path);
        $img = Image::make($path);
        
        $sizes = getimagesize($path);

        $width = (int) $sizes[0] / 100 * (int) $request->scale;

        $img->resize($width, $width);

        $img->save($path);

        $this->deleteCacheFiles($request->path);

        return response()->json(['status' => 'success']);
    }


}
