<?php

namespace DataContracts\Cache;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Psr\SimpleCache\CacheInterface;

class FileCache
{
    /**
     * Make a cache instance
     *
     * @return CacheInterface
     */
    public static function make() : CacheInterface
    {
        // Create a new Container object, needed by the cache manager.
        $container = new Container;

        // The CacheManager creates the cache "repository" based on config values
        // which are loaded from the config class in the container.
        // For now we will use an array for config to avoid having to set-up
        // too much Illuminate configuration - when we just want to use the cache
        $container['config'] = [
            'cache.default' => 'file',
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../storage/cache',
            ],
        ];

        // To use the file cache driver we need an instance of Illuminate's Filesystem, also stored in the container
        $container['files'] = new Filesystem;
        // Create the CacheManager
        $cacheManager = new CacheManager($container);

        // Get the default cache driver (file in this case)
        return $cacheManager->store('file');
    }
}
