<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\App\UserGroupController as LegacyUserGroupController;
use App\Support\V2Access;

class UserGroupController extends LegacyUserGroupController
{
    public function index()
    {
        if (!V2Access::userCanUseV2(auth()->user())) {
            return parent::index();
        }

        $text = trans('v2_users');
        $tabs = $text['tabs'];
        $labels = $text['labels'];

        $config = [
            'type' => 'user-groups',
            'title' => $text['pages']['user_groups']['page_title'],
            'showHeader' => false,
            'panelTitle' => $text['pages']['user_groups']['panel_title'],
            'panelSubtitle' => $text['pages']['user_groups']['panel_subtitle'],
            'fetchUrl' => url('fetch/user-groups'),
            'legacyUrl' => url('user-groups'),
            'v2Url' => route('user-groups.v2'),
            'addUrl' => secure_url('user-group/update'),
            'csrfToken' => csrf_token(),
            'pageLength' => defined('PER_PAGE') ? PER_PAGE : 25,
            'order' => [[1, 'asc']],
            'labels' => [
                'processing' => $labels['loading'],
                'records10' => $labels['records_10'],
                'records25' => $labels['records_25'],
                'records50' => $labels['records_50'],
                'showAll' => $labels['show_all'],
                'downloadExcel' => $labels['download_excel'],
                'export' => $labels['export'],
                'refresh' => $labels['refresh'],
                'close' => $labels['close'],
                'action' => $labels['action'],
                'actions' => $labels['actions'],
                'userPages' => $labels['user_pages'],
                'rows' => $labels['rows'],
                'rowsPerPage' => $labels['rows_per_page'],
                'addUserGroup' => $labels['add_user_group'],
                'active' => $text['statuses']['active'],
                'inactive' => $text['statuses']['inactive'],
                'clicked' => $text['statuses']['clicked'],
                'notClicked' => $text['statuses']['not_clicked'],
                'unknown' => $text['statuses']['unknown'],
                'showingEntries' => $labels['showing_entries'],
                'showingEmpty' => $labels['showing_empty'],
                'filteredEntries' => $labels['filtered_entries'],
                'emptyTable' => $labels['empty_table'],
                'zeroRecords' => $labels['zero_records'],
                'previous' => $labels['previous'],
                'next' => $labels['next'],
                'loading' => $labels['loading'],
            ],
            'quickLinks' => [
                ['type' => 'users', 'label' => $tabs['users'], 'url' => route('users.v2'), 'icon' => 'fa fa-users', 'active' => false],
                ['type' => 'user-groups', 'label' => $tabs['groups'], 'url' => route('user-groups.v2'), 'icon' => 'fa fa-object-group', 'active' => true],
                ['type' => 'user-info', 'label' => $tabs['user_info'], 'url' => route('user-info.v2'), 'icon' => 'fa fa-id-card', 'active' => false],
                ['type' => 'all-users', 'label' => $tabs['all_users'], 'url' => route('all-users.v2'), 'icon' => 'fa fa-address-book', 'active' => false],
                ['type' => 'refresh-popup', 'label' => $tabs['refresh_popup'], 'url' => route('refresh-popup-seen-users.v2'), 'icon' => 'fa fa-bell-slash', 'active' => false],
            ],
            'detailFields' => [
                ['label' => $text['columns']['level_access'], 'key' => 'level_access'],
                ['label' => $text['columns']['created_at'], 'key' => 'created_at'],
                ['label' => $text['columns']['updated'], 'key' => 'updated_at'],
            ],
            'headings' => [
                '',
                $text['columns']['number'],
                $text['columns']['name'],
                $text['columns']['description'],
                $text['columns']['status'],
                $text['columns']['level_access'],
                $text['columns']['created_at'],
                $text['columns']['updated'],
                $text['columns']['actions'],
            ],
            'stats' => [],
        ];

        return view('v2.app.users.index', [
            'page_title' => $text['pages']['user_groups']['page_title'],
            'usersV2Type' => 'user-groups',
            'usersV2Config' => $config,
        ]);
    }
}
