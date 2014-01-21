<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Database\Connection;

class DatabaseSettingStore extends SettingStore
{
	protected $connection;
	protected $table;
	protected $queryConstraint;
	protected $extraColumns = array();

	public function __construct(Connection $connection, $table = null)
	{
		$this->connection = $connection;
		$this->table = $table ?: 'persistant_settings';
	}

	public function setTable($table)
	{
		$this->table = $table;
	}

	public function setConstraint(\Closure $callback)
	{
		$this->queryConstraint = $callback;
	}

	public function setExtraColumns(array $columns)
	{
		$this->extraColumns = $columns;
	}

	protected function write(array $data)
	{
		$this->newQuery()->truncate();
		$dbData = $this->prepareWriteData($this->data);
		$this->newQuery()->insert($dbData);
	}

	/**
	 * Transforms settings data into an array ready to be insterted into the
	 * database.
	 * 
	 * ['foo' => ['bar' => 1, 'baz', => 2]] is first transformed into
	 * ['foo.bar' => 1, 'foo.baz' => 2] which is then transformed into
	 * [['key' => 'foo.bar', 'value' => 1], ...]
	 * 
	 * ['foo' => ['bar', 'baz']] is transformed into
	 * ['foo.0' => 'bar', 'foo.1' => 'baz'] and so on.
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
	protected function prepareWriteData($data)
	{
		$data = array_dot($data);
		$extra = $this->extraColumns;

		return array_map(function($key, $value) use($extra) {
			return array_merge($extra, array('key' => $key, 'value' => $value));
		}, array_keys($data), array_values($data));
	}

	protected function read()
	{
		return $this->parseReadData($this->newQuery()->get());
	}

	public function parseReadData($data)
	{
		$results = array();

		foreach ($data as $row) {
			if (is_array($row)) {
				$key = $row['key'];
				$value = $row['value'];
			} elseif (is_object($row)) {
				$key = $row->key;
				$value = $row->value;
			} else {
				dd($row);
			}

			array_set($results, $key, $value);
		}

		return $results;
	}

	protected function newQuery()
	{
		$query = $this->connection->table($this->table);
		foreach ($this->extraColumns as $key => $value) {
			$query->where($key, '=', $value);
		}
		return $query;
	}
}
