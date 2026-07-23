<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\App\UserController as LegacyUserController;
use App\Support\V2Access;
use App\User;
use Illuminate\Http\Request;

class UserController extends LegacyUserController
{
    public function index(Request $request)
    {
        if (!$this->shouldRenderV2()) {
            return parent::index($request);
        }

        return $this->renderUserPage('users', [
            'page_title' => trans('v2_users.pages.users.page_title'),
        ]);
    }

    public function userInfo(Request $request)
    {
        if (!$this->shouldRenderV2()) {
            return parent::user_info($request);
        }

        return $this->renderUserPage('user-info', [
            'page_title' => trans('v2_users.pages.user_info.page_title'),
        ]);
    }

    public function allUsers(Request $request)
    {
        if (!$this->shouldRenderV2()) {
            return parent::all_users($request);
        }

        return $this->renderUserPage('all-users', [
            'page_title' => trans('v2_users.pages.all_users.page_title'),
            'user_list' => User::select('id', 'username')
                ->where('group_id', 3)
                ->orderBy('username')
                ->get(),
        ]);
    }

    public function refreshPopupSeenUsers(Request $request)
    {
        if (!$this->shouldRenderV2()) {
            return parent::refresh_popup_seen_users($request);
        }

        if (!in_array(auth()->user()->group_id, [1, 2])) {
            return parent::refresh_popup_seen_users($request);
        }

        return $this->renderUserPage('refresh-popup', [
            'page_title' => trans('v2_users.pages.refresh_popup.page_title'),
        ]);
    }

    protected function shouldRenderV2()
    {
        return V2Access::userCanUseV2(auth()->user());
    }

    protected function renderUserPage($type, array $data = [])
    {
        $config = $this->pageConfig($type);
        $config['showHeader'] = false;
        $config['stats'] = [];
        $config['panelSubtitle'] = '';

        return view('v2.app.users.index', array_merge($data, [
            'usersV2Type' => $type,
            'usersV2Config' => $config,
        ]));
    }

