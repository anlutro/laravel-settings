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
	/**
	 * The database connection instance.
	 *
	 * @var \Illuminate\Database\Connection
	 */
	protected $connection;

	/**
	 * The table to query from.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Any query constraints that should be applied.
	 *
	 * @var Closure|null
	 */
	protected $queryConstraint;

	/**
	 * Any extra columns that should be added to the rows.
	 *
	 * @var array
	 */
	protected $extraColumns = array();

	/**
	 * @param \Illuminate\Database\Connection $connection
	 * @param string                         $table
	 */
	public function __construct(Connection $connection, $table = null)
	{
		$this->connection = $connection;
		$this->table = $table ?: 'persistant_settings';
	}

	/**
	 * Set the table to query from.
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Set the query constraint.
	 *
	 * @param Closure $callback
	 */
	public function setConstraint(\Closure $callback)
	{
		$this->queryConstraint = $callback;
	}

	/**
	 * Set extra columns to be added to the rows.
	 *
	 * @param array $columns
	 */
	public function setExtraColumns(array $columns)
	{
		$this->extraColumns = $columns;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $data)
	{
		$keys = $this->newQuery()
			->lists('key');

		$updateData = array();

		foreach ($keys as $key) {
			if (static::arrayKeyExists($data, $key)) {
				array_set($data, $key, array_get($data, $key));
				static::arrayUnset($data, $key);
			}
		}

		foreach ($updateData as $key => $value) {
			$this->newQuery()
				->where('key', '=', $key)
				->update(array('value' => $value));
		}

		if ($data) {
			$dbData = $this->prepareWriteData($data);
			$this->newQuery(true)
				->insert($dbData);
		}
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

	/**
	 * {@inheritdoc}
	 */
	protected function read()
	{
		return $this->parseReadData($this->newQuery()->get());
	}

	/**
	 * Parse data coming from the database.
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
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
				$msg = 'Expected array or object, got '.gettype($row);
				throw new \UnexpectedValueException($msg);
			}

			array_set($results, $key, $value);
		}

		return $results;
	}

	/**
	 * Create a new query builder instance.
	 *
	 * @param  $insert  boolean  Whether the query is an insert or not.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	protected function newQuery($insert = false)
	{
		$query = $this->connection->table($this->table);

		if (!$insert) {
			foreach ($this->extraColumns as $key => $value) {
				$query->where($key, '=', $value);
			}
		}

		if ($this->queryConstraint !== null) {
			$callback = $this->queryConstraint;
			$callback($query, $insert);
		}

		return $query;
	}

	protected static function arrayKeyExists(array $array, $key)
	{
		if (array_key_exists($key, $array)) {
			return true;
		}

		if (strpos($key, '.') === false) {
			return false;
		}

		foreach (explode('.', $key) as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	protected static function arrayUnset(array &$array, $key)
	{
		$parts = explode('.', $key);

		while (count($parts) > 1) {
			$part = array_shift($parts);
			if (isset($array[$part]) && is_array($array[$part])) {
				$array =& $array[$part];
			}
		}

		unset($array[array_shift($parts)]);
	}
}
