<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;


class JsonSettingStoreTest extends TestCase
{
	public function tearDown(): void
	{
		m::close();
	}

	protected function mockFilesystem()
	{
		return m::mock('Illuminate\Filesystem\Filesystem');
	}

	protected function makeStore($files, $path = 'fakepath')
	{
		return new anlutro\LaravelSettings\JsonSettingStore($files, $path);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function throws_exception_when_file_not_writeable()
	{
		$files = $this->mockFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(false);
		$store = $this->makeStore($files);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function throws_exception_when_files_put_fails()
	{
		$files = $this->mockFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(false);
		$files->shouldReceive('put')->once()->with('fakepath', '{}')->andReturn(false);
		$store = $this->makeStore($files);
	}

	/**
	 * @test
	 * @expectedException RuntimeException
	 */
	public function throws_exception_when_file_contains_invalid_json()
	{
		$files = $this->mockFilesystem();
		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('[[!1!11]');

		$store = $this->makeStore($files);
		$store->get('foo');
	}
}
