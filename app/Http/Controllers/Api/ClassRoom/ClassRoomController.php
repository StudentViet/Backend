<?php

namespace App\Http\Controllers\Api\ClassRoom;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ClassRoom;
use App\Models\User;

class ClassRoomController extends Controller
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

    public function index()
    {
        $arrayClassRoom = [];
        $ClassRoom = new ClassRoom();
        $count = 0;
        foreach ($ClassRoom->get() as $row) {
            if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                $arrayClassRoom = Arr::prepend($arrayClassRoom, $row);
                $count++;
            }
        }
        return response()->json([
            'isError'   => $count != 0 ? false : true,
            'message'   => $count != 0 ? 'Lấy danh sách phòng học thành công' : 'Bạn chưa tham gia phòng học nào',
            'data'      => $count != 0 ? $arrayClassRoom : []
        ]);
    }

    public function join($idClass)
    {
        if (!$idClass) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Phòng học bạn đang tìm không hợp lệ'
            ]);
        } else {
            $ClassRoom = ClassRoom::where('idClass', $idClass);
            if ($ClassRoom->count() > 0) {
                $arrayStudent = json_decode($ClassRoom->first()->data, true);

                if (in_array(Auth::user()->email, $arrayStudent)) {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn đã trong phòng học này'
                    ]);
                } else {
                    $arrayStudent = Arr::prepend($arrayStudent, Auth::user()->email);
                    $ClassRoom->update([
                        'data'  => json_encode($arrayStudent)
                    ]);

                    return response()->json([
                        'isError'   => false,
                        'message'   => 'Tham gia lớp học thành công'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Không thể tìm thấy phòng học bạn đang tìm'
                ]);
            }
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:255',
            'data'  => 'required|json'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            if (Auth::user()->role_id == 1) {
                $arrayStudent = [Auth::user()->email];
                $data = json_decode($request->data, true);
                if ($data != []) {
                    foreach ($data as $row) {
                        $arrayStudent[] = $row;
                    }
                }
                $ClassRoom = new ClassRoom;
                $ClassRoom->idClass = \Illuminate\Support\Str::uuid();
                $ClassRoom->userId = Auth::user()->id;
                $ClassRoom->name = $request->name;
                $ClassRoom->data = json_encode($arrayStudent);
                $ClassRoom->save();
                return response()->json([
                    'isError' => false,
                    'message' => 'Tạo phòng học thành công'
                ]);
            } else {
                return response()->json([
                    'isError' => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }

    public function leave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idClass' => 'required|max:64',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $ClassRoom = ClassRoom::where('idClass', $request->idClass);
            if ($ClassRoom->count() > 0) {
                $arrayStudent = json_decode($ClassRoom->first()->data, true);

                if (!in_array(Auth::user()->email, $arrayStudent)) {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                } else {
                    $i = 0;
                    foreach ($arrayStudent as $key => $value) {
                        if ($value == Auth::user()->email) {
                            unset($arrayStudent[$i]);
                            break;
                        }
                        $i++;
                    }
                    $ClassRoom->update([
                        'data'  => json_encode($arrayStudent)
                    ]);

                    return response()->json([
                        'isError'   => false,
                        'message'   => 'Rời khỏi phòng học thành công'
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

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:32',
            'idClass' => 'required|max:64',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {

            if (Auth::user()->role_admin == 1) {
                $ClassRoom = ClassRoom::where('idClass', $request->idClass);
                if ($ClassRoom->count() > 0) {
                    if ($ClassRoom->where('userId', Auth::user()->id)->count() > 0) {
                        $arrayStudent = json_decode($ClassRoom->first()->data, true);

                        if (in_array($request->email, $arrayStudent)) {
                            return response()->json([
                                'isError'   => true,
                                'message'   => 'Thành viên này đã ở trong phòng học'
                            ]);
                        } else {
                            $arrayStudent = Arr::prepend($arrayStudent, $request->email);

                            $ClassRoom->update([
                                'data'  => json_encode($arrayStudent)
                            ]);
                            return response()->json([
                                'isError'   => false,
                                'message'   => 'Đã thêm thành viên vào lớp học'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Bạn không phải chủ phòng học này'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Không tìm thấy phòng học'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }

    public function kick(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:32',
            'idClass' => 'required|max:64',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {

            if (Auth::user()->role_admin == 1) {
                $ClassRoom = ClassRoom::where('idClass', $request->idClass);
                if ($ClassRoom->count() > 0) {
                    if ($ClassRoom->where('userId', Auth::user()->id)->count() > 0) {
                        $arrayStudent = json_decode($ClassRoom->first()->data, true);

                        if (!in_array($request->email, $arrayStudent)) {
                            return response()->json([
                                'isError'   => true,
                                'message'   => 'Không tìm thấy thành viên này'
                            ]);
                        } else {
                            $i = 0;
                            foreach ($arrayStudent as $key => $value) {
                                if ($value == Auth::user()->email) {
                                    unset($arrayStudent[$i]);
                                    break;
                                }
                                $i++;
                            }
                            $ClassRoom->update([
                                'data'  => json_encode($arrayStudent)
                            ]);
                            return response()->json([
                                'isError'   => false,
                                'message'   => 'Đã xóa thành viên này ra khỏi phòng học'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Bạn không phải chủ phòng học này'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Không tìm thấy phòng học'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }

    public function show($id)
    {
        if (!$id) {
            return response()->json([
                'isError'    => true,
                'message'    => 'Thiếu dữ liệu gửi lên'
            ]);
        } else {
            if (Auth::user()->role_id == 1) {
                $ClassRoom = ClassRoom::where([
                    'id'    => $id
                ]);
                if ($ClassRoom->count() > 0) {
                    return response()->json([
                        'isError'   => false,
                        'data'      => $ClassRoom->first()
                    ]);
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Không tìm thấy phòng học'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }

    public function destroy($id)
    {
        if (!$id) {
            return response()->json([
                'isError'    => true,
                'message'    => 'Thiếu dữ liệu gửi lên'
            ]);
        } else {
            if (Auth::user()->role_id == 1) {
                $ClassRoom = ClassRoom::where([
                    'id'    => $id
                ]);
                if ($ClassRoom->count() > 0) {
                    $ClassRoom->delete();
                    return response()->json([
                        'isError'   => false,
                        'message'   => 'Xóa phòng học thành công'
                    ]);
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Không tìm thấy phòng học'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }
}
