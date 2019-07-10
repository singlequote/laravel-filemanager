<?php

namespace SingleQuote\FileManager;

use SingleQuote\FileManager\Controllers\FoldersController;
use SingleQuote\FileManager\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use File;
use Auth;

class FileManager 
{

    
    protected $assetPath;
    protected $userModel;
    protected $script;
    protected $modal;
    protected $css;
    
    
    
    /**
     * Constructor
     *
     */
    public function __construct() 
    {
        $this->assetPath = "vendor/laravel-filemanager/";
        $this->userModel = config('auth.providers.users.model');
        $this->css = file_exists(public_path("{$this->assetPath}filemanager.min.css")) ? asset("{$this->assetPath}filemanager.min.css") : false;
        $this->script = file_exists(public_path("{$this->assetPath}filemanager.min.js")) ? asset("{$this->assetPath}filemanager.min.js") : false;
        
    }
    
    /**
     * Set the access variables for the drivers
     * 
     */
    private function setDriversAccess() 
    {
        $this->myDrive = $this->config('my_drive') && Auth::check();
        $this->sharedDrive = $this->config('shared_drive') && $this->myDrive;
        $this->publicDrive = $this->config('public_drive');
        
        if ($this->config('require_authentication_public_drive', false) && !Auth::check()) {
            $this->publicDrive = false;
        }
    }
    
    /**
     * Return a config value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function config(string $name, $default = false) 
    {
        return config("laravel-filemanager.$name", $default);
    }

    /**
     * Show the resource index file
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index(Request $request) 
    {
        $this->setDriversAccess();
        
        $activeDriver = $this->setActiveDriver($request);
        
        if (!$this->myDrive && !$this->publicDrive) {
            return redirect()->route($this->config('redirect_not_authenticated', 'login'));
        }

        $driversSize = $this->getDriversSize();
        $view = $this->modal ? 'modal' : 'index';
        return view("laravel-filemanager::$view")->with([
            'activeDrive' => $activeDriver,
            'css' => $this->css ? $this->css : route(config('laravel-filemanager.prefix'))."/laravel-datatables.css",
            'script' => $this->script ? $this->script : route(config('laravel-filemanager.prefix'))."/laravel-datatables.js",
            'myDrive' => $this->myDrive,
            'sharedDrive' => $this->sharedDrive,
            'publicDrive' => $this->publicDrive,
            'driversSize' => $driversSize ?? 0,
            'modal' => $this->modal,
            'maxUpload' => $this->config('max_upload_drive', false)
        ]);
    }
    
    /**
     * Set the modal attribute to true
     * 
     * @return index()
     */
    public function modal(Request $request)
    {
        $this->modal = true;
        
        return $this->index($request);
    }
    
    /**
     * Get the size of a driver
     * 
     * @param string $driver
     * @return int
     */
    private function getDriversSize(string $driver = "drive")
    {
        $driversPath = $this->pathByDriverName($driver);
        $path = $this->addPath($driversPath);
        
        $file_size = 0;
        if(File::isDirectory($path)){
            foreach( File::allFiles($path) as $file){
                $file_size += $file->getSize();
            }
        }
        
        return $file_size;
    }

    /**
     * Get the active driver or set the default
     * 
     * @param Request $request
     * @return string
     */
    private function setActiveDriver(Request $request) 
    {
        $default = $this->myDrive ? 'drive' : false;

        if (!$default) {
            $default = $this->publicDrive ? 'public' : false;
        }

        return $request->get('driver', $default);
    }

    /**
     * Return the config as json
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadConfigurations()
    {
        return response()->json([
            "_token" => csrf_token(),
            "mediaUrl" => route($this->config("media.prefix", "media"), ""),
            "trans" => __('filemanager::laravel-filemanager')
        ]);
    }

    /**
     * Load the content by drive
     * 
     * @param Request $request
     * @return mixed
     */
    public function loadContent(Request $request) 
    {
        $this->setDriversAccess();

        if (Str::startsWith($request->path, 'drive')) {
            return $this->loadDrive($request, 'my drive');
        }

        if (Str::startsWith($request->path, 'public')) {
            return $this->loadDrive($request, 'public drive');
        }

        if (Str::startsWith($request->path, 'shared')) {
            return $this->loadDrive($request, 'shared drive');
        }

        return response(__('filemanager::laravel-filemanager.you are not allowed here'), 503);
    }

