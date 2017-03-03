<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

class CreateSettingsTable extends Migration
{
	public function __construct()
	{
		if (version_compare(Application::VERSION, '5.0', '>=')) {
			$this->tablename = Config::get('settings.table');
			$this->key_column_name = Config::get('settings.key_column_name');
            $this->value_column_name = Config::get('settings.value_column_name');
		} else {
			$this->tablename = Config::get('anlutro/l4-settings::table');
			$this->key_column_name = Config::get('anlutro/l4-settings::key_column_name');
            $this->value_column_name = Config::get('anlutro/l4-settings::value_column_name');
		}
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create($this->tablename, function(Blueprint $table)
		{
			$table->increments('id');
			$table->string($this->key_column_name)->index();
			$table->text($this->value_column_name);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop($this->tablename);
	}
}
