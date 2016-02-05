<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

class MemorySettingStore extends SettingStore
{
	/**
	 * @param array $data
	 */
	public function __construct(array $data = null)
	{
		if ($data) {
			$this->data = $data;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function read()
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $data)
	{
		// do nothing
	}
}
