<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSectionToMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menus', function (Blueprint $table) {
            if (!Schema::hasColumn('menus', 'section')) {
                $table->string('section', 30)->nullable()->after('position')->index();
            }
        });

        if (Schema::hasColumn('menus', 'section')) {
            DB::table('menus')
                ->select('id', 'url', 'section')
                ->orderBy('id', 'asc')
                ->get()
                ->each(function ($menu) {
                    $section = strtolower(trim((string) $menu->section));
                    if (in_array($section, ['account', 'services', 'history', 'settings'], true)) {
                        return;
                    }

                    DB::table('menus')
                        ->where('id', (int) $menu->id)
                        ->update(['section' => $this->sectionForPath($menu->url)]);
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'section')) {
                $table->dropColumn('section');
            }
        });
    }

    protected function sectionForPath($url)
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : (string) $url;
        $path = strtolower(trim($path, '/'));

        $exactSections = [
            'dashboard' => 'account',
            'dashboard-v2' => 'account',
            'profile' => 'account',
            'invoices' => 'account',
            'payment' => 'account',
            'payments' => 'account',
            'balance' => 'account',
            'orders' => 'history',
            'my/orders' => 'history',
            'transactions' => 'history',
            'my/transactions' => 'history',
            'failed_transaction' => 'history',
            'cc/report/usage-history' => 'history',
            'cc/report/pins' => 'history',
            'cc-pin-history' => 'history',
            'menus' => 'settings',
            'menus-v2' => 'settings',
            'app-settings' => 'settings',
            'app-settings-v2' => 'settings',
            'service-config' => 'settings',
            'support' => 'settings',
            'help' => 'settings',
            'logout' => 'settings',
        ];

        if (isset($exactSections[$path])) {
            return $exactSections[$path];
        }

        $prefixSections = [
            'dashboard/' => 'account',
            'profile/' => 'account',
            'invoices/' => 'account',
            'payment/' => 'account',
            'payments/' => 'account',
            'orders/' => 'history',
            'my/orders/' => 'history',
            'transactions/' => 'history',
            'my/transactions/' => 'history',
            'failed_transaction/' => 'history',
            'cc/report/' => 'history',
            'cc-pin-history/' => 'history',
            'menus/' => 'settings',
            'menus-v2/' => 'settings',
            'app-settings/' => 'settings',
            'app-settings-v2/' => 'settings',
            'service-config/' => 'settings',
        ];

        foreach ($prefixSections as $prefix => $section) {
            if (strpos($path . '/', $prefix) === 0) {
                return $section;
            }
        }

        return 'services';
    }
}
