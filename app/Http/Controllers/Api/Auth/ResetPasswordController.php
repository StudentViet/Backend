<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    public function handle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|min:128|max:128',
            'password' => 'required|string|min:6|max:32|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'messages' => [$validator->errors()]
            ]);
        } else {
            $User = User::where([
                'email' => DB::table('password_resets')->where([
                    'token' => $request->token,
                ])->first()->email
            ]);
            if ($User->count() > 0) {
                if (Carbon::now()->lessThan(DB::table('password_resets')->where([
                    'token' => $request->token,
                    'email' => $User->first()->email
                ])->first()->expires_at)) {
                    $User->update([
                        'password'  => bcrypt($request->password)
                    ]);
                    DB::table('password_resets')->where([
                        'token' => $request->token,
                        'email' => $User->first()->email
                    ])->delete();
                    return response()->json([
                        'isError'   => false,
                        'message'   => 'Đổi mật khẩu thành công...'
                    ]);
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Liên kết này đã hết hạn'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Tài khoản không tồn tại trong hệ thống'
                ]);
            }
        }
    }
}
