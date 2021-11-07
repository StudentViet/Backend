<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function UserInfo()
    {
        return response()->json([
            'isError'   => false,
            'data'      => [
                'user'  => Auth::user()
            ],
        ]);
    }
}
