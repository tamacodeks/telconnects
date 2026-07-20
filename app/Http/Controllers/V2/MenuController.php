<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Validator;

class MenuController extends Controller
{
    protected function menuSections()
    {
        return [
            'account' => 'Account',
            'services' => 'Services',
            'history' => 'History',
            'settings' => 'Settings',
        ];
    }

    protected function hasMenuSectionColumn()
    {
        try {
            return Schema::hasColumn('menus', 'section');
        } catch (\Exception $exception) {
            return false;
        }
    }

    protected function normalizeMenuSection($section, $url = '')
    {
        $section = strtolower(trim((string) $section));
        if (array_key_exists($section, $this->menuSections())) {
            return $section;
        }

        return $this->sectionForMenuPath($url);
    }

    protected function sectionForMenuPath($url)
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : (string) $url;
        $path = strtolower(trim($path, '/'));

        $exactSections = [
            'dashboard' => 'account',
            'dashboard-v2' => 'account',
            'profile' => 'account',
            'profile-v2' => 'account',
            'invoices' => 'account',
            'payment' => 'account',
            'payments' => 'account',
            'payments-v2' => 'account',
            'balance' => 'account',
            'calling-cards' => 'services',
            'calling-cards-v2' => 'services',
            'callings-cards' => 'services',
            'mycallingcards' => 'services',
            'print_callingcard' => 'services',
            'print_mycallingcard' => 'services',
            'tama-topup' => 'services',
            'tama-topup-v1' => 'services',
            'tama-topup-v2' => 'services',
            'tama-topup-france' => 'services',
            'bus' => 'services',
            'bus-v2' => 'services',
            'flix-bus' => 'services',
            'cc-price-lists' => 'services',
            'cc-price-lists-v2' => 'services',
            'my/cc-price-lists' => 'services',
            'cc-price-list/groups' => 'services',
            'orders' => 'history',
            'orders-v2' => 'history',
            'my/orders' => 'history',
            'transactions' => 'history',
            'transactions-v2' => 'history',
            'my/transactions' => 'history',
            'failed_transaction' => 'history',
            'failed-transactions-v2' => 'history',
            'cc/report/usage-history' => 'history',
            'cc/report/pins' => 'history',
            'cc-pin-history' => 'history',
            'cc-pin-history-v2' => 'history',
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
            'profile-v2/' => 'account',
            'invoices/' => 'account',
            'payment/' => 'account',
            'payments/' => 'account',
            'payments-v2/' => 'account',
            'calling-cards/' => 'services',
            'calling-cards-v2/' => 'services',
            'callings-cards/' => 'services',
            'mycallingcards/' => 'services',
            'tama-topup/' => 'services',
            'tama-topup-v1/' => 'services',
            'tama-topup-v2/' => 'services',
            'tama-topup-france/' => 'services',
            'bus/' => 'services',
            'bus-v2/' => 'services',
            'flix-bus/' => 'services',
            'cc-price-lists/' => 'services',
            'cc-price-lists-v2/' => 'services',
            'my/cc-price-lists/' => 'services',
            'cc-price-list/' => 'services',
            'orders/' => 'history',
            'orders-v2/' => 'history',
            'my/orders/' => 'history',
            'transactions/' => 'history',
            'transactions-v2/' => 'history',
            'my/transactions/' => 'history',
            'failed_transaction/' => 'history',
            'failed-transactions-v2/' => 'history',
            'cc/report/' => 'history',
            'cc-pin-history/' => 'history',
            'cc-pin-history-v2/' => 'history',
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

    public function index(Request $request, $id = '')
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $userGroups = UserGroup::select('id', 'name', 'description', 'status')
            ->orderBy('id', 'asc')
            ->get();
        $groupMenuCounts = Menu::select('group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('group_id')
            ->pluck('total', 'group_id')
            ->toArray();

        $selectedGroupId = (int) $request->get('template', 1);
        $selectedGroup = $userGroups->filter(function ($group) use ($selectedGroupId) {
            return (int) $group->id === (int) $selectedGroupId;
        })->first();

        if ($userGroups->count() > 0 && !$selectedGroup) {
            $selectedGroupId = (int) $userGroups->first()->id;
            $selectedGroup = $userGroups->first();
        }

        $menus = Menu::where('group_id', $selectedGroupId)
            ->orderBy('parent_id', 'asc')
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $row = $this->defaultRow($selectedGroupId);
        if ($id !== '') {
            $menu = Menu::where('group_id', $selectedGroupId)
                ->where('id', (int) $id)
                ->first();

            if ($menu) {
                $row = $this->rowFromMenu($menu);
            }
        }

        $menuTree = $this->buildTree($menus);
        $flatMenus = $this->flattenTree($menuTree);
        $transLang = json_decode((string) $row['trans_lang'], true);
        $transLang = is_array($transLang) ? $transLang : [];

        $activeCount = $menus->filter(function ($menu) {
            return (int) $menu->status === 1;
        })->count();

        $stats = [
            'total' => $menus->count(),
            'active' => $activeCount,
            'inactive' => max(0, $menus->count() - $activeCount),
            'root' => $menus->filter(function ($menu) {
                return (int) $menu->parent_id === 0;
            })->count(),
        ];
        $recentAuditLogs = $this->recentMenuAuditRecords();

        return view('v2.app.menus.index', [
            'page_title' => 'Menu Control Center',
            'userGroups' => $userGroups,
            'selectedGroupId' => $selectedGroupId,
            'selectedGroup' => $selectedGroup,
            'groupMenuCounts' => $groupMenuCounts,
            'menus' => $menus,
            'menuTree' => $menuTree,
            'flatMenus' => $flatMenus,
            'row' => $row,
            'trans_lang' => $transLang,
            'stats' => $stats,
            'menuSections' => $this->menuSections(),
            'canViewMenuAudit' => $this->isRootUser(),
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }

    public function save(Request $request)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'name' => 'required|max:100',
            'url' => 'required|max:100|regex:/^[A-Za-z0-9_\/\-.?=&]+$/',
            'group_id' => 'required|exists:user_groups,id',
            'parent_id' => 'nullable|integer',
            'section' => 'required|in:account,services,history,settings',
            'position' => 'required|in:top,sidebar,both',
            'is_active' => 'required|in:0,1',
            'menu_icon' => 'required|max:155',
            'ordering' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationFailure($request, $validator->errors()->toArray(), 'Please check the menu fields and try again.');
        }

        $groupId = (int) $request->input('group_id');
        $menuId = (int) $request->input('id');
        $parentId = (int) $request->input('parent_id', 0);
        $name = trim((string) $request->input('name'));
        $url = trim((string) $request->input('url'));
        if ($name !== '') {
            $url = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
            $request->merge(['url' => $url]);
        }
        $icon = trim((string) $request->input('menu_icon'));
        $section = $this->normalizeMenuSection($request->input('section'), $url);
        $placeholderErrors = [];

        if (strcasecmp($name, 'Menu title') === 0) {
            $placeholderErrors['name'][] = 'Please enter a real menu title.';
        }

        if ($url === '' || strcasecmp($url, 'menu-url') === 0) {
            $placeholderErrors['url'][] = 'Please enter a real menu URL.';
        }

        if ($icon === '' || strtolower($icon) === 'fa fa-circle-o') {
            $placeholderErrors['menu_icon'][] = 'Please choose a specific icon class for this menu.';
        }

        if (!empty($placeholderErrors)) {
            return $this->validationFailure($request, $placeholderErrors, 'Please replace placeholder values before saving.');
        }

        $menu = $menuId > 0
            ? Menu::where('group_id', $groupId)->where('id', $menuId)->first()
            : new Menu();
        $oldValues = $menuId > 0 && $menu ? $this->menuAuditSnapshot($menu) : null;
        $oldStatus = $menuId > 0 && $menu ? (int) $menu->status : null;

        if ($menuId > 0 && !$menu) {
            return $this->validationFailure($request, [
                'id' => ['Selected menu was not found for this user group.'],
            ], 'Selected menu was not found for this user group.');
        }

        if ($menuId > 0 && $parentId === $menuId) {
            return $this->validationFailure($request, [
                'parent_id' => ['A menu cannot be selected as its own parent.'],
            ], 'Invalid parent selected.');
        }

        if ($menuId > 0 && $parentId > 0 && $this->wouldCreateCycle($menuId, $parentId, $groupId)) {
            return $this->validationFailure($request, [
                'parent_id' => ['A menu cannot be placed under itself or its own child.'],
            ], 'Invalid parent selected.');
        }

        if ($parentId > 0) {
            $parentExists = Menu::where('group_id', $groupId)
                ->where('id', $parentId)
                ->exists();
            if (!$parentExists) {
                $parentId = 0;
            }
        }

        $duplicateNameUrl = Menu::where('group_id', $groupId)
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->where('url', $url)
            ->when($menuId > 0, function ($query) use ($menuId) {
                return $query->where('id', '!=', $menuId);
            })
            ->exists();

        if ($duplicateNameUrl) {
            return $this->validationFailure($request, [
                'name' => ['A menu with the same title and URL already exists under this parent.'],
                'url' => ['A menu with the same title and URL already exists under this parent.'],
            ], 'Duplicate menu title and URL.');
        }

        $duplicateUrl = Menu::where('group_id', $groupId)
            ->where('url', $url)
            ->when($menuId > 0, function ($query) use ($menuId) {
                return $query->where('id', '!=', $menuId);
            })
            ->exists();

        if ($duplicateUrl) {
            return $this->validationFailure($request, [
                'url' => ['This URL already exists for the selected user group.'],
            ], 'Duplicate menu URL.');
        }

        $now = date('Y-m-d H:i:s');
        $userId = auth()->id();

        $menu->parent_id = $parentId;
        $menu->group_id = $groupId;
        $menu->name = $name;
        $menu->url = $url;
        if ($this->hasMenuSectionColumn()) {
            $menu->section = $section;
        }
        $menu->position = $request->input('position');
        $menu->status = (int) $request->input('is_active');
        $menu->icon = $icon;
        $menu->ordering = $request->filled('ordering')
            ? (int) $request->input('ordering')
            : $this->nextOrdering($groupId, $parentId);
        $menu->trans_lang = $this->languagePayload($request);

        if ($menuId > 0) {
            $menu->updated_at = $now;
            $menu->updated_by = $userId;
        } else {
            $menu->created_at = $now;
            $menu->created_by = $userId;
        }

        $menu->save();
        $this->clearMenuCache($groupId);

        $newValues = $this->menuAuditSnapshot($menu);
        $this->writeMenuAudit($request, $menuId > 0 ? 'menu_updated' : 'menu_created', $oldValues, $newValues);

        if ($menuId > 0 && $oldStatus !== null && $oldStatus !== (int) $menu->status) {
            $this->writeMenuAudit($request, 'menu_status_changed', ['status' => $oldStatus], ['status' => (int) $menu->status, 'menu_id' => (int) $menu->id]);
        }

        Log::info('V2 menu id ' . $menu->id . ' has been saved');

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Menu saved successfully.',
                'data' => $this->menuDataPayload($request, $groupId, (int) $menu->id),
            ]);
        }

        return redirect('menus-v2?template=' . $groupId)
            ->with('message', 'Menu saved successfully.')
            ->with('message_type', 'success');
    }

    public function reOrder(Request $request)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'reorder' => 'required',
            'user_group_id' => 'required|exists:user_groups,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('message', 'Unable to reorder menus. Please refresh and try again.')
                ->with('message_type', 'warning');
        }

        $groupId = (int) $request->input('user_group_id');
        $items = json_decode($request->input('reorder'), true);

        if (!is_array($items)) {
            return redirect('menus-v2?template=' . $groupId)
                ->with('message', 'Invalid menu order payload.')
                ->with('message_type', 'warning');
        }
        $ids = array_values(array_unique($this->collectOrderIds($items)));
        $validCount = empty($ids) ? 0 : Menu::where('group_id', $groupId)->whereIn('id', $ids)->count();

        if (count($ids) !== (int) $validCount) {
            return $this->validationFailure($request, [
                'reorder' => ['The reorder payload contains menus outside the selected group.'],
            ], 'Invalid reorder payload.');
        }

        $oldValues = $this->menuOrderSnapshot($groupId);

        DB::transaction(function () use ($items, $groupId) {
            $this->syncOrder($items, 0, $groupId);
        });
        $this->clearMenuCache($groupId);
        $newValues = $this->menuOrderSnapshot($groupId);
        $this->writeMenuAudit($request, 'menu_reordered', $oldValues, $newValues);

        Log::info('V2 menu order updated for user group ' . $groupId);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Menu order updated successfully.',
                'data' => $this->menuDataPayload($request, $groupId),
            ]);
        }

        return redirect('menus-v2?template=' . $groupId)
            ->with('message', 'Menu order updated successfully.')
            ->with('message_type', 'success');
    }

    public function remove(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $template = (int) $request->get('template', 1);
        $menu = Menu::where('id', (int) $id)
            ->where('group_id', $template)
            ->first();

        if (!$menu) {
            return $this->validationFailure($request, [
                'id' => ['Menu not found in the selected group.'],
            ], 'Menu not found.');
        }

        $redirectGroup = (int) $menu->group_id ?: $template;
        $oldValues = $this->menuAuditSnapshot($menu);
        Menu::where('group_id', $redirectGroup)
            ->where('parent_id', $menu->id)
            ->update([
                'parent_id' => (int) $menu->parent_id ?: 0,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->id(),
            ]);

        $menuName = $menu->name;
        $menu->delete();
        $this->clearMenuCache($redirectGroup);
        $this->writeMenuAudit($request, 'menu_deleted', $oldValues, null);

        Log::info('V2 menu ' . $menuName . ' removed');

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Menu removed successfully.',
                'data' => $this->menuDataPayload($request, $redirectGroup),
            ]);
        }

        return redirect('menus-v2?template=' . $redirectGroup)
            ->with('message', 'Menu removed successfully.')
            ->with('message_type', 'success');
    }

    public function data(Request $request)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'group_id' => 'nullable|exists:user_groups,id',
            'template' => 'nullable|exists:user_groups,id',
            'edit_id' => 'nullable|integer',
            'search' => 'nullable|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationFailure($request, $validator->errors()->toArray(), 'Invalid menu data request.');
        }

        $groupId = (int) ($request->get('group_id') ?: $request->get('template') ?: 1);

        return response()->json([
            'message' => 'Menu data loaded.',
            'data' => $this->menuDataPayload($request, $groupId, (int) $request->get('edit_id', 0), (string) $request->get('search', '')),
        ]);
    }

    public function showJson(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $groupId = (int) ($request->get('group_id') ?: $request->get('template') ?: 1);
        $menu = Menu::where('group_id', $groupId)->where('id', (int) $id)->first();

        if (!$menu) {
            return $this->validationFailure($request, [
                'id' => ['Menu not found in the selected group.'],
            ], 'Menu not found.');
        }

        return response()->json([
            'message' => 'Menu loaded.',
            'data' => [
                'row' => $this->rowFromMenu($menu),
                'trans_lang' => json_decode((string) $menu->trans_lang, true) ?: [],
            ],
        ]);
    }

    public function status(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:user_groups,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->validationFailure($request, $validator->errors()->toArray(), 'Invalid status update request.');
        }

        $groupId = (int) $request->input('group_id');
        $menu = Menu::where('group_id', $groupId)->where('id', (int) $id)->first();

        if (!$menu) {
            return $this->validationFailure($request, [
                'id' => ['Menu not found in the selected group.'],
            ], 'Menu not found.');
        }

        $oldValues = $this->menuAuditSnapshot($menu);
        $oldStatus = (int) $menu->status;
        $menu->status = (int) $request->input('status');
        $menu->updated_at = date('Y-m-d H:i:s');
        $menu->updated_by = auth()->id();
        $menu->save();
        $this->clearMenuCache($groupId);

        $this->writeMenuAudit($request, 'menu_status_changed', ['status' => $oldStatus] + $oldValues, $this->menuAuditSnapshot($menu));

        return response()->json([
            'message' => 'Menu status updated.',
            'data' => $this->menuDataPayload($request, $groupId, (int) $menu->id),
        ]);
    }

    protected function defaultRow($groupId)
    {
        return [
            'id' => '',
            'parent_id' => 0,
            'group_id' => $groupId,
            'name' => '',
            'type' => '',
            'url' => '',
            'section' => 'services',
            'position' => 'sidebar',
            'icon' => '',
            'status' => 1,
            'ordering' => '',
            'trans_lang' => '',
        ];
    }

    protected function rowFromMenu(Menu $menu)
    {
        return [
            'id' => $menu->id,
            'parent_id' => (int) $menu->parent_id,
            'group_id' => (int) $menu->group_id,
            'name' => $menu->name,
            'type' => isset($menu->type) ? $menu->type : '',
            'url' => $menu->url,
            'section' => $this->normalizeMenuSection($this->hasMenuSectionColumn() ? $menu->section : '', $menu->url),
            'position' => $menu->position,
            'icon' => $menu->icon,
            'status' => (int) $menu->status,
            'ordering' => $menu->ordering,
            'trans_lang' => $menu->trans_lang,
        ];
    }

    protected function buildTree($menus, $parentId = 0, $depth = 0)
    {
        return $menus->filter(function ($menu) use ($parentId) {
            return (int) $menu->parent_id === (int) $parentId;
        })->map(function ($menu) use ($menus, $depth) {
            $section = $this->normalizeMenuSection($this->hasMenuSectionColumn() ? $menu->section : '', $menu->url);
            $sections = $this->menuSections();

            return [
                'id' => (int) $menu->id,
                'parent_id' => (int) $menu->parent_id,
                'group_id' => (int) $menu->group_id,
                'name' => $menu->name,
                'url' => $menu->url,
                'icon' => $menu->icon ?: 'fa fa-sitemap',
                'section' => $section,
                'section_label' => $sections[$section],
                'status' => (int) $menu->status,
                'ordering' => (int) $menu->ordering,
                'updated_at' => $menu->updated_at,
                'updated_by' => $menu->updated_by,
                'depth' => $depth,
                'children' => $this->buildTree($menus, $menu->id, $depth + 1),
            ];
        })->values()->all();
    }

    protected function flattenTree(array $items, $prefix = '')
    {
        $flat = [];

        foreach ($items as $item) {
            $item['label'] = $prefix . $item['name'];
            $flat[] = $item;

            if (!empty($item['children'])) {
                $flat = array_merge($flat, $this->flattenTree($item['children'], $prefix . '-- '));
            }
        }

        return $flat;
    }

    protected function nextOrdering($groupId, $parentId)
    {
        return (int) Menu::where('group_id', $groupId)
            ->where('parent_id', $parentId)
            ->max('ordering') + 1;
    }

    protected function languagePayload(Request $request)
    {
        $language = ['title' => []];
        $titles = (array) $request->input('language_title', []);

        foreach (['fr'] as $lang) {
            $language['title'][$lang] = isset($titles[$lang])
                ? htmlspecialchars($titles[$lang], ENT_QUOTES, 'UTF-8')
                : '';
        }

        return json_encode($language);
    }

    protected function wouldCreateCycle($menuId, $parentId, $groupId)
    {
        $guard = 0;
        $currentParentId = (int) $parentId;

        while ($currentParentId > 0 && $guard < 50) {
            if ($currentParentId === (int) $menuId) {
                return true;
            }

            $parent = Menu::where('group_id', $groupId)
                ->where('id', $currentParentId)
                ->select('id', 'parent_id')
                ->first();

            if (!$parent) {
                return false;
            }

            $currentParentId = (int) $parent->parent_id;
            $guard++;
        }

        return false;
    }

    protected function denyUnlessRoot(Request $request)
    {
        if ($this->isRootUser()) {
            return null;
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'You are not authorized to manage menus.',
                'errors' => ['authorization' => ['Root access is required.']],
            ], 403);
        }

        abort(403, 'Root access is required.');
    }

    protected function isRootUser()
    {
        return auth()->check() && (int) auth()->user()->group_id === 1;
    }

    protected function validationFailure(Request $request, array $errors, $message)
    {
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors($errors)
            ->with('message', $message)
            ->with('message_type', 'warning');
    }

    protected function menuDataPayload(Request $request, $groupId, $editId = 0, $search = '')
    {
        $menus = $this->menusForGroup($groupId);
        $treeMenus = $this->filterMenusForSearch($menus, $search);
        $menuTree = $this->buildTree($treeMenus);
        $fullTree = $this->buildTree($menus);
        $flatMenus = $this->flattenTree($fullTree);

        $row = $this->defaultRow($groupId);
        if ($editId > 0) {
            $editMenu = $menus->first(function ($menu) use ($editId) {
                return (int) $menu->id === (int) $editId;
            });

            if ($editMenu) {
                $row = $this->rowFromMenu($editMenu);
            }
        }

        $transLang = json_decode((string) $row['trans_lang'], true);
        $transLang = is_array($transLang) ? $transLang : [];
        $activeCount = $menus->filter(function ($menu) {
            return (int) $menu->status === 1;
        })->count();

        return [
            'group_id' => (int) $groupId,
            'stats' => [
                'total' => $menus->count(),
                'active' => $activeCount,
                'inactive' => max(0, $menus->count() - $activeCount),
                'root' => $menus->filter(function ($menu) {
                    return (int) $menu->parent_id === 0;
                })->count(),
            ],
            'tree_html' => view('v2.app.menus.partials.tree', [
                'items' => $menuTree,
                'selectedId' => $row['id'],
                'selectedGroupId' => $groupId,
            ])->render(),
            'row' => $row,
            'trans_lang' => $transLang,
            'flat_menus' => $flatMenus,
            'menu_sections' => $this->menuSections(),
            'existing_menus' => $menus->map(function ($menu) {
                return [
                    'id' => (int) $menu->id,
                    'parent_id' => (int) $menu->parent_id,
                    'name' => (string) $menu->name,
                    'url' => (string) $menu->url,
                    'icon' => (string) $menu->icon,
                    'section' => $this->normalizeMenuSection($this->hasMenuSectionColumn() ? $menu->section : '', $menu->url),
                ];
            })->values()->all(),
            'audit_logs' => $this->recentMenuAuditPayload(),
            'search' => (string) $search,
        ];
    }

    protected function menusForGroup($groupId)
    {
        return Cache::remember('menus_v2_group_' . (int) $groupId, 60, function () use ($groupId) {
            $columns = ['id', 'parent_id', 'group_id', 'name', 'url', 'position', 'icon', 'status', 'ordering', 'trans_lang', 'updated_at', 'updated_by'];
            if ($this->hasMenuSectionColumn()) {
                $columns[] = 'section';
            }

            return Menu::where('group_id', (int) $groupId)
                ->orderBy('parent_id', 'asc')
                ->orderBy('ordering', 'asc')
                ->orderBy('name', 'asc')
                ->select($columns)
                ->get();
        });
    }

    protected function clearMenuCache($groupId)
    {
        Cache::forget('menus_v2_group_' . (int) $groupId);
    }

    protected function filterMenusForSearch($menus, $search)
    {
        $search = strtolower(trim((string) $search));
        if ($search === '') {
            return $menus;
        }

        $byId = $menus->keyBy('id');
        $keep = [];

        foreach ($menus as $menu) {
            $haystack = strtolower((string) $menu->name . ' ' . (string) $menu->url);
            if (strpos($haystack, $search) === false) {
                continue;
            }

            $keep[(int) $menu->id] = true;
            $parentId = (int) $menu->parent_id;
            $guard = 0;

            while ($parentId > 0 && isset($byId[$parentId]) && $guard < 50) {
                $keep[$parentId] = true;
                $parentId = (int) $byId[$parentId]->parent_id;
                $guard++;
            }
        }

        return $menus->filter(function ($menu) use ($keep) {
            return isset($keep[(int) $menu->id]);
        })->values();
    }

    protected function recentMenuAuditPayload()
    {
        return $this->recentMenuAuditRecords()
            ->map(function ($log) {
                return [
                    'user_id' => $log->user_id,
                    'action' => $log->action,
                    'module' => $log->module,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at,
                ];
            })
            ->values()
            ->all();
    }

    protected function recentMenuAuditRecords()
    {
        if (!$this->hasMenuAuditTable()) {
            return collect();
        }

        return DB::table('menu_audit_logs')
            ->where('module', 'menus-v2')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
    }

    protected function menuAuditSnapshot(Menu $menu)
    {
        return [
            'id' => (int) $menu->id,
            'parent_id' => (int) $menu->parent_id,
            'group_id' => (int) $menu->group_id,
            'name' => (string) $menu->name,
            'url' => (string) $menu->url,
            'section' => $this->normalizeMenuSection($this->hasMenuSectionColumn() ? $menu->section : '', $menu->url),
            'position' => (string) $menu->position,
            'icon' => (string) $menu->icon,
            'status' => (int) $menu->status,
            'ordering' => (int) $menu->ordering,
        ];
    }

    protected function menuOrderSnapshot($groupId)
    {
        return Menu::where('group_id', (int) $groupId)
            ->orderBy('parent_id', 'asc')
            ->orderBy('ordering', 'asc')
            ->select('id', 'parent_id', 'group_id', 'name', 'url', 'status', 'ordering')
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => (int) $menu->id,
                    'parent_id' => (int) $menu->parent_id,
                    'group_id' => (int) $menu->group_id,
                    'name' => (string) $menu->name,
                    'url' => (string) $menu->url,
                    'status' => (int) $menu->status,
                    'ordering' => (int) $menu->ordering,
                ];
            })
            ->values()
            ->all();
    }

    protected function writeMenuAudit(Request $request, $action, $oldValues, $newValues)
    {
        if (!$this->hasMenuAuditTable()) {
            return;
        }

        try {
            DB::table('menu_audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $action,
                'module' => 'menus-v2',
                'old_values' => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values' => $newValues !== null ? json_encode($newValues) : null,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Menus V2 audit logging skipped: ' . $exception->getMessage());
        }
    }

    protected function hasMenuAuditTable()
    {
        try {
            return Schema::hasTable('menu_audit_logs');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    protected function collectOrderIds(array $items)
    {
        $ids = [];

        foreach ($items as $item) {
            if (!empty($item['id'])) {
                $ids[] = (int) $item['id'];
            }

            if (!empty($item['children']) && is_array($item['children'])) {
                $ids = array_merge($ids, $this->collectOrderIds($item['children']));
            }
        }

        return $ids;
    }

    protected function syncOrder(array $items, $parentId, $groupId)
    {
        foreach ($items as $ordering => $item) {
            if (empty($item['id'])) {
                continue;
            }

            $id = (int) $item['id'];
            Menu::where('id', $id)
                ->where('group_id', $groupId)
                ->update([
                    'parent_id' => (int) $parentId,
                    'ordering' => (int) $ordering,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => auth()->id(),
                ]);

            if (!empty($item['children']) && is_array($item['children'])) {
                $this->syncOrder($item['children'], $id, $groupId);
            }
        }
    }
}
