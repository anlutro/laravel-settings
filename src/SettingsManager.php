<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Support\Manager;
use Illuminate\Foundation\Application;

class SettingsManager extends Manager
{
	public function getDefaultDriver()
	{
		return $this->getConfig('anlutro/l4-settings::store');
	}

	public function createJsonDriver()
	{
		$path = $this->getConfig('anlutro/l4-settings::path');

		$store = new JsonSettingStore($this->container['files'], $path);

		return $this->wrapDriver($store);
	}

	public function createDatabaseDriver()
	{
		$connectionName = $this->getConfig('anlutro/l4-settings::connection');
		$connection = $this->container['db']->connection($connectionName);
		$table = $this->getConfig('anlutro/l4-settings::table');
		$keyColumn = $this->getConfig('anlutro/l4-settings::keyColumn');
		$valueColumn = $this->getConfig('anlutro/l4-settings::valueColumn');

		$store = new DatabaseSettingStore($connection, $table, $keyColumn, $valueColumn);

		return $this->wrapDriver($store);
	}

	public function createMemoryDriver()
	{
		return $this->wrapDriver(new MemorySettingStore());
	}

	public function createArrayDriver()
	{
		return $this->createMemoryDriver();
	}

	protected function getConfig($key)
	{
		if (version_compare(Application::VERSION, '5.0', '>=')) {
			$key = str_replace('anlutro/l4-settings::', 'settings.', $key);
		}

		return $this->container['config']->get($key);
	}

	protected function wrapDriver($store)
	{
		$store->setDefaults($this->getConfig('anlutro/l4-settings::defaults'));

		if ($this->getConfig('anlutro/l4-settings::enableCache')) {
			$store->setCache(
				$this->container['cache'],
				$this->getConfig('anlutro/l4-settings::cacheTtl'),
				$this->getConfig('anlutro/l4-settings::forgetCacheByWrite')
			);
		}

		return $store;
	}
}
