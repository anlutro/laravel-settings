<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Filesystem\Filesystem;

class JsonSettingStore extends SettingStore
{
	/**
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @param string                           $path
	 */
	public function __construct(Filesystem $files, $path = null)
	{
		$this->files = $files;
		$this->setPath($path ?: storage_path() . '/settings.json');
	}

	/**
	 * Set the path for the JSON file.
	 *
	 * @param string $path
	 */
	public function setPath($path)
	{
		// If the file does not already exist, we will attempt to create it.
		if (!$this->files->exists($path)) {
			$result = $this->files->put($path, '{}');
			if ($result === false) {
				throw new \InvalidArgumentException("Could not write to $path.");
			}
		}

		if (!$this->files->isWritable($path)) {
			throw new \InvalidArgumentException("$path is not writable.");
		}

		$this->path = $path;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function read()
	{
		$contents = $this->files->get($this->path);

		$data = json_decode($contents, true);

		if ($data === null) {
			throw new \RuntimeException("Invalid JSON in {$this->path}");
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $data)
	{
		if ($data) {
			$contents = json_encode($data);
		} else {
			$contents = '{}';
		}

		$this->files->put($this->path, $contents);
	}
}
