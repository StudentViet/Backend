<?php

namespace App\Http\Controllers\Api\ClassRoom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\User;

class ScheduleController extends Controller
{
    public function get($idClass)
    {
        if (!$idClass) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Không thể tìm thấy phòng học bạn tìm'
            ]);
        } else {
            $classRoom = ClassRoom::where('idClass', $idClass);
            if ($classRoom->count() > 0) {
                $arrayStudent = json_decode($classRoom->first()->data, true);

                if (!in_array(Auth::user()->email, $arrayStudent)) {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                } else {
                    if (Schedule::where('idClass', $idClass)->count() > 0) {
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Lấy thông tin thời khóa biểu thành công',
                            'data'      => [
                                'idSchedule' => Schedule::where('idClass', $idClass)->first()->idSchedule,
                                Schedule::where('idClass', $idClass)->first()->data
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Phòng học này chưa có thời khóa biểu'
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Không tìm thấy phòng học'
                ]);
            }
        }
    }

    public function create(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|max:255',
        //     'data' => 'required|json'
        // ]);

        // if ($validator->fails()) {

        //     return response()->json([
        //         'isError' => true,
        //         'message' => $validator->errors()->first()
        //     ]);
        // } else {
        //     $classRoom = ClassRoom::where('idClass', $request->idClass);
        //     if ($classRoom->count() > 0) {
        //         if (Auth::user()->role_id == 1) {
        //             if ($classRoom->where('userId', Auth::user()->id)->count() > 0) {
        //                 $Schedule = new Schedule;
        //                 $Schedule->name = $request->name;
        //                 $Schedule->idClass = $request->idClass;
        //                 $Schedule->idSchedule = \Illuminate\Support\Str::uuid();
        //                 $Schedule->data = $request->data != NULL ? $request->data : json_encode([]);
        //                 $Schedule->save();
        //                 return response()->json([
        //                     'isError'   => false,
        //                     'message'   => 'Tạo thời khóa biểu cho phòng học thành công'
        //                 ]);
        //             } else {
        //                 return response()->json([
        //                     'isError'   => true,
        //                     'message'   => 'Bạn không phải chủ phòng học này'
        //                 ]);
        //             }
        //         } else {
        //             return response()->json([
        //                 'isError' => true,
        //                 'message' => 'Bạn không thể thực hiện hành động này'
        //             ]);
        //         }
        //     } else {
        //         return response()->json([
        //             'isError'   => true,
        //             'message'   => 'Không tìm thấy phòng học'
        //         ]);
        //     }
        // }
        return response()->json([
            'thu2'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
            'thu3'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
            'thu4'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
            'thu5'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
            'thu6'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
            'thu7'  => [
                [
                    'tiet'      => 1,
                    'monhoc'    => 'Ngữ Văn',
                    'time'      => '7h30 - 8h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 2,
                    'monhoc'    => 'Toán',
                    'time'      => '8h15 - 9h',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 3,
                    'monhoc'    => 'Sử',
                    'time'      => '9h - 9h15',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 4,
                    'monhoc'    => 'Anh Văn',
                    'time'      => '9h15 - 9h25',
                    'link'      => 'https://gooogle.com'
                ],
                [
                    'tiet'      => 5,
                    'monhoc'    => 'Ngủ',
                    'time'      => '9h25 - 13h35',
                    'link'      => 'https://gooogle.com'
                ],
            ],
        ]);
    }

    public function delete($id)
    {
        if (!$id) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Không tìm thấy phòng học'
            ]);
        } else {
            $classRoom = ClassRoom::where('id', $id);
            if ($classRoom->count() > 0) {
                if (Auth::user()->role_id == 1) {
                    if ($classRoom->where('userId', Auth::user()->id)->count() > 0) {
                        $classRoom->delete();
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Xóa thời khóa biểu thành công'
                        ]);
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Bạn không phải chủ phòng học này'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError' => true,
                        'message' => 'Bạn không thể thực hiện hành động này'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Không tìm thấy phòng học'
                ]);
            }
        }
    }
}
