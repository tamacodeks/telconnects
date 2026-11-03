<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopupProcessingSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('topup_processing_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('mode', 20)->default('synchronous');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('mode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('topup_processing_settings');
    }
}
