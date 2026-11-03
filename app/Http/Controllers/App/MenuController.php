<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Kalamsoft\Langman\Lman;
use Validator;

class MenuController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index(Request $request, $id = '')
    {
        $template = !empty($request->template) ? $request->template : "1";
        if (!empty($id)) {
            $row = Menu::find($id)->toArray();
            $trans_lang = json_decode($row['trans_lang'], true);
        } else {
            $row = array(
                'id' => '',
                'parent_id' => '',
                'group_id' => '',
                'name' => '',
                'type' => '',
                'url' => '',
                'position' => '',
                'icon' => '',
                'status' => '',
                'ordering' => '',
            );
            $trans_lang = array();
        }
        $page_data = [
            'page_title' => "Manage Menus for the user groups",
            'menus' => AppHelper::menus('sidebar', '', $template,true),
            'row' => $row,
            'trans_lang' => $trans_lang,
            'user_group_id' => $template
        ];
        return view('app.menus.index', $page_data);
    }

    function save(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'url' => 'required',
            'position' => 'required',
        ]);

        if ($validator->fails()) {
            AppHelper::logger('warning','Menu Update','Update menu failed due to validation',[$validator]);
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $menu_id = $request->id;
        $data = array(
            'parent_id' => !is_null($request->input('parent_id')) ? $request->input('parent_id') : '0',
            'group_id' => !is_null($request->input('group_id')) ? $request->input('group_id') : '0',
            'name' => $request->name,
            'url' => $request->url,
            'position' => $request->position,
            'status' => $request->is_active,
            'icon' => $request->menu_icon,
            'ordering' => $request->ordering,
        );
//        dd($request->all());
        $availableLanguages = ['en', 'fr'];

        $language = array();
        foreach ($availableLanguages as $lang) {
            // Skip English ('en') if you only want to process other languages
            if ($lang != 'en') {
                $menu_lang = isset($_POST['language_title'][$lang]) ? htmlspecialchars($_POST['language_title'][$lang], ENT_QUOTES, 'UTF-8') : '';
                $language['title'][$lang] = $menu_lang;
            }
        }

        // Convert the language array to JSON and store it in $data['trans_lang']
        $data['trans_lang'] = json_encode($language);
        if (!empty($menu_id)) {
            //update menu
            $data['updated_at'] = date("Y-m-d H:i:s");
            $data['updated_by'] = auth()->user()->id;
            Menu::where('id', '=', $menu_id)->update($data);
        } else {
            //insert menu
            $data['created_at'] = date("Y-m-d H:i:s");
            $data['created_by'] = auth()->user()->id;
            $menu_id = Menu::insertGetId($data);
        }
        $return = "menus?template=" . $request->input('group_id');
        Log::info('menu id ' . $menu_id . ' has been updated');
        AppHelper::logger('info', 'Menu Update', 'Menu ID ' . $menu_id . ' has been updated');
        return redirect($return)->with('message', 'Menu updated successfully!')->with('message_type', 'success');
    }

    function re_order_menu(Request $request)
    {
//        dd($request->all());
        $rules = array(
            'reorder' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $menus = json_decode($request->input('reorder'), true);
//            dd($menus);
            $child = array();
            $a = 0;
            foreach ($menus as $m) {
                if (isset($m['children'])) {
                    $b = 0;
                    foreach ($m['children'] as $l) {
                        if (isset($l['children'])) {
                            $c = 0;
                            foreach ($l['children'] as $l2) {
                                $level3[] = $l2['id'];
                                Menu::where('id', '=', $l2['id'])
                                    ->update(array('parent_id' => $l['id'], 'ordering' => $c));
                                $c++;
                            }
                        }
                        Menu::where('id', '=', $l['id'])
                            ->update(array('parent_id' => $m['id'], 'ordering' => $b));
                        $b++;
                    }
                }
                Menu::where('id', '=', $m['id'])
                    ->update(array('parent_id' => '0', 'ordering' => $a));
                $a++;
            }
            Log::info('menu re-ordered');
            AppHelper::logger('info', 'Menu Re-Order', "Menu Reordered!");
            return redirect('menus?template='.$request->user_group_id)->with('message', 'Menu Ordered Successfully!')->with('message_type', 'success');
        } else {
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
    }

    public function remove(Request $request,$id)
    {
        $menu = Menu::find($id);
        $menu->delete();
        Log::info('menu ID ' . $id . ' has been removed!');
        AppHelper::logger('info', 'Menu Delete', "Menu id $id has been removed");
        return redirect('menus?template='.$request->template)->with('message',trans('common.msg_remove_success'))->with('message_type','success');
    }
}
