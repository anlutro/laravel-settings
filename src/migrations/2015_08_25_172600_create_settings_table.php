<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

    public function __construct() {
        $this->tablename = config('settings.table');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::connection(config('settings.connection'))->create($this->tablename, function(Blueprint $table) {
            $table->increments('id');
            $table->string('key')->index();
            $table->text('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::connection(config('settings.connection'))->drop($this->tablename);
    }

}
