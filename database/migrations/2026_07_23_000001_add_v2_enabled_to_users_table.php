<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddV2EnabledToUsersTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('users', 'v2_enabled')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tt_v2_refresh_popup_seen')) {
                $table->boolean('v2_enabled')->default(0)->after('tt_v2_refresh_popup_seen');
            } else {
                $table->boolean('v2_enabled')->default(0)->after('can_process_order');
            }
        });

        DB::table('users')
            ->whereIn('id', [1, 7, 138])
            ->update(['v2_enabled' => 1]);
    }

    public function down()
    {
        if (!Schema::hasColumn('users', 'v2_enabled')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('v2_enabled');
        });
    }
}
