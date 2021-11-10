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

    public function searchByEmail($email)
    {
        if (!$email) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Thiếu dữ liệu gửi lên'
            ]);
        } else {
            if (User::whereEmail($email)->count() > 0) {
                return response()->json([
                    'isError'   => false,
                    'message'   => 'Lấy thông tin thành công',
                    'data'      => [
                        'name'  => User::whereEmail($email)->first()->name
                    ],
                ]);
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Email không tồn tại'
                ]);
            }
        }
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
