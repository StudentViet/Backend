<?php

namespace App\Http\Controllers\Api\ClassRoom;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ClassRoom;
use App\Models\Exam;
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

    public function getList()
    {
        $arrayClassRoom = [];
        $arrayStudent = [];
        $array = [];
        $count = 0;
        $ClassRoom = new ClassRoom();
        foreach ($ClassRoom->get() as $row) {
            if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                foreach (json_decode($row->data, true) as $rowData) {
                    $arrayStudent[$row->idClass][] = [
                        'name'  => User::whereEmail($rowData)->first()->name,
                        'email' => User::whereEmail($rowData)->first()->email,
                    ];
                }

                $count++;
            }
        }
        foreach ($ClassRoom->get() as $row) {
            if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                unset($row['data']);
                $row['data']    = $arrayStudent[$row->idClass];
                $row['exercises'] = Exam::where('idClass', $row->idClass)->count();
                $arrayClassRoom = Arr::prepend($arrayClassRoom, $row);
            }
        }
        return response()->json([
            'isError'   => $arrayClassRoom != [] ? false : true,
            'message'   => $arrayClassRoom != [] ? 'Lấy danh sách phòng học thành công' : 'Bạn chưa có phòng học nào',
            'data'      => $arrayClassRoom != [] ? $arrayClassRoom : []
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
                        'message'   => 'Tham gia phòng học thành công'
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
            'nameClass'  => 'required|string|max:255',
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
                        if (isset($row)) {
                            $arrayStudent[] = $row;
                        }
                    }
                }
                $idClass = \Illuminate\Support\Str::uuid();
                $ClassRoom = new ClassRoom;
                if ($ClassRoom->where('idClass', $idClass)->count() > 0) {
                    $idClass = \Illuminate\Support\Str::uuid();
                }
                $ClassRoom->idClass = $idClass;
                $ClassRoom->userId = Auth::user()->id;
                $ClassRoom->name = $request->nameClass;
                $ClassRoom->data = json_encode($arrayStudent);
                $ClassRoom->save();
                return response()->json([
                    'isError' => false,
                    'message' => 'Tạo phòng học thành công',
                    'idClass' => $idClass
                ]);
            } else {
                return response()->json([
                    'isError' => true,
                    'message' => 'Bạn không thể thực hiện hành động này'
                ]);
            }
        }
    }

    public function leave($idClass)
    {

        if (!$idClass) {

            return response()->json([
                'isError' => true,
                'message' => 'Trường mã phòng học không được bỏ trống'
            ]);
        } else {
            $ClassRoom = ClassRoom::where('idClass', $idClass);
            if ($ClassRoom->count() > 0) {
                $arrayStudent = json_decode($ClassRoom->first()->data, true);

                if (!in_array(Auth::user()->email, $arrayStudent)) {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                } else {
                    $i = 0;
                    foreach ($arrayStudent as $value) {
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
            if (User::where('email', $request->email)->count() > 0) {
                if (Auth::user()->role_id == 1) {
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
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Thành viên này không tồn tại'
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

            if (Auth::user()->role_id == 1) {
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
                                if ($value == $request->email) {
                                    unset($arrayStudent[$key]);
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
            $ClassRoom = ClassRoom::where([
                'idClass'    => $id
            ]);
            if ($this->CheckExists(Auth::user()->email, $id)) {
                $arrayStudent = [];
                $arrayClassRoom = [];
                $arrayExam = [];
                foreach ($ClassRoom->get() as $row) {
                    if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                        foreach (json_decode($row->data, true) as $rowData) {
                            $arrayStudent[$row->idClass][] = [
                                'name'  => User::whereEmail($rowData)->first()->name,
                                'email' => User::whereEmail($rowData)->first()->email,
                            ];
                        }
                    }
                }
                if (Exam::where('idClass', $id)->count() > 0) {
                    foreach (Exam::where('idClass', $id)->get() as $rowExam) {
                        if ($rowExam->idClass == $id) {
                            $arrayExam = Arr::prepend($arrayExam, $rowExam);
                        }
                    }
                }

                foreach ($ClassRoom->get() as $row) {
                    if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                        unset($row['data']);
                        $row['data']    = $arrayStudent[$row->idClass];
                        $row['exercises'] = $arrayExam;
                        $arrayClassRoom = Arr::prepend($arrayClassRoom, $row);
                    }
                }
                return response()->json([
                    'isError'   => $arrayClassRoom != [] ? false : true,
                    'message'   => $arrayClassRoom != [] ? 'Lấy danh sách phòng học thành công' : 'Bạn chưa có phòng học nào',
                    'data'      => $arrayClassRoom != [] ? $arrayClassRoom : []
                ]);
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Bạn chưa tham gia phòng học này'
                ]);
            }
        }
    }

    public function delete($id)
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
