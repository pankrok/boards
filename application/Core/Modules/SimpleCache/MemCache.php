<?php

declare(strict_types=1);

namespace Application\Core\Modules\SimpleCache;

class MemCache extends Cache
{
    protected $memcache;
 
    /**
    * Connect to memcached server.
    *
    * @param array $server     The unique key of this item in the cache.
    *
    * @return void
    */
    
    public function init(array $server) : void
    {
        $this->memcache = new \Memcache;
        try {
            if (@$this->memcache->connect($server['server'], $server['port']) === false) {
                throw new \Exception('Memcached server connection error', 444);
            }
        } catch (\Exception $e) {
            echo '<b>BOARDS CACHE ERROR!</b><br />';
            echo 'CODE: ' . $e->getCode() . '<br />';
            echo htmlentities($e->getMessage()) ;
            die();
        }
    }
    
    /**
    * Fetches a value from the cache.
    *
    * @param string $key     The unique key of this item in the cache.
    * @param mixed  $default Default value to return if the key does not exist.
    *
    * @return mixed The value of the item from the cache, or $default in case of cache miss.
    *
    * @throws \Psr\SimpleCache\InvalidArgumentException
    *   MUST be thrown if the $key string is not a legal value.
    */
    public function get($key, $default = false)
    {
        try {
            $content = $this->memcache->get($this->path . $key);
            if ($content === false) {
                return $default;
            }
            $content = unserialize($content);
                  
            if ($content['ttl'] < time()) {
                self::delete($key);
                return $default;
            }
            
            return $content['value'];
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($key);
        }
        return $default;
    }

    /**
    * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
    *
    * @param string                 $key   The key of the item to store.
    * @param mixed                  $value The value of the item to store. Must be serializable.
    * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
    *                                      the driver supports TTL then the library may set a default value
    *                                      for it or let the driver take care of that.
    *
    * @return bool True on success and false on failure.
    *
    * @throws \Psr\SimpleCache\InvalidArgumentException
    *   MUST be thrown if the $key string is not a legal value.
    */
    public function set($key, $value, $ttl = null)
    {
        $ttl = intval($ttl ?? $this->ttl);
        $content = serialize([
            'value' => $value,
            'ttl' => (time()+$ttl)
        ]);
        
        try {
            $this->memcache->set($this->path  . $key, $content, MEMCACHE_COMPRESSED, $ttl);
            return true;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($key);
        }
        
        return false;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        try {
            $this->memcache->delete($this->path  . $key);
            return true;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($key);
        }
        
        return false;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        try {
            $this->memcache->flush();
            return true;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($key);
        }
        
        return false;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = false)
    {
        $return = [];
        try {
            foreach ($keys as $i => $key) {
                $return[$i] = self::get($key);
            }
            
            return $return;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($keys);
        }
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        try {
            foreach ($values as $key => $value) {
                self::set($key, $value, $ttl);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($values);
        }
        
        return false;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        try {
            foreach ($keys as $key) {
                self::delete($key);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($keys);
        }
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it, making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        try {
            if ($this->memcache->get($this->path . $key) !== false) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Psr\SimpleCache\InvalidArgumentException($keys);
        }
        
        return $key;
    }
}
