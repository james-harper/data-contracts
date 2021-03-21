<?php

namespace DataContracts\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * A very simplified cache that can be used to prevent
 * JSON schemas from being parsed multiple times
 */
class SimpleCache implements CacheInterface
{
    private $cache = [];

    /**
     * Get the value from the cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->cache[$key];
    }

    /**
     * Check if the cache has the given key
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->cache[$key]);
    }

    /**
     * Put a value into the cache
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $this->cache[$key] = $value;
        return true;
    }

    /**
     * Remove value from cache
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        unset($this->cache[$key]);
        return true;
    }

    /**
     * Empty cache
     *
     * @return bool
     */
    public function clear()
    {
        $this->cache = [];
        return true;
    }

    /**
     * Get multiple cached items by key
     *
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * Set multiple cache values
     *
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Delete multiple keys from cache
     *
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }
}
