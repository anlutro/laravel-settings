<?php

use Mockery as m;

class JsonSettingStoreTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function mockFilesystem()
	{
		return m::mock('Illuminate\Filesystem\Filesystem');
	}

	protected function makeStore($files, $path = 'fakepath', $options = 0)
	{
		return new anlutro\LaravelSettings\JsonSettingStore($files, $path, $options);
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

    /**
     * @test
     */
    public function allows_construction_with_json_options()
    {
      $data = ['abc' => 123];

  		$files = $this->mockFilesystem();
  		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
  		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
  		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('{}');
  		$files->shouldReceive('put')->once()->with('fakepath', json_encode(compact('data'), JSON_PRETTY_PRINT));

      $store = $this->makeStore($files, 'fakepath', JSON_PRETTY_PRINT);
    	$store->set('data', $data);
      $store->save();
    }

    /**
     * @test
     */
    public function can_enable_json_options()
    {
      $data = ['abc' => 123];

  		$files = $this->mockFilesystem();
  		$files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
  		$files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
  		$files->shouldReceive('get')->once()->with('fakepath')->andReturn('{}');
  		$files->shouldReceive('put')->once()->with('fakepath', json_encode(compact('data')));

      $store = $this->makeStore($files, 'fakepath');
    	$store->set('data', $data);
      $store->save();

  		$files->shouldReceive('put')->once()->with('fakepath', json_encode(compact('data'), JSON_PRETTY_PRINT));
      $store->enablePrettyPrint();

    	$store->set('data', $data);
      $store->save();

    }
}
