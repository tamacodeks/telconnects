<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNightMaintenanceSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('night_maintenance_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->boolean('enabled')->default(true);
            $table->time('allowed_start')->default('07:30:00');
            $table->time('allowed_end')->default('00:00:00');
            $table->string('timezone', 64)->default('Europe/Paris');
            $table->timestamps();
            $table->index('enabled');
        });
    }

    public function down()
    {
        Schema::dropIfExists('night_maintenance_settings');
    }
}
