<?php
namespace SingleQuote\FileManager\Controllers;

use SingleQuote\FileManager\Controllers\ShareController;
use SingleQuote\FileManager\Observers\FileObserver;
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

        return cache()->tags(['laravel-filemanager', 'laravel-filemanager:files'])->remember("fi" . md5($this->driver), 3600, function() {
                $items = File::files($this->getPath());
                $files = [];
                foreach ($items as $item) {
                    $content = File::get($item->getPathname(), false);
                    $object = json_decode($content);
                    if ($object && isset($object->type) && $object->type === 'file') {
                        $files[] = $object;
                    } elseif ($object && Str::contains($object->basepath, '.')) {
                        $files[] = $object;
                    }
                }

                return $files;
            });
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
            FileObserver::update($config);
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
            'type' => "file",
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
        FileObserver::create((object) $fileConfig);
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
        $config = cache()->tags(['laravel-filemanager', 'laravel-filemanager:files'])->remember("laravel-filemanager:file-$request->item", 3600, function() use($request) {
            $config = $this->parseConfig($this->makePath($request, $request->item));

            if ($config) {
                $config->content = view('laravel-filemanager::types.details')->with(compact('config'))->render();
                $config->isOwner = \Auth::check() && $config->uploader && \Auth::id() === decrypt(optional($config->uploader)->id);
                return $config;
            }

            return false;
        });

        return response()->json($config);
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
            FileObserver::delete($config);
            return response("", 204);
        }

        abort(403);
    }
}
