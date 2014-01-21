<?php
/**
 * Laravel 4 - Persistant Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	/**
	 * This provider is deferred and should be lazy loaded.
	 *
	 * @var boolean
	 */
	protected $defer = true;

	/**
	 * Register IoC bindings.
	 */
	public function register()
	{
		/**
		 * Bind the manager as a singleton on the container.
		 */
		$this->app->bindShared('anlutro\LaravelSettings\SettingsManager', function($app) {
			/**
			 * When the class has been resolved once, make sure that settings
			 * are saved when the application shuts down.
			 */
			$app->shutdown(function($app) {
				$app->make('anlutro\LaravelSettings\SettingStore')->save();
			});
			
			/**
			 * Construct the actual manager.
			 */
			return new SettingsManager($this->app);
		});

		/**
		 * Provide a shortcut to the SettingStore for injecting into classes.
		 */
		$this->app->bind('anlutro\LaravelSettings\SettingStore', function($app) {
			return $app->make('anlutro\LaravelSettings\SettingsManager')->driver();
		});
	}

	/**
	 * Boot the package.
	 */
	public function boot()
	{
		$this->app['config']->package(
			'anlutro/l4-settings', __DIR__ . '/config', 'anlutro/l4-settings'
		);
	}

	/**
	 * Which IoC bindings the provider provides.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'anlutro\LaravelSettings\SettingsManager',
			'anlutro\LaravelSettings\SettingStore',
		);
	}
}
