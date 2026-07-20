<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginSecurityFieldsToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'login_attempts')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('login_attempts')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'otp_attempts')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('otp_attempts')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'otp_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('otp_hash')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'otp_expires_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('otp_expires_at')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'last_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_session_id')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'ip_address2')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('ip_address2')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'verify_ip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('verify_ip')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'enable_ip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('enable_ip')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'enable_2fa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('enable_2fa')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'verify_2fa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('verify_2fa')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('secret')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'last_activity')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('last_activity')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'login_attempts')) {
                $table->dropColumn('login_attempts');
            }
            if (Schema::hasColumn('users', 'otp_attempts')) {
                $table->dropColumn('otp_attempts');
            }
            if (Schema::hasColumn('users', 'otp_hash')) {
                $table->dropColumn('otp_hash');
            }
            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
            if (Schema::hasColumn('users', 'last_session_id')) {
                $table->dropColumn('last_session_id');
            }
            if (Schema::hasColumn('users', 'ip_address2')) {
                $table->dropColumn('ip_address2');
            }
            if (Schema::hasColumn('users', 'verify_ip')) {
                $table->dropColumn('verify_ip');
            }
            if (Schema::hasColumn('users', 'enable_ip')) {
                $table->dropColumn('enable_ip');
            }
            if (Schema::hasColumn('users', 'enable_2fa')) {
                $table->dropColumn('enable_2fa');
            }
            if (Schema::hasColumn('users', 'verify_2fa')) {
                $table->dropColumn('verify_2fa');
            }
            if (Schema::hasColumn('users', 'secret')) {
                $table->dropColumn('secret');
            }
            if (Schema::hasColumn('users', 'last_activity')) {
                $table->dropColumn('last_activity');
            }
        });
    }
}
