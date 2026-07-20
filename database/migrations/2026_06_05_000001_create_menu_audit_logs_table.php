<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id', false)->unsigned()->nullable();
            $table->string('action', 80);
            $table->string('module', 80);
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index(['module', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_audit_logs');
    }
}
