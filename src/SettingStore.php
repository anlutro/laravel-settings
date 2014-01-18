<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

abstract class SettingStore
{
	protected $data = array();
	protected $unsaved = false;
	protected $loaded = false;

	public function get($key, $default = null)
	{
		$this->checkLoaded();
		return array_get($this->data, $key, $default);
	}

	public function has($key)
	{
		$this->checkLoaded();
		return array_key_exists($key, $this->data);
	}

	public function set($key, $value)
	{
		$this->checkLoaded();
		$this->unsaved = true;
		array_set($this->data, $key, $value);
	}

	public function forget($key)
	{
		if ($this->has($key)) unset($this->data[$keys]);
	}

	public function all()
	{
		$this->checkLoaded();
		return $this->data;
	}

	public function save()
	{
		if (!$this->unsaved) return;

		$this->write($this->data);
		$this->unsaved = false;
	}

	protected function checkLoaded()
	{
		if (!$this->loaded) {
			$this->data = $this->read();
			$this->loaded = true;
		}
	}

	/**
	 * Read the data from the store.
	 *
	 * @return array
	 */
	abstract protected function read();

	/**
	 * Write the data into the store.
	 *
	 * @param  array  $data
	 *
	 * @return void
	 */
	abstract protected function write(array $data);
}
