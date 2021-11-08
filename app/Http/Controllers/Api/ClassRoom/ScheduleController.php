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
    public function getList($idClass)
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
                    return response()->json([
                        'isError'   => false,
                        'message'   => 'Lấy thông tin thời khóa biểu thành công',
                        'data'      => [
                            'idSchedule' => Schedule::where('idClass', $idClass)->first()->idSchedule,
                            Schedule::where('idClass', $idClass)->first()->data
                        ]
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

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'data' => 'required|json'
        ]);

        if ($validator->fails()) {

            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $classRoom = ClassRoom::where('idClass', $request->idClass);
            if ($classRoom->count() > 0) {
                if (Auth::user()->role_id == 1) {
                    if ($classRoom->where('userId', Auth::user()->id)->count() > 0) {
                        $Schedule = new Schedule;
                        $Schedule->name = $request->title;
                        $Schedule->idClass = $request->idClass;
                        $Schedule->idSchedule = \Illuminate\Support\Str::uuid();
                        $Schedule->data = $request->data != NULL ? $request->data : json_encode([]);
                        $Schedule->save();
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Tạo thời khóa biểu cho phòng học thành công'
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
