<?php

use anlutro\LaravelSettings\ArrayUtil;
use PHPUnit\Framework\TestCase;

class ArrayUtilTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider getGetData
	 */
	public function getReturnsCorrectValue(array $data, $key, $expected)
	{
		$this->assertEquals($expected, ArrayUtil::get($data, $key));
	}

	public function getGetData()
	{
		return array(
			// $data, $key, $expected
			array(array(), 'foo', null),
			array(array('foo' => 'bar'), 'foo', 'bar'),
			array(array('foo' => 'bar'), 'bar', null),
			array(array('foo' => 'bar'), 'foo.bar', null),
			array(array('foo' => array('bar' => 'baz')), 'foo.bar', 'baz'),
			array(array('foo' => array('bar' => 'baz')), 'foo.baz', null),
			array(array('foo' => array('bar' => 'baz')), 'foo', array('bar' => 'baz')),
			array(
				array('foo' => 'bar', 'bar' => 'baz'),
				array('foo', 'bar'),
				array('foo' => 'bar', 'bar' => 'baz')
			),
			array(
				array('foo' => array('bar' => 'baz'), 'bar' => 'baz'),
				array('foo.bar', 'bar'),
				array('foo' => array('bar' => 'baz'), 'bar' => 'baz'),
			),
			array(
				array('foo' => array('bar' => 'baz'), 'bar' => 'baz'),
				array('foo.bar'),
				array('foo' => array('bar' => 'baz')),
			),
			array(
				array('foo' => array('bar' => 'baz'), 'bar' => 'baz'),
				array('foo.bar', 'baz'),
				array('foo' => array('bar' => 'baz'), 'baz' => null),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider getGetWithDefaultsData
	 */
	public function getWithDefaultsReturnsCorrectValue(array $data, $key, $default, $expected)
	{
		$this->assertEquals($expected, ArrayUtil::get($data, $key, $default));
	}

	public function getGetWithDefaultsData()
	{
		return [
			// $data, $key, $default, $expected
			[[], 'foo', 'default', 'default'],
			[['foo' => 'value'], 'foo', 'default', 'value'],
			[[], ['foo'], ['foo' => 'default'], ['foo' => 'default']],
			[['foo' => 'value'], ['foo'], ['foo' => 'default'], ['foo' => 'value']],
		];
	}

	/**
	 * @test
	 * @dataProvider getSetData
	 */
	public function setSetsCorrectKeyToValue(array $input, $key, $value, array $expected)
	{
		ArrayUtil::set($input, $key, $value);
		$this->assertEquals($expected, $input);
	}

	public function getSetData()
	{
		return array(
			array(
				array('foo' => 'bar'),
				'foo',
				'baz',
				array('foo' => 'baz'),
			),
			array(
				array(),
				'foo',
				'bar',
				array('foo' => 'bar'),
			),
			array(
				array(),
				'foo.bar',
				'baz',
				array('foo' => array('bar' => 'baz')),
			),
			array(
				array('foo' => array('bar' => 'baz')),
				'foo.baz',
				'foo',
				array('foo' => array('bar' => 'baz', 'baz' => 'foo')),
			),
			array(
				array('foo' => array('bar' => 'baz')),
				'foo.baz.bar',
				'baz',
				array('foo' => array('bar' => 'baz', 'baz' => array('bar' => 'baz'))),
			),
			array(
				array(),
				'foo.bar.baz',
				'foo',
				array('foo' => array('bar' => array('baz' => 'foo'))),
			),
		);
	}

	/** @test */
	public function setThrowsExceptionOnNonArraySegment()
	{
		$data = array('foo' => 'bar');
		$this->expectException('UnexpectedValueException');
		$this->expectExceptionMessage('Non-array segment encountered');
		ArrayUtil::set($data, 'foo.bar', 'baz');
	}

	/**
	 * @test
	 * @dataProvider getHasData
	 */
	public function hasReturnsCorrectly(array $input, $key, $expected)
	{
		$this->assertEquals($expected, ArrayUtil::has($input, $key));
	}

	public function getHasData()
	{
		return array(
			array(array(), 'foo', false),
			array(array('foo' => 'bar'), 'foo', true),
			array(array('foo' => 'bar'), 'bar', false),
			array(array('foo' => 'bar'), 'foo.bar', false),
			array(array('foo' => array('bar' => 'baz')), 'foo.bar', true),
			array(array('foo' => array('bar' => 'baz')), 'foo.baz', false),
			array(array('foo' => array('bar' => 'baz')), 'foo', true),
			array(array('foo' => null), 'foo', true),
			array(array('foo' => array('bar' => null)), 'foo.bar', true),
		);
	}
}
