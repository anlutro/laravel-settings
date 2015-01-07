<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

use Mockery as m;

class DatabaseSettingStoreTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function testGetAndSet()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);
		$query->shouldReceive('get')->once()->andReturn(array());
		$store = $this->makeStore($connection);

		$store->set('foo', 'bar');
		$this->assertEquals('bar', $store->get('foo'));
	}

	public function testCorrectDataIsInserted()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);

		$query->shouldReceive('get')->once()->andReturn(array());
		$query->shouldReceive('lists')->once()->andReturn(array());
		$query->shouldReceive('insert')->once()->with($this->getDbData());

		$store = $this->makeStore($connection);
		$store->set('foo', 'bar');
		$store->set('nest.one', 'nestone');
		$store->set('nest.two', 'nesttwo');
		$store->set('array', array('one', 'two'));
		$store->save();
	}

	public function testCorrectDataIsUpdated()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);

		$query->shouldReceive('get')->once()->andReturn(array(array('key' => 'nest.one', 'value' => 'old')));
		$query->shouldReceive('lists')->once()->andReturn(array('nest.one'));
		$dbData = $this->getDbData();
		unset($dbData[1]); // remove the nest.one array member
		$query->shouldReceive('where')->with('key', '=', 'nest.one')->andReturn(m::self())->getMock()
			->shouldReceive('update')->with(array('value' => 'nestone'));
		$test = $this;
		$query->shouldReceive('insert')->once()->andReturnUsing(function($arg) use($dbData, $test) {
			$this->assertEquals(count($dbData), count($arg));
			foreach ($dbData as $key => $value) {
				$test->assertContains($value, $arg);
			}
		});

		$store = $this->makeStore($connection);
		$store->set('foo', 'bar');
		$store->set('nest.one', 'nestone');
		$store->set('nest.two', 'nesttwo');
		$store->set('array', array('one', 'two'));
		$store->save();
	}

	public function testCorrectDataIsRead()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);

		$query->shouldReceive('get')->once()->andReturn($this->getDbData());
		
		$store = $this->makeStore($connection);
		$this->assertEquals('bar', $store->get('foo'));
		$this->assertEquals('nestone', $store->get('nest.one'));
		$this->assertEquals('nesttwo', $store->get('nest.two'));
		$this->assertEquals(array('one', 'two'), $store->get('array'));
	}

	public function testExtraColumnsAreQueried()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);
		$query->shouldReceive('where')->once()->with('foo', '=', 'bar')
			->andReturn(m::self())->getMock()
			->shouldReceive('get')->once()->andReturn(array(array('key' => 'foo', 'value' => 'bar')));

		$store = $this->makeStore($connection);
		$store->setExtraColumns(array('foo' => 'bar'));
		$this->assertEquals('bar', $store->get('foo'));
	}

	public function testExtraColumnsAreInserted()
	{
		$connection = $this->makeConnection();
		$query = $this->makeQuery($connection);
		$query->shouldReceive('where')->times(2)->with('extracol', '=', 'extradata')
			->andReturn(m::self());
		$query->shouldReceive('get')->once()->andReturn(array());
		$query->shouldReceive('lists')->once()->andReturn(array());
		$query->shouldReceive('insert')->once()->with(array(array('key' => 'foo', 'value' => 'bar', 'extracol' => 'extradata')));
		
		$store = $this->makeStore($connection);
		$store->setExtraColumns(array('extracol' => 'extradata'));
		$store->set('foo', 'bar');
		$store->save();
	}

	protected function getPhpData()
	{
		return array(
			'foo' => 'bar',
			'nest' => array(
				'one' => 'nestone',
				'two' => 'nesttwo',
			),
			'array' => array('one', 'two'),
		);
	}

	protected function getDbData()
	{
		return array(
			array('key' => 'foo', 'value' => 'bar'),
			array('key' => 'nest.one', 'value' => 'nestone'),
			array('key' => 'nest.two', 'value' => 'nesttwo'),
			array('key' => 'array.0', 'value' => 'one'),
			array('key' => 'array.1', 'value' => 'two'),
		);
	}

	protected function makeConnection()
	{
		return m::mock('Illuminate\Database\Connection');
	}

	protected function makeQuery($connection)
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$connection->shouldReceive('table')->andReturn($query);
		return $query;
	}

	protected function makeStore($connection)
	{
		return new anlutro\LaravelSettings\DatabaseSettingStore($connection);
	}
}
