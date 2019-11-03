<?php
/**
 * Laravel 4 - Persistent Settings
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-settings
 */

namespace anlutro\LaravelSettings;

use Illuminate\Foundation\Application;
use Blade;

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
		$method = version_compare(Application::VERSION, '5.2', '>=') ? 'singleton' : 'bindShared';

		// Bind the manager as a singleton on the container.
		$this->app->$method('anlutro\LaravelSettings\SettingsManager', function($app) {
			// When the class has been resolved once, make sure that settings
			// are saved when the application shuts down.
			if (version_compare(Application::VERSION, '5.0', '<')) {
				$app->shutdown(function($app) {
					$app->make('anlutro\LaravelSettings\SettingStore')->save();
				});
			}
			
			/**
			 * Construct the actual manager.
			 */
			return new SettingsManager($app);
		});

		// Provide a shortcut to the SettingStore for injecting into classes.
		$this->app->bind('anlutro\LaravelSettings\SettingStore', function($app) {
			return $app->make('anlutro\LaravelSettings\SettingsManager')->driver();
		});

		$this->app->alias('anlutro\LaravelSettings\SettingStore', 'setting');

		if (version_compare(Application::VERSION, '5.0', '>=')) {
			$this->mergeConfigFrom(__DIR__ . '/config/config.php', 'settings');
		}
	}

	/**
	 * Boot the package.
	 */
	public function boot()
	{
		if (version_compare(Application::VERSION, '5.0', '>=')) {
			$this->publishes([
				__DIR__.'/config/config.php' => config_path('settings.php')
			], 'config');
			$this->publishes([
				__DIR__.'/migrations/2015_08_25_172600_create_settings_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_settings_table.php')
			], 'migrations');

			//Override configs
			$override = config('settings.override', []);
			
			//Compatibility with Laravel >= 5.8 and < 5.7
			if(function_exists('array_dot')){
				$override_values = array_dot($override);
			}else{
				$override_values = \Illuminate\Support\Arr::dot($override);
			}

			foreach ($override_values as $config_key => $setting_key) {
				$config_key = $config_key ?: $setting_key;
				try {
					if (! is_null($value = setting($setting_key))) {
						config([$config_key => $value]);
					}
				} catch (\Exception $e) {
					continue;
				}
			}

			// Register blade directive
			Blade::directive('setting', function ($expression) {
				return "<?php echo setting($expression); ?>";
			});
		} else {
			$this->app['config']->package(
				'anlutro/l4-settings', __DIR__ . '/config', 'anlutro/l4-settings'
			);
		}
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
			'setting'
		);
	}
}