    protected function pageConfig($type)
    {
        $text = trans('v2_users');
        $labels = [
            'processing' => $text['labels']['loading'],
            'records10' => $text['labels']['records_10'],
            'records25' => $text['labels']['records_25'],
            'records50' => $text['labels']['records_50'],
            'showAll' => $text['labels']['show_all'],
            'downloadExcel' => $text['labels']['download_excel'],
            'export' => $text['labels']['export'],
            'refresh' => $text['labels']['refresh'],
            'close' => $text['labels']['close'],
            'action' => $text['labels']['action'],
            'actions' => $text['labels']['actions'],
            'userPages' => $text['labels']['user_pages'],
            'search' => $text['labels']['search'],
            'reset' => $text['labels']['reset'],
            'runResetCorrections' => $text['labels']['run_reset_corrections'],
            'resetCorrections' => $text['labels']['reset_corrections'],
            'resetCorrectionsTitle' => $text['labels']['reset_corrections_title'],
            'resetCorrectionsPrompt' => $text['labels']['reset_corrections_prompt'],
            'resetCorrectionsFailed' => $text['labels']['reset_corrections_failed'],
            'v2AccessFailed' => $text['labels']['v2_access_failed'],
            'updated' => $text['labels']['updated'],
            'rows' => $text['labels']['rows'],
            'rowsPerPage' => $text['labels']['rows_per_page'],
            'manager' => $text['labels']['manager'],
            'selectManager' => $text['labels']['select_manager'],
            'status' => $text['labels']['status'],
            'selectStatus' => $text['labels']['select_status'],
            'date' => $text['labels']['date'],
            'from' => $text['labels']['from'],
            'to' => $text['labels']['to'],
            'range' => $text['labels']['range'],
            'totalUpdated' => $text['labels']['total_updated'],
            'selectTimeRange' => $text['labels']['select_time_range'],
            'success' => $text['labels']['success'],
            'information' => $text['labels']['information'],
            'addUser' => $text['labels']['add_user'],
            'addUserGroup' => $text['labels']['add_user_group'],
            'active' => $text['statuses']['active'],
            'inactive' => $text['statuses']['inactive'],
            'clicked' => $text['statuses']['clicked'],
            'notClicked' => $text['statuses']['not_clicked'],
            'unknown' => $text['statuses']['unknown'],
            'authIpOtp' => $text['auth_methods']['ip_otp'],
            'authIpOtpTitle' => $text['auth_methods']['ip_otp_title'],
            'authTotp' => $text['auth_methods']['totp'],
            'authTotpTitle' => $text['auth_methods']['totp_title'],
            'authNone' => $text['auth_methods']['none'],
            'authNoneTitle' => $text['auth_methods']['none_title'],
            'showingEntries' => $text['labels']['showing_entries'],
            'showingEmpty' => $text['labels']['showing_empty'],
            'filteredEntries' => $text['labels']['filtered_entries'],
            'emptyTable' => $text['labels']['empty_table'],
            'zeroRecords' => $text['labels']['zero_records'],
            'previous' => $text['labels']['previous'],
            'next' => $text['labels']['next'],
            'loading' => $text['labels']['loading'],
        ];

        $pages = [
            'users' => [
                'type' => 'users',
                'title' => $text['pages']['users']['page_title'],
                'panelTitle' => $text['pages']['users']['panel_title'],
                'panelSubtitle' => $text['pages']['users']['panel_subtitle'],
                'fetchUrl' => url('fetch/users'),
                'legacyUrl' => url('users'),
                'v2Url' => route('users.v2'),
                'addUrl' => secure_url('user/update'),
                'resetUrl' => secure_url('users/reset-transaction-corrections'),
                'runResetUrl' => secure_url('users/run-reset-corrections-today'),
                'pageLength' => -1,
                'order' => [[1, 'asc']],
                'canManageCorrections' => in_array(auth()->user()->group_id, [1, 2]),
                'detailFields' => [
                    ['label' => $text['columns']['customer_id'], 'key' => 'cust_id'],
                    ['label' => $text['columns']['representative'], 'key' => 'representative'],
                    ['label' => $text['columns']['mobile'], 'key' => 'mobile'],
                    ['label' => $text['columns']['email'], 'key' => 'email'],
                    ['label' => $text['columns']['credit_limit'], 'key' => 'credit_limit'],
                    ['label' => $text['columns']['created_at'], 'key' => 'created_at'],
                    ['label' => $text['columns']['updated'], 'key' => 'updated_at'],
                ],
                'headings' => [
                    '',
                    $text['columns']['user'],
                    $text['columns']['role'],
                    $text['columns']['auth'],
                    $text['columns']['v2_access'],
                    $text['columns']['balance_credit'],
                    $text['columns']['last_seen'],
                    $text['columns']['actions'],
                ],
            ],
            'user-info' => [
                'type' => 'user-info',
                'title' => $text['pages']['user_info']['page_title'],
                'panelTitle' => $text['pages']['user_info']['panel_title'],
                'panelSubtitle' => $text['pages']['user_info']['panel_subtitle'],
                'fetchUrl' => url('fetch/users_info'),
                'legacyUrl' => url('user_info'),
                'v2Url' => route('user-info.v2'),
                'pageLength' => -1,
                'order' => [[1, 'asc']],
                'detailFields' => [
                    ['label' => $text['columns']['user'], 'key' => 'username'],
                    ['label' => $text['columns']['account_type'], 'key' => 'name'],
                    ['label' => $text['columns']['ip_address'], 'key' => 'ip_address'],
                    ['label' => $text['columns']['mobile'], 'key' => 'mobile'],
                    ['label' => $text['columns']['email'], 'key' => 'email'],
                ],
                'headings' => [
                    $text['columns']['number'],
                    $text['columns']['user'],
                    $text['columns']['account_type'],
                    $text['columns']['ip_address'],
                    $text['columns']['email'],
                ],
            ],
            'all-users' => [
                'type' => 'all-users',
                'title' => $text['pages']['all_users']['page_title'],
                'panelTitle' => $text['pages']['all_users']['panel_title'],
                'panelSubtitle' => $text['pages']['all_users']['panel_subtitle'],
                'fetchUrl' => url('fetch_all_users'),
                'legacyUrl' => url('all_users'),
                'v2Url' => route('all-users.v2'),
                'pageLength' => -1,
                'order' => [[2, 'desc']],
                'hasFilters' => true,
                'detailFields' => [
                    ['label' => $text['columns']['representative'], 'key' => 'representative'],
                    ['label' => $text['columns']['last_seen'], 'key' => 'last_activity'],
                    ['label' => $text['columns']['balance'], 'key' => 'balance'],
                    ['label' => $text['columns']['credit_limit'], 'key' => 'credit_limit'],
                ],
                'headings' => [
                    '',
                    $text['columns']['number'],
                    $text['columns']['user'],
                    $text['columns']['account_type'],
                    $text['columns']['representative'],
                    $text['columns']['last_seen'],
                    $text['columns']['status'],
                    $text['columns']['balance'],
                    $text['columns']['credit_limit'],
                ],
            ],
            'refresh-popup' => [
                'type' => 'refresh-popup',
                'title' => $text['pages']['refresh_popup']['page_title'],
                'panelTitle' => $text['pages']['refresh_popup']['panel_title'],
                'panelSubtitle' => $text['pages']['refresh_popup']['panel_subtitle'],
                'fetchUrl' => url('fetch_refresh_popup_seen_users'),
                'legacyUrl' => url('refresh_popup_seen_users'),
                'v2Url' => route('refresh-popup-seen-users.v2'),
                'pageLength' => 25,
                'order' => [[1, 'asc'], [3, 'asc']],
                'detailFields' => [
                    ['label' => $text['columns']['parent_name'], 'key' => 'parent_name'],
                    ['label' => $text['columns']['parent_status'], 'key' => 'parent_status'],
                    ['label' => $text['columns']['user'], 'key' => 'username'],
                    ['label' => $text['columns']['popup_status'], 'key' => 'status'],
                    ['label' => $text['columns']['last_seen'], 'key' => 'last_activity'],
                ],
                'headings' => [
                    $text['columns']['number'],
                    $text['columns']['parent_name'],
                    $text['columns']['parent_status'],
                    $text['columns']['user'],
                    $text['columns']['popup_status'],
                    $text['columns']['last_seen'],
                ],
            ],
        ];

        return array_merge($pages[$type], [
            'csrfToken' => csrf_token(),
            'labels' => $labels,
            'quickLinks' => $this->quickLinks($type),
        ]);
    }

    protected function quickLinks($activeType)
    {
        $tabs = trans('v2_users.tabs');

        return [
            ['type' => 'users', 'label' => $tabs['users'], 'url' => route('users.v2'), 'icon' => 'fa fa-users', 'active' => $activeType === 'users'],
            ['type' => 'user-groups', 'label' => $tabs['groups'], 'url' => route('user-groups.v2'), 'icon' => 'fa fa-object-group', 'active' => false],
            ['type' => 'user-info', 'label' => $tabs['user_info'], 'url' => route('user-info.v2'), 'icon' => 'fa fa-id-card', 'active' => $activeType === 'user-info'],
            ['type' => 'all-users', 'label' => $tabs['all_users'], 'url' => route('all-users.v2'), 'icon' => 'fa fa-address-book', 'active' => $activeType === 'all-users'],
            ['type' => 'refresh-popup', 'label' => $tabs['refresh_popup'], 'url' => route('refresh-popup-seen-users.v2'), 'icon' => 'fa fa-bell-slash', 'active' => $activeType === 'refresh-popup'],
        ];
    }

}
