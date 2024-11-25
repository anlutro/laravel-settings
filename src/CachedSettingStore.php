<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

abstract class CachedSettingStore extends SettingStore
{
    /**
     * Cache key for save
     */
    const CACHE_KEY = 'setting:cache';

    /**
     * @var \Illuminate\Contracts\Cache\Store|\Illuminate\Cache\StoreInterface
     */
    protected $cache = null;

    /**
     * Cache TTL in seconds.
     *
     * @var int
     */
    protected $cacheTtl = 15;

    /**
     * Whether to reset the cache when changing a setting.
     *
     * @var boolean
     */
    protected $cacheForgetOnWrite = true;

    /**
     * Set the cache.
     * @param \Illuminate\Contracts\Cache\Store|\Illuminate\Cache\StoreInterface $cache
     * @param int $ttl
     * @param bool $forgetOnWrite
     */
    public function setCache($cache, $ttl = null, $forgetOnWrite = null)
    {
        $this->cache = $cache;
        if ($ttl !== null) {
            $this->cacheTtl = $ttl;
        }
        if ($forgetOnWrite !== null) {
            $this->cacheForgetOnWrite = $forgetOnWrite;
        }
    }

    /**
     * Save any changes done to the settings data.
     *
     * @return void
     */
    public function save()
    {
        if ($this->unsaved && $this->cache && $this->cacheForgetOnWrite) {
            $this->cache->forget(static::CACHE_KEY);
        }

        parent::save();
    }

    /**
     * Read data from a store
     *
     * @return array
     */
    private function readData()
    {
        if ($this->cache) {
            return $this->cache->remember(static::CACHE_KEY, $this->cacheTtl, function () {
                return $this->read();
            });
        }

        return $this->read();
    }

}
