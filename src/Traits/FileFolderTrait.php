<?php
namespace SingleQuote\FileManager\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use File;

/**
 * Description of FileFolderTrait
 *
 * @author WPruiksma
 */
trait FileFolderTrait
{

    /**
     * The driver
     * 
     * string
     */
    private $driver = null;

    /**
     * The path
     */
    private $path = "";

    /**
     * The start of the page
     * 
     */
    public $start = 0;

    /**
     * The end of the page results
     * 
     */
    public $end = 0;

    /**
     * Set the driver to init the class
     * 
     * @param string $driver
     * @return \SingleQuote\FileManager\Controllers\FilesController
     */
    public static function setDriver(string $driver)
    {
        $namespace = self::class;
        $class = new $namespace();

        $class->driver = $class->config('path', 'media') . "/$driver";

        return $class;
    }

    /**
     * Set the request and it's paramaters
     * 
     * @param Request $request
     * @param int $length
     * @return $this
     */
    public function request(Request $request, int $length = null, int $page = null)
    {
        $this->request = $request;
        $this->page = $length ? $page : null;

        $this->end = $this->page ? $length * $this->page : null;
        $this->start = $this->page ? $this->end - $length : null;
        $this->length = $this->page ? $length : null;

        if (!Storage::disk($this->config('disk', 'local'))->exists($this->config('path', 'media') . "/" . $this->getDriversPath($request))) {
            Storage::disk($this->config('disk', 'local'))->makeDirectory($this->config('path', 'media') . "/" . $this->getDriversPath($request));
        }

        return $this;
    }

    /**
     * Set the drivers path
     * 
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Return the full path off the driver
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->parseUrl(Storage::disk($this->config("disk", "local"))
                    ->path("$this->driver/$this->path"));
    }

    /**
     * Return the full file|folder path
     * 
     * @param string $path
     * @return string
     */
    public static function path(string $path, $storage = true): string
    {
        if ($storage) {
            return Storage::disk(config('laravel-filemanager.disk', 'local'))
                    ->path(config('laravel-filemanager.path', 'media') . "/$path");
        }

        return config('laravel-filemanager.path', 'media') . "/$path";
    }

    /**
     * Read the configuration of a folder
     * 
     * @param string $path
     * @return object
     */
    private function parseConfig(string $path): object
    {
        $config = File::get("$path.fmc");

        return json_decode($config);
    }

    /**
     * Get the config of a file or folder
     * 
     * @param string $path
     * @return object
     */
    public static function getConfig(string $path): object
    {
        $config = File::get("$path.fmc");

        return json_decode($config);
    }

    /**
     * Write to or create new config file
     * 
     * @param object $file
     * @param string $path
     * @return boolean
     */
    private function writeConfig(object $file, string $path = null)
    {
        if (!$path) {
            $path = $this->config('path') . "/" . Str::before($file->basepath, $file->id) . "$file->id.fmc";
        }

        return Storage::disk($this->config('disk', 'local'))->put($path, json_encode($file));
    }

    /**
     * Write to config file
     * 
     * @param object $file
     * @param string $path
     * @return FileFolderTrait::writeConfig(object, string)
     */
    public static function writeToConfig(object $file, string $path = null)
    {
        $namespace = self::class;
        $class = new $namespace();

        return $class->writeConfig($file, $path);
    }

    /**
     * Count all the folders
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->load());
    }

    /**
     * Make the object
     * 
     * @return \stdClass
     */
    public function make()
    {
        $make = new \stdClass;
        $make->items = $this->get();
        $make->total = $this->count();
        $make->start = $this->start;
        $make->end = $this->end;
        $make->showMore = $this->page && $make->total > $make->end;

        return $make;
    }

    /**
     * Return a collection with the folders
     * 
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        if (!is_null($this->start) && !is_null($this->end)) {
            $items = array_slice($this->load(), $this->start, $this->length);
        } else {
            $items = $this->load();
        }

        return collect($items);
    }
}
