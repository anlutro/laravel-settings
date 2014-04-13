<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

abstract class SettingStore
{
	/**
	 * The settings data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Whether the store has changed since it was last loaded.
	 *
	 * @var boolean
	 */
	protected $unsaved = false;

	/**
	 * Whether the settings data are loaded.
	 *
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Get a specific key from the settings data.
	 *
	 * @param  string $key
	 * @param  mixed  $default Optional default value.
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$this->checkLoaded();
		return array_get($this->data, $key, $default);
	}

	/**
	 * Determine if a key exists in the settings data.
	 *
	 * @param  string  $key
	 *
	 * @return boolean
	 */
	public function has($key)
	{
		$this->checkLoaded();
		return array_key_exists($key, $this->data);
	}

	/**
	 * Set a specific key to a value in the settings data.
	 *
	 * @param string|array $key   Key string or associative array of key => value
	 * @param mixed        $value Optional only if the first argument is an array
	 */
	public function set($key, $value = null)
	{
		$this->checkLoaded();
		$this->unsaved = true;
		
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				array_set($this->data, $k, $v);
			}
		} else {
			array_set($this->data, $key, $value);
		}
	}

	/**
	 * Unset a key in the settings data.
	 *
	 * @param  string $key
	 */
	public function forget($key)
	{
		if ($this->has($key)) unset($this->data[$key]);
	}

	/**
	 * Get all settings data.
	 *
	 * @return array
	 */
	public function all()
	{
		$this->checkLoaded();
		return $this->data;
	}

	/**
	 * Save any changes done to the settings data.
	 */
	public function save()
	{
		if (!$this->unsaved) return;

		$this->write($this->data);
		$this->unsaved = false;
	}

	/**
	 * Check if the settings data has been loaded.
	 */
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
