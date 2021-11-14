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

    protected function CheckExists($email, $idClass)
    {
        $ClassRoom = ClassRoom::where('idClass', $idClass);
        if ($ClassRoom->count() > 0) {
            foreach ($ClassRoom->get() as $row) {
                $arrayStudent = json_decode($row->data, true);
                if (in_array($email, $arrayStudent)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idClass'   => 'required|string|max:255',
            'period'    => 'required|integer|max:1',
            'subject'   => 'required|string|max:255',
            'time'      => 'required',
            'link'      => 'required|url|max:255',
            'day'       => 'required|integer|max:1'
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
                        $Schedule->idClass = $request->idClass;
                        $Schedule->idSchedule = \Illuminate\Support\Str::uuid();
                        $Schedule->period = $request->period;
                        $Schedule->subject = $request->subject;
                        $Schedule->time = $request->time;
                        $Schedule->link = $request->link;
                        $Schedule->day = $request->day;
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

    public function delete($idClass)
    {
        if (!$idClass) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Không tìm thấy phòng học'
            ]);
        } else {
            $classRoom = ClassRoom::where('idClass', $idClass);
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
