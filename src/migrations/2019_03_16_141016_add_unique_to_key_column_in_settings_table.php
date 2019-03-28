<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

class AddUniqueToKeyColumnInSettingsTable extends Migration
{
    public function __construct()
    {
        if (version_compare(Application::VERSION, '5.0', '>=')) {
            $this->tablename = Config::get('settings.table');
            $this->keyColumn = Config::get('settings.keyColumn');
            $this->valueColumn = Config::get('settings.valueColumn');
        } else {
            $this->tablename = Config::get('anlutro/l4-settings::table');
            $this->keyColumn = Config::get('anlutro/l4-settings::keyColumn');
            $this->valueColumn = Config::get('anlutro/l4-settings::valueColumn');
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropIndex($this->tablename . '_' . $this->keyColumn . '_index');
            $table->unique($this->keyColumn, 'settings_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->Index($this->keyColumn);
        });
    }
}
