<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRetailerServiceReportMenu extends Migration
{
    public function up()
    {
        $reportTranslations = json_encode([
            'title' => [
                'en' => 'Report',
                'fr' => 'Rapport',
                'pt' => 'Relatorio',
            ],
        ]);
        $serviceReportTranslations = json_encode([
            'title' => [
                'en' => 'Service Report',
                'fr' => 'Rapport services',
                'pt' => 'Relatorio de Servicos',
            ],
        ]);

        foreach ([1, 2, 3] as $groupId) {
            $parentId = DB::table('menus')
                ->where('group_id', $groupId)
                ->where('parent_id', 0)
                ->where('name', 'Report')
                ->value('id');

            if (!$parentId) {
                $parentId = DB::table('menus')->insertGetId([
                    'parent_id' => 0,
                    'group_id' => $groupId,
                    'url' => 'reports',
                    'name' => 'Report',
                    'ordering' => $this->parentOrdering($groupId),
                    'position' => 'sidebar',
                    'icon' => 'fa fa-chart-bar',
                    'status' => 1,
                    'trans_lang' => $reportTranslations,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 1,
                ]);
            } else {
                DB::table('menus')->where('id', $parentId)->update([
                    'trans_lang' => $reportTranslations,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => 1,
                ]);
            }

            $exists = DB::table('menus')
                ->where('group_id', $groupId)
                ->where('url', 'reports/retailer-services')
                ->exists();

            if (!$exists) {
                DB::table('menus')->insert([
                    'parent_id' => $parentId,
                    'group_id' => $groupId,
                    'url' => 'reports/retailer-services',
                    'name' => 'Service Report',
                    'ordering' => 0,
                    'position' => 'sidebar',
                    'icon' => 'fa fa-chart-pie',
                    'status' => 1,
                    'trans_lang' => $serviceReportTranslations,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 1,
                ]);
            } else {
                DB::table('menus')
                    ->where('group_id', $groupId)
                    ->where('url', 'reports/retailer-services')
                    ->update([
                        'name' => 'Service Report',
                        'trans_lang' => $serviceReportTranslations,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => 1,
                    ]);
            }
        }
    }

    public function down()
    {
        DB::table('menus')
            ->whereIn('group_id', [1, 2, 3])
            ->where('url', 'reports/retailer-services')
            ->delete();

        foreach ([1, 2, 3] as $groupId) {
            $parentId = DB::table('menus')
                ->where('group_id', $groupId)
                ->where('parent_id', 0)
                ->where('name', 'Report')
                ->value('id');

            if ($parentId && !DB::table('menus')->where('parent_id', $parentId)->exists()) {
                DB::table('menus')->where('id', $parentId)->delete();
            }
        }
    }

    private function parentOrdering($groupId)
    {
        if ((int) $groupId === 1) {
            return 7;
        }

        if ((int) $groupId === 2) {
            return 9;
        }

        return 12;
    }
}
