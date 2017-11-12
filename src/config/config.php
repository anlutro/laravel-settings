<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Settings Store
	|--------------------------------------------------------------------------
	|
	| This option controls the default settings store that gets used while
	| using this settings library.
	|
	| Supported: "json", "database"
	|
	*/
	'store' => env('SETTINGS_STORE', 'json'),

	/*
	|--------------------------------------------------------------------------
	| JSON Store
	|--------------------------------------------------------------------------
	|
	| If the store is set to "json", settings are stored in the defined
	| file path in JSON format. Use full path to file.
	|
	*/
	'path' => storage_path().env('SETTINGS_PATH', '/settings.json'),

	/*
	|--------------------------------------------------------------------------
	| Database Store
	|--------------------------------------------------------------------------
	|
	| The settings are stored in the defined file path in JSON format.
	| Use full path to JSON file.
	|
	*/
	// If set to null, the default connection will be used.
	'connection' => env('SETTINGS_CONNECTION', null),
	// Name of the table used.
	'table' => env('SETTINGS_TABLE', 'settings'),
	// If you want to use custom column names in database store you could
	// set them in this configuration
	'keyColumn' => env('SETTINGS_KEY_COLUMN', 'key'),
	'valueColumn' => env('SETTINGS_VALUE_COLUMN', 'value')
];
