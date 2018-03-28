<?php

use Mockery as m;
use Illuminate\Container\Container;

class HelperTest extends PHPUnit_Framework_TestCase
{
	public static $functions;

	public function setUp()
	{
		self::$functions = m::mock();

		if (version_compare(\Illuminate\Foundation\Application::VERSION, '5.0', '<')) {
		    $this->markTestSkipped('Laravel 5 feature.');
		    return;
		}

		Container::setInstance(new Container);

		$store = m::mock('anlutro\LaravelSettings\SettingStore');

		app()->bind('setting', function() use ($store) {
			return $store;
		});
	}

	/** @test */
	public function helper_without_parameters_returns_store() 
	{
		$this->assertInstanceOf('anlutro\LaravelSettings\SettingStore', setting());
	}

	/** @test */
	public function single_parameter_get_a_key_from_store() 
	{
		app('setting')->shouldReceive('get')->with('foo', null)->once();

		setting('foo');
	}

	public function two_parameters_return_a_default_value()
	{
		app('setting')->shouldReceive('get')->with('foo', 'bar')->once();

		setting('foo', 'bar');
	}


	/** @test */
	public function array_parameter_call_set_method_into_store() 
	{
		app('setting')->shouldReceive('set')->with(['foo', 'bar'])->once();

		setting(['foo', 'bar']);
	}
}