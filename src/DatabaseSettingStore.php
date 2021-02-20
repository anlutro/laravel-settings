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
     	* The createdAt column storing timestamp.
     	*
     	* @var string
     	*/
    	protected $createdAtColumn;

    	/**
     	* The updatedAt column storing timestamp.
     	*
     	* @var string
     	*/
    	protected $updatedAtColumn;

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
	public function __construct(Connection $connection,
                                $table = null,
                                $keyColumn = null,
                                $valueColumn = null,
                                $createdAtColumn = null,
                                $updatedAtColumn = null)
	{
		$this->connection = $connection;
		$this->table = $table ?: 'persistant_settings';
		$this->keyColumn = $keyColumn ?: 'key';
		$this->valueColumn = $valueColumn ?: 'value';
		$this->createdAtColumn = $createdAtColumn ?: 'created_at';
		$this->updatedAtColumn = $updatedAtColumn ?: 'updated_at';
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
     	* Set the value column name to query from.
     	*
     	* @param string $value_column
     	*/
    	public function setCreatedAtColumn($valueColumn)
    	{
		$this->createdAtColumn = $valueColumn;
    	}

    	/**
     	* Set the value column name to query from.
     	*
     	* @param string $value_column
     	*/
    	public function setUpdatedAtColumn($valueColumn)
    	{
		$this->updatedAtColumn = $valueColumn;
    	}

	/**
	 * Set the query constraint.
	 *
	 * @param Closure $callback
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
         * Returns fresh time
	 *
	 * @return integer
	 */
	public function freshTimestamp()
	{
		return time();
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
		$updateData = array();
		$deleteKeys = array();

		foreach ($keys as $key) {
			if (isset($insertData[$key])) {
				$updateData[$key] = $insertData[$key];
			} else {
				$deleteKeys[] = $key;
			}
			unset($insertData[$key]);
		}

		foreach ($updateData as $key => $value) {
			$updatedAtValue = $this->freshTimestamp();
			$this->newQuery()
				->where($this->keyColumn, '=', strval($key))
				->update(array(
					$this->valueColumn => $value,
					$this->updatedAtColumn => $updatedAtValue));
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
	 * database. Call array_dot on a multidimensional array before passing it
	 * into this method!
	 *
	 * @param  array $data Call array_dot on a multidimensional array before passing it into this method!
	 *
	 * @return array
	 */
	protected function prepareInsertData(array $data)
	{
		$dbData = array();

		$freshTimestamp = $this->freshTimestamp();
		$timestamps = array(
			$this->createdAtColumn => $freshTimestamp,
			$this->updatedAtColumn => $freshTimestamp);

		foreach ($data as $key => $value) {
			$dbData[] = array_merge(
				$this->extraColumns,
				array(
					$this->keyColumn => $key,
					$this->valueColumn => $value),
				$timestamps);
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
