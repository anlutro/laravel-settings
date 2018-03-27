<?php

if (! function_exists('setting')) {
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('setting');
        }

        if (!is_null($default)) {
            return app('setting')->set($key, $default);
        }

        return app('setting')->get($key, $default);
    }
}