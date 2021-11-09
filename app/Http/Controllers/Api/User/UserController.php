<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

    public function logout(Request $request)
    {
        $user = $request->user()->token();
        $user->revoke();
        return response()->json([
            'isError'  => false,
            'message'  => 'Đăng xuất thành công'
        ]);
    }
}
