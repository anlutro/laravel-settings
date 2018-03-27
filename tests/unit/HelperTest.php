<?php

use Mockery as m;

class HelperTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function helper_without_parameters_returns_store() 
	{
		$this->assertInstanceOf('anlutro\LaravelSettings\SettingStore', setting());
	}

	/** @test */
	public function single_parameter_get_a_key_from_store() 
	{
		app()->shouldReceive('get')->with('foo', null);

		setting('foo');
	}


	/** @test */
	public function two_parameters_set_to_store() 
	{
		app()->shouldReceive('set')->with('foo', 'bar');

		setting('foo', 'bar');
	}
}

$containerInstance = m::mock('anlutro\LaravelSettings\SettingStore');

function app() {
	global $containerInstance;
	return $containerInstance;
}