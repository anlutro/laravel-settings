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

class Setting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'anlutro\LaravelSettings\SettingsManager';
    }
}
