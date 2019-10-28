<?php

if (! function_exists('setting')) {
    function setting($key = null, $default = null)
    {
        $setting = app('setting');

        if (is_array($key)) {
            $setting->set($key);
        } elseif (! is_null($key)) {
            return $setting->get($key, $default);
        }

        return $setting;
    }
}

/*
* Compatibility with Laravel >= 5.8 and < 5.7
*/
if (! function_exists('custom_array_dot')) {
    function custom_array_dot($array)
	{
		//Laravel <= 5.7
		return  function_exists('array_dot')?array_dot($array):\Illuminate\Support\Arr::dot($array);
	}
}