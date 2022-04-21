<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace SingleQuote\FileManager;

use SingleQuote\FileManager\Controllers\FilesController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Description of FileSystem
 *
 * @author Wim Pruiksma
 */
class FileSystem
{

    protected $tempConfig = [];

    /**
     * Create a new directory instance
     * 
     * @param string $path
     * @param bool $recursive
     * @return string
     */
    public function createDirectory(string $path): string
    {
        $config = $this->getDirectoryConfig($path);

        Storage::disk($this->getConfig('disk', 'local'))
            ->put("{$this->getConfig('path')}/{$config['basepath']}.fmc", json_encode($config));

        Storage::disk($this->getConfig('disk', 'local'))
            ->makeDirectory("{$this->getConfig('path')}/{$config['basepath']}");

        return $config['id'];
    }

    /**
     * Check if a config path is a directory
     * 
     * @param string $path
     * @return bool
     */
    public function isDirectory(string $path): bool
    {
        try {
            $config = $this->get(Str::before($path, '.'));

            return Storage::disk($this->getConfig('disk', 'local'))
                    ->exists("{$this->getConfig('path')}/{$config->basepath}");
        } catch (\Exception $ex) {
            return false;
        }

        return false;
    }

    /**
     * Delete a file and it's config file
     * 
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        $config = $this->get(Str::before($path, '.'));
        $extracted = $this->extractFromPath(Str::before($path, '.'));

        Storage::disk($this->getConfig('disk', 'local'))
            ->delete("{$this->getConfig('path')}/{$config->basepath}");

        return Storage::disk($this->getConfig('disk', 'local'))
                ->delete("{$this->getConfig('path')}/{$extracted['basepath']}.fmc");
    }

    /**
     * Copy a file and it's config file to a new destination 
     * 
     * @param type $path
     * @param string $destination
     * @return bool
     */
    public function copy($path, string $destination): bool
    {
        $extractedDestination = $this->extractFromPath($destination);

        $config = is_object($path) ? $this->get($path->basepath) : $this->get($path);
        $oldPath = $config->basepath;
        $id = Str::uuid();

        $config->id = (string) $id;

        if ($config->type === 'folder') {
            $config->basepath = "{$extractedDestination["basepath"]}/$id";
        } else {
            $config->basepath = "{$extractedDestination["basepath"]}/$id.$config->extension";
        }

        Storage::disk($this->getConfig('disk', 'local'))
            ->put("{$this->getConfig('path')}/{$extractedDestination['basepath']}/$id.fmc", json_encode($config));

        if ($config->type === 'folder') {
            return Storage::disk($this->getConfig('disk', 'local'))
                    ->makeDirectory("{$this->getConfig('path')}/{$extractedDestination['basepath']}/$id");
        } else {
            return Storage::disk($this->getConfig('disk', 'local'))
                    ->copy("{$this->getConfig('path')}/$oldPath", "{$this->getConfig('path')}/{$extractedDestination['basepath']}/$id.$config->extension");
        }
    }

    /**
     * Delete an directory and it's config file
     * 
     * @param string $path
     * @return bool
     */
    public function deleteDirectory(string $path): bool
    {
        $extracted = $this->extractFromPath($path);
        $basepath = Storage::disk($this->getConfig('disk', 'local'))
            ->path("{$this->getConfig('path')}/{$extracted['basepath']}");

        if (file_exists("$basepath.fmc")) {
            \File::delete("$basepath.fmc");
        }

        if (is_dir($basepath)) {
            return \File::deleteDirectory($basepath);
        }

        return false;
    }

    /**
     * Delete all files and folders inside an directory
     * 
     * @param string $path
     * @return bool
     */
    public function cleanDirectory(string $path): bool
    {
        $extracted = $this->extractFromPath($path);
        $basepath = Storage::disk($this->getConfig('disk', 'local'))
            ->path("{$this->getConfig('path')}/{$extracted['basepath']}");

        if (is_dir($basepath)) {
            return \File::cleanDirectory($basepath);
        }

        return false;
    }

    /**
     * Upload file object from request
     * 
     * @param object $file
     * @param string $path
     * @return string
     */
    public function upload(object $file, string $path = '')
    {
        $explode = explode("/", $path);

        $route = "";
        foreach ($explode as $index => $key) {
            $route .= $index === 0 ? "$key" : "/$key";
            if (!$this->isDirectory($route)) {
                $this->config(['id' => strtolower($key)])->createDirectory($route);
            }
        }

        $uploadPath = $file->storeAs(
            "{$this->getConfig('path')}/{$this->driver}/{$route}", $file->getClientOriginalName()
        );

        return FilesController::createConfigByFile(storage_path("app/$uploadPath"));
    }

