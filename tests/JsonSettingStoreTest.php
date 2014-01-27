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
	public function testExceptionWhenNotWritable()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(false);
		$store = $this->makeStore($files);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionWhenPutFails()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(false);
		$files->shouldReceive('put')->once()->with('fakepath', '[]')->andReturn(false);
		$store = $this->makeStore($files);
	}

	public function testGetAndSet()
	{
		$files = $this->makeFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[]');

		$store = $this->makeStore($files);
		$store->set('foo', 'bar');
		$this->assertEquals('bar', $store->get('foo'));
	}

	public function testWriteData()
	{
		$files = $this->makeFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[]');
		$files->shouldReceive('put')->once()->with('fakepath', $this->getJsonData());

		$store = $this->makeStore($files);
		$store->set('foo', 'bar');
		$store->set('nest.one', 'nestone');
		$store->set('nest.two', 'nesttwo');
		$store->set('array', array('one', 'two'));
		$store->save();
	}

	public function testReadData()
	{
		$files = $this->makeFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn($this->getJsonData());

		$store = $this->makeStore($files);
		$this->assertEquals('bar', $store->get('foo'));
		$this->assertEquals('nestone', $store->get('nest.one'));
		$this->assertEquals('nesttwo', $store->get('nest.two'));
		$this->assertEquals(array('one', 'two'), $store->get('array'));
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testExceptionWhenInvalidJson()
	{
		$files = $this->makeFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[[!1!11]');

		$store = $this->makeStore($files);
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

	protected function makeFilesystem($exists = true, $isWritable = true)
	{
		return m::mock('Illuminate\Filesystem\Filesystem');
	}

	protected function makeStore($files, $path = 'fakepath')
	{
		return new anlutro\LaravelSettings\JsonSettingStore($files, $path);
	}
}
