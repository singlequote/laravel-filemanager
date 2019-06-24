<?php

namespace SingleQuote\FileManager;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid; //Uuid::generate();
use Storage;
use File;
use Auth;

class FileManager {

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->css = asset("vendor/laravel-filemanager/filemanager.min.css");
        $this->script = asset("vendor/laravel-filemanager/filemanager.min.js");
        $this->userModel = config('auth.providers.users.model');
    }

    /**
     * Return a config value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function config(string $name, $default = false) {
        return config("laravel-filemanager.$name", $default);
    }

    /**
     * Show the resource index file
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index(Request $request) {
        $this->setDriversAccess();

        $activeDriver = $this->setActiveDriver($request);

        if (!$this->myDrive && !$this->publicDrive) {
            return redirect()->route($this->config('redirect_not_authenticated', 'login'));
        }

        return view('laravel-filemanager::index')->with([
                    'activeDrive' => $activeDriver,
                    'css' => $this->css,
                    'script' => $this->script,
                    'myDrive' => $this->myDrive,
                    'sharedDrive' => $this->sharedDrive,
                    'publicDrive' => $this->publicDrive
        ]);
    }

    /**
     * Set the access variables for the drivers
     * 
     */
    private function setDriversAccess() {
        $this->myDrive = $this->config('my_drive') && Auth::check();
        $this->sharedDrive = $this->config('shared_drive') && $this->myDrive;
        $this->publicDrive = $this->config('public_drive');

        if ($this->config('require_authentication_public_drive', false) && !Auth::check()) {
            $this->publicDrive = false;
        }
    }

    /**
     * Get the active driver or set the default
     * 
     * @param Request $request
     * @return string
     */
    private function setActiveDriver(Request $request) {
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
    public function getConfig() {
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
    public function loadContent(Request $request) {
        $this->setDriversAccess();

        if (Str::startsWith($request->path, 'drive')) {
            $structure = Str::after($request->path, 'drive');
            return $this->loadDrive($request, $structure);
        }

        if (Str::startsWith($request->path, 'public')) {
            $structure = Str::after($request->path, 'public');
            return $this->loadPublic($request, $structure);
        }

        if (Str::startsWith($request->path, 'shared')) {
            $structure = Str::after($request->path, 'shared');
            return $this->loadShared($request, $structure);
        }

        return response(__('filemanager::laravel-filemanager.you are not allowed here'), 503);
    }

    /**
     * Load the private drive content
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function loadDrive(Request $request, string $structure = "") {
        if (!$request->user() || !$this->myDrive) {
            return $this->loadPublic();
        }

        $this->path = $this->parseUrl($this->config('path', 'media') . "/my-drive/" . md5($request->user()->id));

        if (!Storage::disk($this->config("disk", "local"))->exists($this->path)) {
            Storage::disk($this->config("disk"))->makeDirectory($this->path);
        }
        $items = $this->getFilesAndFoldersByPath($structure);

        return view("laravel-filemanager::content")->with([
                    'title' => __('filemanager::laravel-filemanager.my drive'),
                    'breadcrumb' => $this->createBreadCrumb($structure),
                    'files' => $items[0],
                    'folders' => $items[1]
        ]);
    }

    /**
     * Load the public drive content
     * 
     * @param Request $request
     * @param string $structure
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function loadPublic(Request $request, string $structure = "") {
        if (!$this->publicDrive) {
            abort(503);
        }

        $this->path = $this->parseUrl($this->config('path', 'media') . "/public-drive");

        if (!Storage::disk($this->config("disk", "local"))->exists($this->path)) {
            Storage::disk($this->config("disk"))->makeDirectory($this->path);
        }

        $items = $this->getFilesAndFoldersByPath($structure);

        return view("laravel-filemanager::content")->with([
                    'title' => __('filemanager::laravel-filemanager.public drive'),
                    'breadcrumb' => $this->createBreadCrumb($structure),
                    'files' => $items[0],
                    'folders' => $items[1]
        ]);
    }

    /**
     * Load shared content drive
     * 
     * @param Request $request
     * @param string $structure
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function loadShared(Request $request, string $structure = "") {
        if (!$this->sharedDrive) {
            abort(503);
        }

        $this->path = $this->parseUrl($this->config('path', 'media') . "/shared-drive/" . md5($request->user()->id));

        if (!Storage::disk($this->config("disk", "local"))->exists($this->path)) {
            Storage::disk($this->config("disk"))->makeDirectory($this->path);
        }

        $items = $this->getFilesAndFoldersByPath($structure);

        return view("laravel-filemanager::content")->with([
                    'title' => __('filemanager::laravel-filemanager.shared drive'),
                    'breadcrumb' => $this->createBreadCrumb($structure),
                    'files' => $items[0],
                    'folders' => $items[1]
        ]);
    }

    /**
     * Create a breadcrumb of the structure path
     * 
     * @param array $structure
     * @return array
     */
    private function createBreadCrumb(string $structure = null): array {
        if (!$structure) {
            return [];
        }

        $explode = explode('/', $this->parseUrl($structure));
        $breadcrumb = [];
        $previous = "";
        
        foreach ($explode as $index => $key) {
            $previous .= "$key/";
            $config = $this->configurateFolder(Storage::disk($this->config("disk", "local"))->path($this->parseUrl("$this->path/$previous")));
                        
            $breadcrumb[] = [
                'path' => $config->path,
                'name' => $config->name
            ];
        }

        return $breadcrumb;
    }

    /**
     * Get the files and folders by structure path
     * 
     * @param string $structure
     * @return boolean
     */
    private function getFilesAndFoldersByPath(string $structure) {
        $path = $this->parseUrl($this->path . "/$structure");

        if (!Storage::disk($this->config("disk", "local"))->exists($path)) {
            return false;
        }

        $files = File::files(Storage::disk($this->config("disk", "local"))->path($path));

        foreach ($files as $index => $file) {
            if (Str::endsWith($file->getPathname(), '.fmc')) {
                unset($files[$index]);
                continue;
            }
            $files[$index] = $this->configurateFile($file->getPathname());
        }

        $folders = File::directories(Storage::disk($this->config("disk", "local"))->path($path));

        foreach ($folders as $index => $folder) {
            $folders[$index] = $this->configurateFolder(str_replace('\\', '/', $folder));
        }

        return [collect($files), collect($folders)];
    }

    /**
     * Read the configuration of a file
     *
     * @param string $file
     * @return object
     */
    private function configurateFile(string $file): object {
        $item = File::get(Str::before($file, '.') . ".fmc");
        return json_decode($item);
    }

    /**
     * Read the configuration of a folder
     * 
     * @param string $folder
     * @return object
     */
    private function configurateFolder($folder): object {
        $config = File::get("$folder.fmc");
        return json_decode($config);
    }

    /**
     * Upload the file
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function uploadFiles(Request $request) {
        $path = $this->getPathByDrive($request);
        $file = $request->file('file');
        $id = Uuid::generate();

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
            'uploader' => $request->user() ? $request->user()->toArray() : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($this->config('disk', 'local'))->put($this->config('path', 'media') . "/$path/$id.fmc", json_encode($fileConfig));

        return response("", 204);
    }

    /**
     * Get the path by drive
     * 
     * @param Request $request
     * @return string
     */
    private function getPathByDrive(Request $request): string {
        $path = $this->parseUrl($request->path);

        if (Str::startsWith($path, 'drive')) {
            return $this->parseUrl("my-drive/" . md5($request->user()->id) . "/" . Str::after($path, 'drive') . '/');
        }

        if (Str::startsWith($path, 'public')) {
            return $this->parseUrl("public-drive/" . Str::after($path, 'public') . '/');
        }

        if (Str::startsWith($path, 'shared')) {
            return $this->parseUrl("shared-drive/" . md5($request->user()->id) . "/" . Str::after($path, 'shared') . '/');
        }

        abort(503);
    }

    /**
     * Delete a file and it's config
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function deleteFile(Request $request) {
        $path = $this->getPathByDrive($request);
        $file = $this->config('path') . "/$path/$request->item.fmc";

        if (Storage::disk($this->config('disk', 'local'))->exists($file)) {
            $config = json_decode(Storage::disk($this->config('disk', 'local'))->get($file));
            Storage::disk($this->config('disk', 'local'))->delete($this->config('path') . "/$config->basepath");
            Storage::disk($this->config('disk', 'local'))->delete($file);

            return response("", 204);
        }

        abort(403);
    }

    /**
     * Delete a folder and its contents
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function deleteFolder(Request $request) {
        $config = $this->getFileConfig($request, 'folder');
        
        if (!Storage::disk($this->config('disk', 'local'))->exists($config->basepath)) {
            abort(403);
        }
            
        if(isset($config->shared)){
            foreach($config->shared as $shared){
                File::deleteDirectory($shared->path);
                File::delete("$shared->path.fmc");
            }
        }
        
        Storage::disk($this->config('disk', 'local'))->deleteDirectory($config->basepath);
        Storage::disk($this->config('disk', 'local'))->delete($config->basepath.".fmc");

        return response("", 204);

        
    }

    /**
     * Return the file details
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function detailsFile(Request $request) {
        $config = $this->getFileConfig($request);

        if ($config) {
//            unset($config->basepath);

            $config->uploader = isset($config->uploader) ? $config->uploader->name : false;
            $config->shared = isset($config->shared) ? true : false;

            return response()->json($config);
        }

        abort(403);
    }

    /**
     * Return the file config if it exists
     * 
     * @param Request $request
     * @return boolean
     */
    private function getFileConfig(Request $request, $type = 'file') {
        $this->path = $this->config('path') . "/" . $this->getPathByDrive($request);

        if ($request->get('type', $type) === 'file') {
            $file = "$this->path/$request->item.fmc";
            if (Storage::disk($this->config('disk', 'local'))->exists($file)) {
                return json_decode(Storage::disk($this->config('disk', 'local'))->get($file));
            }
        } else {
            $folder = Storage::disk($this->config('disk', 'local'))->path("$this->path/$request->item");
            return (object) $this->configurateFolder($folder);
        }

        return false;
    }

    /**
     * Write the config file
     * 
     * @param object $file
     * @param string $path
     * @return boolean
     */
    private function writeFileConfig(object $file, $path = false) {
        if (!$path) {
            $path = $this->config('path') . "/" . Str::before($file->basepath, $file->id) . "$file->id.fmc";
        }

        Storage::disk($this->config('disk', 'local'))->put($path, json_encode($file));

        return true;
    }

    /**
     * Rename the file and return the config
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameFile(Request $request) {
        $file = $this->getFileConfig($request);

        if ($file) {
            $file->filename = Str::slug($request->rename);
            $file->updated_at = now()->format('Y-m-d H:i:s');
            $this->writeFileConfig($file);

            return response()->json($file);
        }

        abort(403);
    }

    /**
     * Create a new folder
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function createFolder(Request $request) {
        $id = Uuid::generate();
        $path = $this->getPathByDrive($request);

        $folderPath = $this->parseUrl($this->config('path') . "/$path/$id");

        Storage::disk($this->config('disk', 'local'))->makeDirectory($folderPath);

        $data = [
            'basepath' => $this->parseUrl($folderPath),
            'path' => $this->parseUrl("$request->path/$id", true),
            'id' => "$id",
            'name' => $request->name,
            'uploader' => $request->user() ? $request->user()->toArray() : null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        Storage::disk($this->config('disk', 'local'))->put("$folderPath.fmc", json_encode($data));

        return response("", 204);
    }

    /**
     * Parse url to valid url without double //
     * 
     * @param string $url
     * @return string
     */
    private function parseUrl(string $url, $withoutDriver = false) {
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
     * Share content between user resources
     * Determine if a user exists
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function shareContent(Request $request) {
        if (!$request->get('email', false) || strlen($request->email) === 0) {
            abort(403);
        }

        $email = explode(',', str_replace(' ', '', $request->email));
        $model = new $this->userModel;
        $users = $model->whereIn('email', $email)->get();

        $file = $this->getFileConfig($request);

        if ($request->get('type', 'file') === 'file') {
            $this->shareFile($file, $users);
        } else {
            $this->shareFolder($file, $users);
        }

        return response("", 204);
    }

    /**
     * Create snlink to share a folder with another user resource
     * 
     * @param object $folder
     * @param \Illuminate\Database\Eloquent\Collection $users
     */
    private function shareFolder(object $folder, \Illuminate\Database\Eloquent\Collection $users) {
        foreach ($users as $user) {
            $path = "$this->path/$folder->path";
            $newPath = $this->config('path') . "/shared-drive/" . md5($user->id) . "/$folder->path";
            
            symlink(
                Storage::disk($this->config('disk', 'local'))->path($path),
                Storage::disk($this->config('disk', 'local'))->path($newPath)
            );
            symlink(
                Storage::disk($this->config('disk', 'local'))->path("$path.fmc"),
                Storage::disk($this->config('disk', 'local'))->path("$newPath.fmc")
            );
            
            $shared = isset($folder->shared) ? (array) $folder->shared : [];
            $shared[$user->id] = [
                'user' => $user,
                'path' => Storage::disk($this->config('disk', 'local'))->path($newPath),
                'shared_on' => now()->format('Y-m-d H:i:s')
            ];

            $folder->shared = $shared;
            $this->writeFileConfig($folder, "$path.fmc");
        }
    }

    /**
     * Create symlink to share a file with another user resource
     * 
     * @param object $file
     * @param \Illuminate\Database\Eloquent\Collection $users
     */
    private function shareFile(object $file, \Illuminate\Database\Eloquent\Collection $users) {
        $path = $this->config('path') . "/" . Str::before($file->basepath, $file->id) . "$file->id";

        foreach ($users as $user) {
            $newPath = $this->config('path') . "/shared-drive/" . md5($user->id) . "/";

            symlink(
                Storage::disk($this->config('disk', 'local'))->path($path . '.fmc'),
                Storage::disk($this->config('disk', 'local'))->path($newPath . "$file->id.fmc")
            );

            symlink(
                Storage::disk($this->config('disk', 'local'))->path($path . ".$file->extension"),
                Storage::disk($this->config('disk', 'local'))->path($newPath . "$file->id.$file->extension")
            );

            $shared = isset($file->shared) ? (array) $file->shared : [];
            $shared[$user->id] = [
                'user' => $user,
                'path' => Storage::disk($this->config('disk', 'local'))->path($newPath . "$file->id.fmc"),
                'shared_on' => now()->format('Y-m-d H:i:s')
            ];

            $file->shared = $shared;
            $this->writeFileConfig($file);
        }
    }

}
