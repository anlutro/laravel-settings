# Laravel Settings

[![Build Status](https://travis-ci.org/anlutro/laravel-settings.png?branch=master)](https://travis-ci.org/anlutro/laravel-settings)
[![Latest Stable Version](https://poser.pugx.org/anlutro/l4-settings/v/stable.svg)](https://github.com/anlutro/laravel-settings/releases)
[![License](https://poser.pugx.org/anlutro/l4-settings/license.svg)](http://opensource.org/licenses/MIT)

Persistant settings for Laravel.

### Installation

1. `composer require anlutro/l4-settings`

2. Add `anlutro\LaravelSettings\ServiceProvider` to the array of providers in `app/config/app.php`.

3. Publish the config file by running `php artisan config:publish anlutro/l4-settings` (Laravel 4.x) or `php artisan vendor:publish` (Laravel 5). The config file will give you control over which storage engine to use as well as some storage-specific settings.

Optional: add `'Setting' => 'anlutro\LaravelSettings\Facade'` to the array of aliases in the same file.

### Usage

You can either access the setting store via its facade or inject it by type-hinting towards the abstract class `anlutro\LaravelSettings\SettingStore`.

```php
<?php
Setting::set('foo', 'bar');
Setting::get('foo', 'default value');
Setting::get('nested.element');
Setting::forget('foo');
$settings = Setting::all();
?>
```

You can call `Setting::save()` explicitly to save changes made. In Laravel 4.x, the library makes sure to auto-save every time the application shuts down if anything has been changed.

The package comes with two default setting stores: database and JSON.

#### Database

If you use the database store you need to create the table yourself. It needs two columns - key and value, both should be varchars - how long depends on the amount of data you plan to store there.

If you want to store settings for multiple users/clients in the same database you can do so by specifying extra columns:

```php
<?php
Setting::setExtraColumns(array(
	'user_id' => Auth::user()->id
));
?>
```

`where user_id = x` will now be added to the database query when settings are retrieved, and when new settings are saved, the `user_id` will be populated.

If you need more fine-tuned control over which data gets queried, you can use the `setConstraint` method which takes a closure with two arguments:

- `$query` is the query builder instance
- `$insert` is a boolean telling you whether the query is an insert or not. If it is an insert, you usually don't need to do anything to `$query`.

```php
<?php
Setting::setConstraint(function($query, $insert) {
	if ($insert) return;
	$query->where(/* ... */);
});
?>
```

#### JSON

You can modify the path used on run-time using `Setting::setPath($path)`.

#### Extension

This package uses the Laravel 4 Manager class under the hood, so it's easy to add your own custom session store driver if you want to store in some other way. All you need to do is extend the abstract `SettingStore` class, implement the abstract methods and call `Setting::extend`.

```php
<?php
class MyStore extends anlutro\LaravelSettings\SettingStore {
	// ...
}
Setting::extend('mystore', function($app) {
	return $app->make('MyStore');
});
?>
```

## Contact

Open an issue on GitHub if you have any problems or suggestions.

## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
