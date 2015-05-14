<?php

use anlutro\LaravelSettings\JsonSettingStore;
use anlutro\LaravelSettings\DatabaseSettingStore;

abstract class AbstractFunctionalTest extends PHPUnit_Framework_TestCase
{
	protected abstract function createStore(array $data = array());

	protected function assertStoreEquals($store, $expected, $message = null)
	{
		$this->assertEquals($expected, $store->all(), $message);
		$store->save();
		$store = $this->createStore();
		$this->assertEquals($expected, $store->all(), $message);
	}

	protected function assertStoreKeyEquals($store, $key, $expected, $message = null)
	{
		$this->assertEquals($expected, $store->get($key), $message);
		$store->save();
		$store = $this->createStore();
		$this->assertEquals($expected, $store->get($key), $message);
	}

	/** @test */
	public function store_is_initially_empty()
	{
		$store = $this->createStore();
		$this->assertEquals(array(), $store->all());
	}

	/** @test */
	public function written_changes_are_saved()
	{
		$store = $this->createStore();
		$store->set('foo', 'bar');
		$this->assertStoreKeyEquals($store, 'foo', 'bar');
	}

	/** @test */
	public function nested_keys_are_nested()
	{
		$store = $this->createStore();
		$store->set('foo.bar', 'baz');
		$this->assertStoreEquals($store, array('foo' => array('bar' => 'baz')));
	}

	/** @test */
	public function cannot_set_nested_key_on_non_array_member()
	{
		$store = $this->createStore();
		$store->set('foo', 'bar');
		$this->setExpectedException('UnexpectedValueException', 'Non-array segment encountered');
		$store->set('foo.bar', 'baz');
	}

	/** @test */
	public function can_forget_key()
	{
		$store = $this->createStore();
		$store->set('foo', 'bar');
		$store->set('bar', 'baz');
		$this->assertStoreEquals($store, array('foo' => 'bar', 'bar' => 'baz'));
		
		$store->forget('foo');
		$this->assertStoreEquals($store, array('bar' => 'baz'));
	}

	/** @test */
	public function can_forget_nested_key()
	{
		$store = $this->createStore();
		$store->set('foo.bar', 'baz');
		$store->set('foo.baz', 'bar');
		$store->set('bar.foo', 'baz');
		$this->assertStoreEquals($store, array(
			'foo' => array(
				'bar' => 'baz',
				'baz' => 'bar',
			),
			'bar' => array(
				'foo' => 'baz',
			),
		));
		
		$store->forget('foo.bar');
		$this->assertStoreEquals($store, array(
			'foo' => array(
				'baz' => 'bar',
			),
			'bar' => array(
				'foo' => 'baz',
			),
		));

		$store->forget('bar.foo');
		$expected = array(
			'foo' => array(
				'baz' => 'bar',
			),
			'bar' => array(
			),
		);
		if ($store instanceof DatabaseSettingStore) {
			unset($expected['bar']);
		}
		$this->assertStoreEquals($store, $expected);
	}

	/** @test */
	public function can_forget_all()
	{
		$store = $this->createStore(array('foo' => 'bar'));
		$this->assertStoreEquals($store, array('foo' => 'bar'));
		$store->forgetAll();
		$this->assertStoreEquals($store, array());
	}
}
