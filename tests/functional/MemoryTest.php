<?php

class MemoryTest extends AbstractFunctionalTest
{
	protected function assertStoreEquals($store, $expected, $message = '')
	{
		$this->assertEquals($expected, $store->all(), $message);
		// removed persistance test assertions
	}

	protected function assertStoreKeyEquals($store, $key, $expected, $message = '')
	{
		$this->assertEquals($expected, $store->get($key), $message);
		// removed persistance test assertions
	}

	protected function createStore(array $data = null)
	{
		return new \anlutro\LaravelSettings\MemorySettingStore($data);
	}
}
