<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Support\Manager;

class SettingsManager extends Manager
{
	public function getDefaultDriver()
	{
		return $this->app['config']->get('anlutro/l4-settings::store');
	}

	public function createJsonDriver()
	{
		$path = $this->app['config']->get('anlutro/l4-settings::path');
		return new JsonSettingStore($this->app['files'], $path);
	}

	public function createDatabaseDriver()
	{
		$connection = $this->app['db']->connection();
		$table = $this->app['config']->get('anlutro/l4-settings::table');
		return new DatabaseSettingStore($connection, $table);
	}
}
