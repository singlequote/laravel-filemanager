<?php

namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\MediaController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use Auth;
/**
 * Init the cart controller
 *
 */
class FileManager extends Controller
{

    /**
     * Init the application
     *
     * @return view
     */
    public function index(Request $request)
    {
        if($request->filled('file')){
            $file = config('laravel-filemanager.encrypted') ? decrypt($request->file) : $request->file;
            return (new MediaController)->getFile($request, $file);
        }
        return view('laravel-filemanager::index');
    }

    /**
     * get the sidebar
     *
     * @param Request $request
     * @return view
     */
    public function getSidebar()
    {
        $directory = 'laravel-filemanager::partials';
        $privateFolders = $this->getPrivateFolders();
        $publicFolders = $this->getPublicFolders();
        return view('laravel-filemanager::partials.sidebar')->with(compact('directory', 'privateFolders', 'publicFolders'));
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
        $currentRoute = Auth::user()->id.'/'.config('laravel-filemanager.auth.private_prefix');
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
                'route'     => $folder,
                'name'      => end($name),
                'children'  => $this->createPaths($route.'/'. end($name), $replace)
            ];
        }
        return collect($structure);
    }

    /**
     * get the content
     *
     * @param Request $request
     * @return view
     */
    public function getContent(Request $request)
    {
        $view = $request->get('view', 'thumb');
        $directory = config('laravel-filemanager.encrypted') ? decrypt($request->folder) : $request->folder;
        $root = $this->isRoot($directory);
        $previous = $this->getPrevious($directory);
        $disk = config('laravel-filemanager.disk');
        $files = $this->createContentItems(Storage::disk($disk)->files($directory), $directory);
        $folders = $this->createContentItems(Storage::disk($disk)->directories($directory), $directory);
        return view('laravel-filemanager::partials.content')->with(compact('directory', 'files', 'folders', 'root', 'previous','view'));
    }

    /**
     * Check if directory is the root
     *
     * @param string $directory
     * @return boolean
     */
    protected function isRoot(string $directory = '')
    {
        return count(explode('/',$directory)) === 1;
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
    private function createContentItems($files, $directory)
    {
        $items = [];
        foreach($files as $file){
            $type = Storage::disk(config('laravel-filemanager.disk'))->mimeType($file);
            $exploded = explode('/', $type);
            $name = explode('/', $file);
            $items[] = (object) [
                'name' => end($name),
                'route' => $directory.'/'.end($name),
                'mimetype' => $type,
                'type' => (isset($exploded[0]))?$exploded[0]:$type
            ];
        }
        return collect($items);
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
            $explode = explode('/', $route);
            $extension = explode('.', $request->rename);
            array_pop($explode);
            Storage::disk($disk)->move(
                $route,
                collect($explode)->implode('/').'/'.
                $this->createFileSlug($request->rename, end($extension))
            );
            return response()->json(['status' => 'success']);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
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
            $folder = $this->folder($request->folder);
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
            if($request->type === 'file'){
                Storage::disk($disk)->delete($route);
            }else{
                Storage::disk($disk)->deleteDirectory($route);
            }
            return response()->json(['status' => 'success']);
        } catch (Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Upload new file
     *
     * @param Request $request
     * @return json
     */
    public function uploadItem(Request $request)
    {
        try{
            $disk = config('laravel-filemanager.disk');
            $upload = $request->file('file');
            if($upload->isValid()){
                $slug = $this->createFileSlug($upload->getClientOriginalName(), $upload->getClientOriginalExtension());
                $path = $this->folder($request->folder);
                $upload->storeAs(
                    $path, $slug, $disk
                );
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    /**
     * Return the folder from a parameter
     *
     * @param string $folder
     * @return string
     */
    private function folder(string $folder) :string
    {
        $param = str_replace('?folder=', '', $folder);
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
    

}