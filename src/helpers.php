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