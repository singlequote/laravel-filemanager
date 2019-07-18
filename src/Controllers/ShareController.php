<?php
namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\FilesController;
use SingleQuote\FileManager\Observers\FileObserver;
use SingleQuote\FileManager\Observers\FolderObserver;
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

        $this->path = $this->makePath($request, $request->item);
        $this->users = $model->whereIn('email', $email)->get();
        $this->item = FilesController::getConfig($this->path);
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

            File::link("$this->path.fmc", "$newPath/{$this->item->id}.fmc");

            $shared = isset($this->item->shared) ? (array) $this->item->shared : [];

            $shared[$user->id] = [
                'user' => $user,
                'path' => "$newPath/{$this->item->id}",
                'shared_on' => now()->format('Y-m-d H:i:s'),
                'permissions' => [
                    'open' => (int) $request->get('open', 1),
                    'edit' => (int) $request->get('edit', 0),
                    'delete' => (int)$request->get('delete', 0)
                ]
            ];

            $this->item->shared = $shared;
            
            FilesController::writeToConfig($this->item, "$sharedPath/{$this->item->id}.fmc");
        }

        FileObserver::shared($this->item);
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
            $this->shareElement($user, $this->item, $this->path, [
                'open' => (int) $request->get('open', 1),
                'edit' => (int) $request->get('edit', 0),
                'delete' => (int)$request->get('delete', 0),
                'upload' => (int)$request->get('upload', 0)
            ]);
        }

        FolderObserver::shared($this->item);
        return response("", 204);
    }
    
    /**
     * Share the element
     * 
     * @param mixed $user
     * @param object $item
     */
    public function shareElement($user, object $item, string $path,  array $permissions, $add = "")
    {
        $sharedPath = "{$this->config('path', 'media')}/shared-drive/" . md5($user->id);
        $newPath = Storage::disk($this->config('disk', 'local'))->path($sharedPath);

        File::link("$path.fmc", "$newPath/{$add}/{$item->id}.fmc");
        File::link("$path", "$newPath/{$add}/{$item->id}.");

        $shared = isset($item->shared) ? (array) $item->shared : [];

        $shared[$user->id] = [
            'user' => $user,
            'path' => "$newPath/{$item->id}",
            'shared_on' => now()->format('Y-m-d H:i:s'),
            'permissions' => $permissions
        ];

        $item->shared = $shared;

        FilesController::writeToConfig($item);
    }

    /**
     * Delete all the shared conenctions of an item
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function deleteSharedItems(Request $request)
    {
        $path = $this->makePath($request, $request->item);
        $item = FilesController::getConfig($path);

        self::delete($item);

        FileObserver::shared($item);
        FolderObserver::shared($item);

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
        if (!isset($config->shared)) {
            return true;
        }

        foreach ($config->shared as $shared) {
            File::delete("$shared->path.fmc");
            File::deleteDirectory("$shared->path");
        }

        unset($config->shared);

        FilesController::writeToConfig($config);

        FileObserver::shared($config);
        FolderObserver::shared($config);

        return true;
    }
}
