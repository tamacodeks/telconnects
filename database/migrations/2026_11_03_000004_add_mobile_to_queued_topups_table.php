<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMobileToQueuedTopupsTable extends Migration
{
    public function up()
    {
        Schema::table('queued_topups', function (Blueprint $table) {
            $table->string('mobile', 32)->nullable()->index();
        });

        DB::table('queued_topups')->select('id', 'payload')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $payload = json_decode($row->payload, true);
                if (!is_array($payload)) {
                    continue;
                }
                foreach (['mobile_number', 'AccountNumber', 'recipientNumber', 'accountNumber'] as $key) {
                    if (!empty($payload[$key]) && is_scalar($payload[$key])) {
                        DB::table('queued_topups')->where('id', $row->id)->update([
                            'mobile' => substr(preg_replace('/\D+/', '', (string) $payload[$key]), 0, 32),
                        ]);
                        break;
                    }
                }
            }
        });
    }

    public function down()
    {
        Schema::table('queued_topups', function (Blueprint $table) {
            $table->dropColumn('mobile');
        });
    }
}
