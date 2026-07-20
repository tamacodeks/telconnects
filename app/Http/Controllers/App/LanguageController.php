<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kalamsoft\Langman\Lman;

class LanguageController extends Controller
{
    private $language;

    public function __construct()
    {
        $this->middleware('auth')->except('switchLang');
        $this->language = [
            ['folder' => 'en'],
            ['folder' => 'fr'],
            // Add more languages as needed
        ];
    }

    /**
     * Switch the language
     * @param $lang
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLang($lang, Request $request)
    {
        foreach ($this->language as $all_lang) {
            if ($all_lang['folder'] == $lang) {
                \Session::put('locale', $all_lang['folder']);
                break;
            }
        }
        return redirect()->back();
    }

    /**
     * VIEW - Manage All Translations
     * @param Request $request
     * @param null $type
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $langPath = base_path('resources/lang');
        $languages = [];

        foreach (glob($langPath . '/*', GLOB_ONLYDIR) as $dir) {
            $folder = basename($dir);
            $configFile = $dir . '/config.json';

            $meta = [
                'name' => ucfirst($folder),
                'folder' => $folder,
                'author' => 'System',
            ];

            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                $meta = array_merge($meta, $config);
            }

            $languages[] = $meta;
        }

        // Editing a specific language file
        if ($request->has('edit')) {
            $lang = $request->input('edit');
            $file = $request->input('file') ?? 'auth.php';
            $path = $langPath . '/' . $lang . '/' . $file;

            if (!file_exists($path)) {
                abort(404, 'Language file not found.');
            }

            $this->data = [
                'stringLang' => include($path),
                'lang' => $lang,
                'files' => array_diff(scandir($langPath . '/' . $lang), ['.', '..']),
                'file' => $file,
            ];
            return view('translation.edit', $this->data);
        }

        return view('translation.index', ['languages' => $languages]);
    }


    /**
     * VIEW - Add New Translation
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view("translation.create");
    }

    /**
     * POST - Add New Translation
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save( Request $request)
    {
        $rules = array(
            'name'		=> 'required',
            'folder'	=> 'required|alpha'
        );
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $template = base_path();
            $folder = $request->input('folder');
            if(\File::exists($template."/resources/lang/".$folder) == false){
                mkdir( $template."/resources/lang/".$folder ,0777 );
            }
            $info = json_encode(array("name"=> $request->input('name'),"folder"=> $folder , "author" => $request->input('author') ? $request->input('author') : ""));
            $fp=fopen(  $template.'/resources/lang/'.$folder.'/config.json',"w+");
            fwrite($fp,$info);
            fclose($fp);
            $files = scandir( $template .'/resources/lang/en/');
            foreach($files as $f)
            {
                if($f != "." and $f != ".." and $f != 'config.json')
                {
                    copy( $template .'/resources/lang/en/'.$f, $template .'/resources/lang/'.$folder.'/'.$f);
                }
            }
            return redirect('translation');
        } else {
            return redirect('translation')
                ->withInput()
                ->withErrors($validator);
        }
    }

    /**
     * POST - Update Translation Phrases
     * @param Request $request
     * @return mixed
     */
    public function update( Request $request)
    {
        $template = base_path();
        $form  	= "<?php \n";
        $form .= "/**
 * Updated by prabakaran-t/lman
 * Date: ".date("Y-m-d H:i:s")."
 */\n";
        $form 	.= "return array( \n";
        foreach($_POST as $key => $val)
        {
            if($key !='_token' && $key !='lang' && $key !='file')
            {
                if(!is_array($val))
                {
                    $form .= '"'.$key.'" => "'.strip_tags($val).'", '." \n ";

                } else {
                    $form .= '"'.$key.'" => array( '." \n ";
                    foreach($val as $k=>$v)
                    {
                        $form .= ' "'.$k.'" => "'.strip_tags($v).'", '." \n ";
                    }
                    $form .= "), \n";
                }
            }

        }
        $form .= ');';
        //echo $form; exit;
        $lang = $request->input('lang');
        $file	= $request->input('file');
        $filename = $template .'/resources/lang/'.$lang.'/'.$file;
        //	$filename = 'lang.php';
        $fp=fopen($filename,"w+");
        fwrite($fp,$form);
        fclose($fp);
        return redirect('translation?edit='.$lang.'&file='.$file);
    }

    /**
     * Remove Translation folder
     * @param $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove( $folder )
    {
        self::removeDir( base_path()."/resources/lang/".$folder);
        return redirect('translation');
    }

    /**
     * UTILITY FN - Remove dir
     * @param $dir
     */
    function removeDir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                self::removedir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

}
