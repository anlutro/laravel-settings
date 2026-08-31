<?php
/**
 * Laravel 4 - Persistent Settings
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings\Facades;

use \Illuminate\Support\Facades\Facade;

/**
 * @method static void getDefaultDriver()
 * @method static void createJsonDriver()
 * @method static void createDatabaseDriver()
 * @method static void createMemoryDriver()
 * @method static void createArrayDriver()
 * @method static mixed driver(\UnitEnum|string|null $driver = null)
 * @method static \anlutro\LaravelSettings\SettingsManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \anlutro\LaravelSettings\SettingsManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \anlutro\LaravelSettings\SettingsManager forgetDrivers()
 * @method static void setDefaults(array $defaults)
 * @method static void setCache(\Illuminate\Contracts\Cache\Store|mixed $cache, int $ttl = null, bool $forgetOnWrite = null)
 * @method static mixed get(string|array $key, mixed $default = null)
 * @method static mixed has(string $key)
 * @method static void set(string|array $key, mixed $value = null)
 * @method static void forget(string $key)
 * @method static void forgetAll()
 * @method static array all()
 * @method static void save()
 * @method static void load(void $force = false)
 *
 * @see \anlutro\LaravelSettings\SettingsManager
 * @see \anlutro\LaravelSettings\SettingStore
 */
class Setting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'anlutro\LaravelSettings\SettingsManager';
    }
}
