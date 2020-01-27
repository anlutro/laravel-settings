<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabaseSettingStoreTest extends TestCase
{
	public function tearDown(): void
	{
		m::close();
	}

	/** @test */
	public function correct_data_is_inserted_and_updated()
	{
		$connection = $this->mockConnection();
		$query = $this->mockQuery($connection);

		$query->shouldReceive('get')->once()->andReturn(array(
			array('key' => 'nest.one', 'value' => 'old'),
		));
		$query->shouldReceive('lists')->atMost(1)->andReturn(array('nest.one'));
		$query->shouldReceive('pluck')->atMost(1)->andReturn(array('nest.one'));
		$dbData = $this->getDbData();
		unset($dbData[1]); // remove the nest.one array member
		$query->shouldReceive('where')->with('key', '=', 'nest.one')->andReturn(m::self())->getMock()
			->shouldReceive('update')->with(array('value' => 'nestone'));
		$self = $this; // 5.3 compatibility
		$query->shouldReceive('insert')->once()->andReturnUsing(function($arg) use($dbData, $self) {
			$self->assertEquals(count($dbData), count($arg));
			foreach ($dbData as $key => $value) {
				$self->assertContains($value, $arg);
			}
		});

		$store = $this->makeStore($connection);
		$store->set('foo', 'bar');
		$store->set('nest.one', 'nestone');
		$store->set('nest.two', 'nesttwo');
		$store->set('array', array('one', 'two'));
		$store->save();
	}

	/** @test */
	public function extra_columns_are_queried()
	{
		$connection = $this->mockConnection();
		$query = $this->mockQuery($connection);
		$query->shouldReceive('where')->once()->with('foo', '=', 'bar')
			->andReturn(m::self())->getMock()
			->shouldReceive('get')->once()->andReturn(array(
				array('key' => 'foo', 'value' => 'bar'),
			));

		$store = $this->makeStore($connection);
		$store->setExtraColumns(array('foo' => 'bar'));
		$this->assertEquals('bar', $store->get('foo'));
	}

	/**  */
	public function extra_columns_are_inserted()
	{
		$connection = $this->mockConnection();
		$query = $this->mockQuery($connection);
		$query->shouldReceive('where')->times(2)->with('extracol', '=', 'extradata')
			->andReturn(m::self());
		$query->shouldReceive('get')->once()->andReturn(array());
		$query->shouldReceive('lists')->atMost(1)->andReturn(array());
		$query->shouldReceive('pluck')->atMost(1)->andReturn(array());
		$query->shouldReceive('insert')->once()->with(array(
			array('key' => 'foo', 'value' => 'bar', 'extracol' => 'extradata'),
		));
		
		$store = $this->makeStore($connection);
        $store->set('foo', 'bar');
        $store->setExtraColumns(array('extracol' => 'extradata'));
		$store->save();
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

	protected function mockConnection()
	{
		return m::mock('Illuminate\Database\Connection');
	}

	protected function mockQuery($connection)
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
