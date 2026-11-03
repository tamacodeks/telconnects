<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueuedTopupsTable extends Migration
{
    public function up()
    {
        Schema::create('queued_topups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_id', 64)->unique();
            $table->string('provider', 20)->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('order_id')->index();
            $table->unsignedInteger('track_order_id')->nullable()->index();
            $table->string('local_reference', 100)->index();
            $table->string('provider_reference', 191)->nullable()->index();
            $table->string('status', 20)->default('pending')->index();
            $table->longText('payload');
            $table->longText('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('dispatched_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('queued_topups');
    }
}
