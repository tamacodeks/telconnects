<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = Auth::user();
        $avatar = $user->getMedia('avatar')->first();

        return view('v2.app.profile.show', [
            'page_title' => 'Profile ' . $user->username,
            'user_image' => $avatar ? $avatar->getUrl('thumb') : 'images/avatar.png',
            'services' => Service::all(),
        ]);
    }
}
