<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Database\Connection;
use Illuminate\Support\Arr;

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
	 * The key column name to query from.
	 *
	 * @var string
	 */
	protected $keyColumn;

	/**
	 * The value column name to query from.
	 *
	 * @var string
	 */
	protected $valueColumn;

	/**
	 * Any query constraints that should be applied.
	 *
	 * @var \Closure|null
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
	public function __construct(Connection $connection, $table = null, $keyColumn = null, $valueColumn = null)
	{
		$this->connection = $connection;
		$this->table = $table ?: 'persistant_settings';
		$this->keyColumn = $keyColumn ?: 'key';
		$this->valueColumn = $valueColumn ?: 'value';
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
	 * Set the key column name to query from.
	 *
	 * @param string $key_column
	 */
	public function setKeyColumn($keyColumn)
	{
		$this->keyColumn = $keyColumn;
	}

	/**
	 * Set the value column name to query from.
	 *
	 * @param string $value_column
	 */
	public function setValueColumn($valueColumn)
	{
		$this->valueColumn = $valueColumn;
	}

	/**
	 * Set the query constraint.
	 *
	 * @param \Closure $callback
	 */
	public function setConstraint(\Closure $callback)
	{
		$this->data = array();
		$this->loaded = false;
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
	public function forget($key)
	{
		parent::forget($key);

		// because the database store cannot store empty arrays, remove empty
		// arrays to keep data consistent before and after saving
		$segments = explode('.', $key);
		array_pop($segments);

		while ($segments) {
			$segment = implode('.', $segments);

			// non-empty array - exit out of the loop
			if ($this->get($segment)) {
				break;
			}

			// remove the empty array and move on to the next segment
			$this->forget($segment);
			array_pop($segments);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $data)
	{
		$keysQuery = $this->newQuery();

		// "lists" was removed in Laravel 5.3, at which point
		// "pluck" should provide the same functionality.
		$method = !method_exists($keysQuery, 'lists') ? 'pluck' : 'lists';
		$keys = $keysQuery->$method($this->keyColumn);

		$insertData = Arr::dot($data);
		$updatedData = Arr::dot($this->updatedData);
		$persistedData = Arr::dot($this->persistedData);
		$updateData = array();
		$deleteKeys = array();

		foreach ($keys as $key) {
            if (isset($updatedData[$key]) && isset($persistedData[$key]) && (string)$updatedData[$key] !== (string)$persistedData[$key]) {
				$updateData[$key] = $updatedData[$key];
            } elseif (!isset($insertData[$key])) {
				$deleteKeys[] = $key;
			}
			unset($insertData[$key]);
		}

		foreach ($updateData as $key => $value) {
			$this->newQuery()
				->where($this->keyColumn, '=', strval($key))
				->update(array($this->valueColumn => $value));
		}

		if ($insertData) {
			$this->newQuery(true)
				->insert($this->prepareInsertData($insertData));
		}

		if ($deleteKeys) {
			$this->newQuery()
				->whereIn($this->keyColumn, $deleteKeys)
				->delete();
		}
	}

	/**
	 * Transforms settings data into an array ready to be insterted into the
	 * database. Call Arr::dot on a multidimensional array before passing it
	 * into this method!
	 *
	 * @param  array $data Call Arr::dot on a multidimensional array before passing it into this method!
	 *
	 * @return array
	 */
	protected function prepareInsertData(array $data)
	{
		$dbData = array();

		if ($this->extraColumns) {
			foreach ($data as $key => $value) {
				$dbData[] = array_merge(
					$this->extraColumns,
					array($this->keyColumn => $key, $this->valueColumn => $value)
				);
			}
		} else {
			foreach ($data as $key => $value) {
				$dbData[] = array($this->keyColumn => $key, $this->valueColumn => $value);
			}
		}

		return $dbData;
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
				$key = $row[$this->keyColumn];
				$value = $row[$this->valueColumn];
			} elseif (is_object($row)) {
				$key = $row->{$this->keyColumn};
				$value = $row->{$this->valueColumn};
			} else {
				$msg = 'Expected array or object, got '.gettype($row);
				throw new \UnexpectedValueException($msg);
			}

			ArrayUtil::set($results, $key, $value);
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
}
