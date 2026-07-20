<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiDeviceLoginFieldsToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'max_active_sessions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_active_sessions')->default(1)->after('last_session_id');
            });
        }

        if (! Schema::hasColumn('users', 'active_session_ids')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('active_session_ids')->nullable()->after('max_active_sessions');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'active_session_ids')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('active_session_ids');
            });
        }

        if (Schema::hasColumn('users', 'max_active_sessions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('max_active_sessions');
            });
        }
    }
}
