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

		return new JsonSettingStore($this->getSupportedContainer()['files'], $path);
	}

	public function createDatabaseDriver()
	{
		$connectionName = $this->getConfig('anlutro/l4-settings::connection');
		$connection = $this->getSupportedContainer()['db']->connection($connectionName);
		$table = $this->getConfig('anlutro/l4-settings::table');
		$keyColumn = $this->getConfig('anlutro/l4-settings::keyColumn');
		$valueColumn = $this->getConfig('anlutro/l4-settings::valueColumn');

		return new DatabaseSettingStore($connection, $table, $keyColumn, $valueColumn);
	}

	public function createMemoryDriver()
	{
		return new MemorySettingStore();
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

		return $this->getSupportedContainer()['config']->get($key);
	}

	protected function createDriver($driver)
	{
		$instance = parent::createDriver($driver);

		$instance->setDefaults($this->getConfig('anlutro/l4-settings::defaults'));

		if ($instance instanceof CachedSettingStore && $this->getConfig('anlutro/l4-settings::enableCache')) {
			$instance->setCache(
				$this->getSupportedContainer()['cache'],
				$this->getConfig('anlutro/l4-settings::cacheTtl'),
				$this->getConfig('anlutro/l4-settings::forgetCacheByWrite')
			);
		}

		return $instance;
	}

	protected function getSupportedContainer()
    {
	    return isset($this->app) ? $this->app : $this->container;
    }
}
