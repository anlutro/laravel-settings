<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Connection;

class DatabaseWithCacheSettingStore extends DatabaseSettingStore
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var int
     */
    protected $ttl;

    protected $key = 'setting:database:cache';

    public function __construct(Connection $connection, $table = null, $keyColumn = null, $valueColumn = null, CacheManager $cacheManager, $ttl)
    {
        parent::__construct($connection, $table, $keyColumn, $valueColumn);
        $this->cacheManager = $cacheManager;
        $this->ttl = $ttl;
    }

    protected function read()
    {
        return $this->cacheManager->remember($this->key, $this->ttl, function () {
            return parent::read();
        });
    }
}
