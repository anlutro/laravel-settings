<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */


use Mockery as m;

class JsonSettingStoreTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetupWithInvalidPath()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('isDirectory')->once()->andReturn(false);
		$store = $this->makeStore($files);
	}

	public function testGetAndSet()
	{
		$files = $this->makeFilesystem();
		$store = $this->makeStore($files);
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[]');

		$store->set('foo', 'bar');
		$this->assertEquals('bar', $store->get('foo'));
	}

	public function testWriteData()
	{
		$files = $this->makeFilesystem();
		$store = $this->makeStore($files);
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[]');
		$files->shouldReceive('put')->once()->with('fakepath', $this->getJsonData());

		$store->set('foo', 'bar');
		$store->set('nest.one', 'nestone');
		$store->set('nest.two', 'nesttwo');
		$store->set('array', array('one', 'two'));
		$store->save();
	}

	public function testReadData()
	{
		$files = $this->makeFilesystem();
		$store = $this->makeStore($files);
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn($this->getJsonData());

		$this->assertEquals('bar', $store->get('foo'));
		$this->assertEquals('nestone', $store->get('nest.one'));
		$this->assertEquals('nesttwo', $store->get('nest.two'));
		$this->assertEquals(array('one', 'two'), $store->get('array'));
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testReadInvalidData()
	{
		$files = $this->makeFilesystem();
		$store = $this->makeStore($files);
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[[!1!11]');

		$store->get('foo');
	}

	protected function getTestData()
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

	protected function getJsonData()
	{
		return json_encode($this->getTestData());
	}

	protected function makeFilesystem($isDirectory = true)
	{
		$mock = m::mock('Illuminate\Filesystem\Filesystem');
		$mock->shouldReceive('isDirectory')->once()->andReturn($isDirectory);
		return $mock;
	}

	protected function makeStore($connection)
	{
		return new anlutro\LaravelSettings\JsonSettingStore($connection, 'fakepath');
	}
}
