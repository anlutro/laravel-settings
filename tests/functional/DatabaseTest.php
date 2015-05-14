<?php

class DatabaseTest extends AbstractFunctionalTest
{
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

	protected function createStore(array $data = array())
	{
		if ($data) {
			$store = $this->createStore();
			$store->set($data);
			$store->save();
			unset($store);
		}

		return new \anlutro\LaravelSettings\DatabaseSettingStore(
			$this->capsule->getConnection()
		);
	}
}
