<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopupConfirmAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('topup_confirm_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('request_id', 64)->unique();
            $table->string('layer', 32)->default('frontend');
            $table->string('provider', 32);
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('username', 100)->nullable();
            $table->string('mobile', 32)->index();
            $table->string('amount', 32)->nullable();
            $table->string('status', 32)->index();
            $table->string('lock_key', 191)->nullable()->index();
            $table->string('internal_reference', 100)->nullable()->index();
            $table->string('provider_reference', 191)->nullable()->index();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->unsignedInteger('transaction_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 191)->nullable();
            $table->string('origin', 191)->nullable();
            $table->text('error_message')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['mobile', 'status']);
            $table->index(['provider', 'mobile']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('topup_confirm_attempts');
    }
}
