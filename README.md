# Laravel Settings

[![Build Status](https://api.travis-ci.org/anlutro/laravel-settings.svg?branch=master)](https://travis-ci.org/anlutro/laravel-settings)
[![Latest Stable Version](https://poser.pugx.org/anlutro/l4-settings/v/stable.svg)](https://github.com/anlutro/laravel-settings/releases)
[![License](https://poser.pugx.org/anlutro/l4-settings/license.svg)](http://opensource.org/licenses/MIT)

Persistent, application-wide settings for Laravel. 

Despite the package name, this package should work with Laravel 4, 5, 6, 7 and 8 (though some versions are not automatically tested).

## Common problems

- Class not found errors: https://github.com/anlutro/laravel-settings/issues/38

## Installation - Laravel >= 5.5

1. `composer require anlutro/l4-settings`
2. Publish the config file by running `php artisan vendor:publish --provider="anlutro\LaravelSettings\ServiceProvider" --tag="config"`. The config file will give you control over which storage engine to use as well as some storage-specific settings.

## Installation - Laravel < 5.5

1. `composer require anlutro/l4-settings`
2. Add `anlutro\LaravelSettings\ServiceProvider` to the array of providers in `config/app.php`.
3. Publish the config file by running `php artisan config:publish anlutro/l4-settings` (Laravel 4.x) or `php artisan vendor:publish` (Laravel 5.x). The config file will give you control over which storage engine to use as well as some storage-specific settings.
4. Optional: add `'Setting' => 'anlutro\LaravelSettings\Facade'` to the array of aliases in `config/app.php`.

## Usage

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

Call `Setting::save()` explicitly to save changes made.

You could also use the `setting()` helper:

```php
// Get the store instance
setting();

// Get values
setting('foo');
setting('foo.bar');
setting('foo', 'default value');
setting()->get('foo');

// Set values
setting(['foo' => 'bar']);
setting(['foo.bar' => 'baz']);
setting()->set('foo', 'bar');

// Method chaining
setting(['foo' => 'bar'])->save();
```


### Auto-saving

In Laravel 4.x, the library makes sure to auto-save every time the application shuts down if anything has been changed.

In Laravel 5.x, if you add the middleware `anlutro\LaravelSettings\SaveMiddleware` to your `middleware` list in `app\Http\Kernel.php`, settings will be saved automatically at the end of all HTTP requests, but you'll still need to call `Setting::save()` explicitly in console commands, queue workers etc.


### Store cache

When reading from the store, you can enable the cache.

You can also configure flushing of the cache when writing and configure time to live.

Reading will come from the store, and then from the cache, this can reduce load on the store.

```php
// Cache usage configurations.
'enableCache' => false,
'forgetCacheByWrite' => true,
'cacheTtl' => 15,
```

### JSON storage

You can modify the path used on run-time using `Setting::setPath($path)`.


### Database storage

#### Using Migration File

If you use the database store you need to run `php artisan migrate --package=anlutro/l4-settings` (Laravel 4.x) or `php artisan vendor:publish --provider="anlutro\LaravelSettings\ServiceProvider" --tag="migrations" && php artisan migrate` (Laravel 5.x) to generate the table.

#### Example

For example, if you want to store settings for multiple users/clients in the same database you can do so by specifying extra columns:

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

### Custom stores

This package uses the Laravel `Manager` class under the hood, so it's easy to add your own custom session store driver if you want to store in some other way. All you need to do is extend the abstract `SettingStore` class, implement the abstract methods and call `Setting::extend`.

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
