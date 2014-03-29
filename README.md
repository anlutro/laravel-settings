# Laravel Settings [![Build Status](https://travis-ci.org/anlutro/laravel-settings.png?branch=master)](https://travis-ci.org/anlutro/laravel-settings) [![Latest Version](http://img.shields.io/github/tag/anlutro/laravel-settings.svg)](https://github.com/anlutro/laravel-settings/releases)

Persistant settings for Laravel 4.

### Installation

`composer require anlutro/l4-settings` - pick the latest version from Packagist or the list of tags on Github.

Add `anlutro\LaravelSettings\ServiceProvider` to the array of providers in `app/config/app.php`.

Optional: add `'Setting' => 'anlutro\LaravelSettings\Facade'` to the array of aliases in the same file.

Publish the config file by running `php artisan config:publish anlutro/l4-settings`.

### Usage

You can either access the setting store via its facade or inject it by type-hinting towards the abstract class `anlutro\LaravelSettings\SettingStore`.

```php
<?php
Setting::set('foo', 'bar');
Setting::get('foo');
Setting::get('nested.element');
Setting::forget('foo');
$settings = Setting::all();
?>
```

You can call `Setting::save()` explicitly to save changes made, but the library makes sure to auto-save every time the application shuts down if anything has been changed.

The package comes with two default setting stores: database and JSON.

#### Database

If you use the database store you need to create the table yourself. It needs two columns - key and value, both should be varchars - how long depends on the amount of data you plan to store there.

If you want to store settings for multiple users/clients in the same database you can do so by specifying extra columns:

```php
<?php
Setting::setExtraColumns(array('user_id' => Auth::user()->id));
?>
```

`where user_id = x` will now be added to the database query when settings are retrieved, and when new settings are saved, the `user_id` will be populated.

You can also use the `setConstraint` method which takes a closure with `$query` as the only argument - this closure will be ran on every query.

#### JSON

You can modify the path used on run-time using `Setting::setPath($path)`.

#### Extending

This package uses the Laravel 4 Manager class under the hood, so it's easy to add your own custom session store driver if you want to store in some other way.

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