    /**
     * Load the private drive content
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function loadDrive(Request $request, string $driver = "")
    {
        if ((!$request->user() || !$this->myDrive) && $driver === 'my drive') {
            return $this->loadDrive($request, 'public drive');
        }
        
        $path = $this->getPathByDrive($request);
                
        $folders = FoldersController::setDriver($path)
                ->request($request)->count();
        
        $files = FilesController::setDriver($path)
                ->request($request)->count();

        return view("laravel-filemanager::content")->with([
            'title' => __("filemanager::laravel-filemanager.$driver"),
            'breadcrumb' => $this->createBreadCrumb($request),
            'files' => $files,
            'folders' => $folders
        ]);
    }

    /**
     * Load the files
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getFiles(Request $request)
    {
        $files = FilesController::setDriver($this->getPathByDrive($request))
                ->request($request, $this->config('pagination_results_files', null), $request->get('pageFiles', null))
                ->make();

        return view("laravel-filemanager::files")->with(compact('files'));
    }
    
    /**
     * Load the folders
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getFolders(Request $request)
    { 
        $folders = FoldersController::setDriver($this->getPathByDrive($request))
               ->request($request, $this->config('pagination_results_folders', null), $request->get('pageFolders', null))
                ->make();
        
        return view("laravel-filemanager::folders")->with(compact('folders'));
    }

    /**
     * Create a breadcrumb of the structure path
     * 
     * @param array $structure
     * @return array
     */
    private function createBreadCrumb(Request $request): array 
    {      
        $path = $this->getStructure($request);
        if(!strlen($path)){
            return [];
        }
        
        $explode    = explode('/', $path);
        $breadcrumb = [];
        $previous   = "";
        
        foreach ($explode as $index => $key) {
            
            $previous .= "$key";
            
            $config = FoldersController::getConfig(
                    Storage::disk($this->config("disk", "local"))->path($this->config('path', 'media')."/$this->drivePath/$previous")
            );
            $previous .= "/";         
            
            $breadcrumb[] = [
                'path' => $config->path,
                'name' => $config->name
            ];
        }

        return $breadcrumb;
    }

    /**
     * Get the path by drive
     * 
     * @param Request $request
     * @return string
     */
    public function getPathByDrive(Request $request): string 
    {        
        return "{$this->getDriversPath($request)}/{$this->getStructure($request)}";
    }
    
    /**
     * Get the path of the driver
     * 
     * @return string
     */
    public function getDriversPath(Request $request)
    {
        $path = $this->parseUrl($request->path);

        if (Str::startsWith($path, 'drive')) {
            return $this->pathByDriverName('drive');
        }

        if (Str::startsWith($path, 'public')) {
            return $this->pathByDriverName('public');
        }

        if (Str::startsWith($path, 'shared')) {
            return $this->pathByDriverName('shared');
        }

        abort(503);
    }
    
    /**
     * Get the path by the name of the driver
     * 
     * @param string $driver
     * @return string
     */
    public function pathByDriverName(string $driver) : string
    {
        if ($driver === 'drive') {
            $this->drivePath = "my-drive/" . md5(Auth::id());
            return $this->parseUrl("$this->drivePath/");
        }

        if ($driver === 'public') {
            $this->drivePath = "public-drive";
            return $this->parseUrl("$this->drivePath/");
        }

        if ($driver === 'shared') {
            $this->drivePath = "shared-drive";
            return $this->parseUrl("$this->drivePath/" . md5(Auth::id()));
        }
        
        return false;
    }
    
    /**
     * Get the current path
     * 
     * @param Request $request
     * @return string
     */
    public function getStructure(Request $request)
    {
        $path = $this->parseUrl($request->path);
        
        if (Str::startsWith($path, 'drive')) {
            return $this->parseUrl(Str::after($path, 'drive'));
        }
        if (Str::startsWith($path, 'public')) {
            return $this->parseUrl(Str::after($path, 'public'));
        }
        if (Str::startsWith($path, 'shared')) {
            return $this->parseUrl(Str::after($path, 'shared'));
        }
        
        abort(503);
    }

    /**
     * Return a full path by driver configs
     * 
     * @param Request $request
     * @return string
     */
    public function makePath(Request $request, string $add = "") : string
    {
        $path = $this->getPathByDrive($request);
        
        return $this->addPath($path, $add);
    }
    
    /**
     * Return the full path from storage
     * 
     * @param string $path
     * @param string $add
     * @return string
     */
    public function addPath(string $path, string $add = "")
    {
        return Storage::disk($this->config('disk', 'local'))
                ->path("{$this->config('path', 'media')}/$path/$add");
    }

    /**
     * Parse url to valid url without double //
     * 
     * @param string $url
     * @return string
     */
    public function parseUrl(string $url, $withoutDriver = false) 
    {
        if ($withoutDriver) {
            $url = str_replace(['drive', 'public', 'shared'], '', $url);
        }
        $valid = str_replace("\\", "/", $url);
        $explode = explode("/", $valid);
        $result = array_filter($explode, function($key) {
            return strlen($key) > 0;
        });

        return implode($result, '/');
    }
    
    /**
     * Get the required script
     * 
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getScript()
    {
        $contents = file_get_contents(base_path('vendor/singlequote/laravel-filemanager/src/resources/dist/filemanager.min.js'));
        $response = \Response::make($contents, 200);
        $response->header('Content-Type', 'application/javascript');
        return $response;
    }
    
    /**
     * Get the stylesheet
     * 
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getStyle()
    {
        $contents = file_get_contents(base_path('vendor/singlequote/laravel-filemanager/src/resources/dist/filemanager.min.css'));
        $response = \Response::make($contents, 200);
        $response->header('Content-Type', 'text/css');
        return $response;
    }
    
}
