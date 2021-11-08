<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\OfferMail;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotController extends Controller
{
    public function handle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:128',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            if (User::whereEmail($request->email)->count() > 0) {
                $token = Str::random(128);
                $offer = [
                    'url' => config('app.url') . '/auth/reset-password/' . $token,
                    'name'  => User::whereEmail($request->email)->first()->name
                ];
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'expires_at' => Carbon::now()->addMinutes(5),
                    'created_at' => Carbon::now(),
                ]);

                Mail::to($request->email)->send(new OfferMail($offer));
                return response()->json([
                    'isError'   => false,
                    'message'   => 'Đã gửi liên kết lấy lại mật khẩu qua email của bạn'
                ]);
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Email không tồn tại vui lòng kiểm tra lại'
                ]);
            }
        }
    }
}