    /**
     * Get all files from directory
     * 
     * @param string $path
     * @return Collection
     */
    public function allFiles(string $path): Collection
    {
        $extracted = $this->extractFromPath($path);

        $links = Storage::disk($this->getConfig('disk', 'local'))
            ->allFiles("{$this->getConfig('path')}/{$extracted['basepath']}");
        $files = [];

        foreach ($links as $link) {
            if (Str::endsWith($link, '.fmc')) {
                try {
                    $files[] = $this->get(Str::before($link, '.fmc'));
                } catch (\Exception $e) {
                    
                }
            }
        }

        return collect($files);
    }

    /**
     * Get config file of a file or directory
     * 
     * @param string $path
     * @return object
     */
    public function get(string $path): ?object
    {
        $extracted = $this->extractFromPath($path);

        $file = Storage::disk($this->getConfig('disk', 'local'))
            ->get("{$this->getConfig('path')}/{$extracted['basepath']}.fmc");

        return json_decode($file);
    }

    /**
     * Set the driver
     * 
     * @param string $driver
     * @return \SingleQuote\FileManager\FileSystem
     */
    public static function driver(string $driver): FileSystem
    {
        $class = new self;
        switch ($driver) {
            case 'public' :
                $class->driver = "public-drive";
                break;
            case 'shared' :
                $class->driver = Auth::check() ? "shared-drive/" . md5(Auth::id()) : "public-drive";
                break;
            case 'drive' :
                $class->driver = Auth::check() ? "my-drive/" . md5(Auth::id()) : "public-drive";
                break;
        }

        return $class;
    }

    /**
     * Set the driver
     * 
     * @param string $driver
     * @return \SingleQuote\FileManager\FileSystem
     */
    public static function disk(string $driver): FileSystem
    {
        return self::driver($driver);
    }

    /**
     * Set custom config
     * 
     * @param array $config
     * @return \SingleQuote\FileManager\FileSystem
     */
    public function config(array $config): FileSystem
    {
        $this->tempConfig = $config;

        return $this;
    }

    /**
     * Get the config for the directory
     * 
     * @param string $path
     * @return array
     */
    protected function getDirectoryConfig(string $path): array
    {
        $id = isset($this->tempConfig['id']) ? $this->tempConfig['id'] : (string) Str::uuid();

        $extract = $this->extractFromPath($path, $id);
        $user = Auth::check() ? Auth::user()->only(['name', 'id']) : null;

        if ($user) {
            $user['id'] = encrypt($user['id']);
        }

        return array_merge([
            'type' => "folder",
            'basepath' => $extract['basepath'],
            'path' => $extract['path'],
            'id' => $extract['id'],
            'name' => ucfirst($extract['name']),
            'uploader' => $user,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
            ], $this->tempConfig);
    }

    /**
     * Get the config for the directory
     * 
     * @param string $path
     * @return array
     */
    protected function getFileConfig(string $path): array
    {
        //
    }

    /**
     * Return extracted path parts
     * 
     * @param string $path
     * @param string $findId
     * @return array
     */
    protected function extractFromPath(string $path, string $findId = null): array
    {
        $removeExtension = explode('.', $path);
        $exploded = explode('/', $this->parseUrl($removeExtension[0]));
        $name = end($exploded);
        $id = $findId ?? $name;
        $newPath = "$this->driver/" . rtrim(Str::after($removeExtension[0], $this->driver), $name);

        return [
            'id' => $id,
            'name' => $name,
            'path' => $this->parseUrl(rtrim($removeExtension[0], $name) . "/$id"),
            'basepath' => $this->parseUrl(str_replace("$name/$name", "$name", "$newPath/$id")),
            "isDir" => is_dir(Storage::disk($this->getConfig('disk', 'local'))->path("{$this->getConfig('path')}/{$this->parseUrl("$newPath/$id")}"))
        ];
    }

    /**
     * Return a laravel-filemanager config value
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig(string $name, $default = false)
    {
        return config("laravel-filemanager.$name", $default);
    }

    /**
     * Generate a valid path from string
     * 
     * @param string $url
     * @return string
     */
    protected function parseUrl(string $url): string
    {
        $valid = str_replace("\\", "/", $url);
        $explode = explode("/", $valid);
        $result = array_filter($explode, function ($key) {
            return strlen($key) > 0;
        });
        $route = implode('/', $result);

        return Str::startsWith($route, 'var') ? "/$route" : $route;
    }
}
