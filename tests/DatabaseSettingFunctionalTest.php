<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

use Mockery as m;

class DatabaseSettingFunctionalTest extends PHPUnit_Framework_TestCase
{
	protected $container;
	protected $capsule;

	public function setUp()
	{
		$this->container = new \Illuminate\Container\Container;
		$this->capsule = new \Illuminate\Database\Capsule\Manager($this->container);
		$this->capsule->setAsGlobal();
		$this->container['db'] = $this->capsule;
		$this->capsule->addConnection(array(
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		));

		$this->capsule->schema()->create('persistant_settings', function($t) {
			$t->string('key', 64)->unique();
			$t->string('value', 4096);
		});
	}

	public function tearDown()
	{
		$this->capsule->schema()->drop('persistant_settings');
		unset($this->capsule);
		unset($this->container);
	}

	public function makeSettingStore()
	{
		return new \anlutro\LaravelSettings\DatabaseSettingStore($this->capsule->getConnection());
	}

	/** @test */
	public function stuffWorks()
	{
		// batch 1 of data
		$s = $this->makeSettingStore();
		$s->set(array(
			'one' => 'one_old',
			'two.one' => 'one_old',
			'two.two' => 'two_old',
			'three.one' => 'one_old',
		));
		$s->save();

		// batch 2 of data
		$s = $this->makeSettingStore();
		$s->set(array(
			'one' => 'one_new',
			'two.two' => 'two_new',
			'three.two' => 'two_new',
		));
		$s->save();

		// batch 3 of data
		$s = $this->makeSettingStore();
		$s->set(array(
			'one' => 'one_extra_new',
		));
		$s->save();

		// check that data is correct
		$expected = array(
			'one' => 'one_extra_new',
			'two' => array(
				'one' => 'one_old',
				'two' => 'two_new',
			),
			'three' => array(
				'one' => 'one_old',
				'two' => 'two_new',
			),
		);
		$s = $this->makeSettingStore();
		$this->assertEquals($expected, $s->all());

		// remove a setting
		$s = $this->makeSettingStore();
		$s->forget('two.two');
		$s->save();
		$s = $this->makeSettingStore();
		unset($expected['two']['two']);
		$this->assertEquals($expected, $s->all());
	}
}
