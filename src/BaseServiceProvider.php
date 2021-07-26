<?php

/**
 * Laravel 4 - Persistent Settings.
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 */

namespace anlutro\LaravelSettings;

if (interface_exists('Illuminate\Contracts\Support\DeferrableProvider')) {
    class BaseServiceProvider extends \Illuminate\Support\ServiceProvider implements \Illuminate\Contracts\Support\DeferrableProvider
    {
    }
} else {
    class BaseServiceProvider extends \Illuminate\Support\ServiceProvider
    {
    }
}
