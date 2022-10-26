<?php

use anlutro\LaravelSettings\DatabaseSettingStore;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use Mockery as m;

abstract class AbstractFunctionalTest extends TestCase
{
	protected $defaults;

	protected abstract function createStore(array $data = null);

	protected function getStore(array $data = null)
	{
		$store = $this->createStore($data);
		if ($this->defaults) {
			$store->setDefaults($this->defaults);
		}
		return $store;
	}

	public function tearDown(): void
	{
		m::close();
		$this->defaults = [];
	}

	protected function assertStoreEquals($store, $expected, $message = '')
	{
		$this->assertEquals($expected, $store->all(), $message);
		$store->save();
		$store = $this->getStore();
		$this->assertEquals($expected, $store->all(), $message);
	}

	protected function assertStoreKeyEquals($store, $key, $expected, $message = '')
	{
		$this->assertEquals($expected, $store->get($key), $message);
		$store->save();
		$store = $this->getStore();
		$this->assertEquals($expected, $store->get($key), $message);
	}

	/** @test */
	public function store_is_initially_empty()
	{
		$store = $this->getStore();
		$this->assertEquals(array(), $store->all());
	}

	/** @test */
	public function written_changes_are_saved()
	{
		$store = $this->getStore();
		$store->set('foo', 'bar');
		$this->assertStoreKeyEquals($store, 'foo', 'bar');
	}

	/** @test */
	public function nested_keys_are_nested()
	{
		$store = $this->getStore();
		$store->set('foo.bar', 'baz');
		$this->assertStoreEquals($store, array('foo' => array('bar' => 'baz')));
	}

	/** @test */
	public function cannot_set_nested_key_on_non_array_member()
	{
		$store = $this->getStore();
		$store->set('foo', 'bar');
		$this->expectException('UnexpectedValueException');
		$this->expectExceptionMessage('Non-array segment encountered');
		$store->set('foo.bar', 'baz');
	}

	/** @test */
	public function can_forget_key()
	{
		$store = $this->getStore();
		$store->set('foo', 'bar');
		$store->set('bar', 'baz');
		$this->assertStoreEquals($store, array('foo' => 'bar', 'bar' => 'baz'));
		
		$store->forget('foo');
		$this->assertStoreEquals($store, array('bar' => 'baz'));
	}

	/** @test */
	public function can_forget_nested_key()
	{
		$store = $this->getStore();
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
		$store = $this->getStore(array('foo' => 'bar'));
		$this->assertStoreEquals($store, array('foo' => 'bar'));
		$store->forgetAll();
		$this->assertStoreEquals($store, array());
	}

	/** @test */
	public function defaults_are_respected()
	{
		$this->defaults = ['foo' => 'default', 'bar' => 'default'];
		$store = $this->getStore(array('foo' => 'bar'));
		$this->assertStoreEquals($store, ['foo' => 'bar']);
		$this->assertStoreKeyEquals($store, ['foo', 'bar'], ['foo' => 'bar', 'bar' => 'default']);
	}

    /** @test */
    public function numeric_keys_are_retrieved_correctly()
    {
        $store = $this->getStore();
        $store->set('1234', 'foo');
        $store->set('9876', 'bar');
        $store->load(true);
        $this->assertStoreEquals($store, ['1234' => 'foo', '9876' => 'bar']);
        $this->assertStoreKeyEquals($store, ['1234', '9876'], ['1234' => 'foo', '9876' => 'bar']);
    }
}
