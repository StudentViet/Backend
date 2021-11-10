<?php

namespace App\Http\Controllers\Api\ClassRoom;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Models\ClassRoom;
use App\Models\ListExercises;
use App\Models\Exam;
use Carbon\Carbon;

class ExamController extends Controller
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
        $arrayExam = [];
        $classRoom = new ClassRoom();
        $Exam = new Exam;
        $count = 0;
        foreach ($classRoom->get() as $row) {
            if ($this->CheckExists(Auth::user()->email, $row->idClass)) {
                $arrayClassRoom = Arr::prepend($arrayClassRoom, $row->idClass);
                $count++;
            }
        }
        foreach ($arrayClassRoom as $row) {
            if ($classRoom->where('idClass', $row)->count() > 0) {
                foreach ($Exam->get() as $rowExam) {
                    if ($rowExam->idClass == $row) {
                        $arrayExam = Arr::prepend($arrayExam, $rowExam);
                    }
                }
            }
        }
        if ($count == 0) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Bạn chưa tham gia phòng học nào'
            ]);
        } else {
            return response()->json([
                'isError'   => $arrayExam != [] ? false : true,
                'message'   => $arrayExam != [] ? 'Lấy danh sách bài tập thành công' : 'Hiện tại chưa có bài tập nào',
                'data'      => $arrayExam != [] ? $arrayExam : [],
            ]);
        }
    }

    public function cancelSendExercise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idExam'   => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $Exam = Exam::where('idExam', $request->idExam);
            if ($Exam->count() > 0) {
                if ($this->CheckExists(Auth::user()->email, $Exam->first()->idClass)) {
                    if (Auth::user()->role_id != 1) {
                        if (Carbon::now()->lessThan($Exam->first()->expires_at)) {

                            $ListExercises = ListExercises::where([
                                'idExam'    => $request->idExam,
                                'email'     => Auth::user()->email,
                            ]);

                            if ($ListExercises->count() > 0) {

                                return response()->json([
                                    'isError'   => true,
                                    'message'   => 'Bạn đã nộp bài tâp này từ trước rồi'
                                ]);
                            } else {
                                $ListExercises->submitted = false;
                                $ListExercises->save();
                                return response()->json([
                                    'isError'   => false,
                                    'message'   => 'Hoàn tác nộp bài thành công'
                                ]);
                            }
                        } else {
                            return response()->json([
                                'isError'   => true,
                                'message'   => 'Đã quá thời hạn nộp bài'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Giáo viên không thể thực hiện'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Bài tập này không tòn tại'
                ]);
            }
        }
    }

    public function delExerciseFile(Request $request)
    {
    }

    public function sendExercise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idExam'   => 'required|string|max:255',
            'file'  => 'required|mimes:doc,docx,pdf,txt|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $Exam = Exam::where('idExam', $request->idExam);
            if ($Exam->count() > 0) {
                if ($this->CheckExists(Auth::user()->email, $Exam->first()->idClass)) {
                    if (Auth::user()->role_id != 1) {
                        if (Carbon::now()->lessThan($Exam->first()->expires_at)) {
                            if ($request->file('file')) {
                                $ListExercises = new ListExercises;
                                if ($ListExercises->where([
                                    'idExam'    => $request->idExam,
                                    'email'     => Auth::user()->email,
                                ])->count() <= 0) {
                                    $ListExercises->idExam = $request->idExam;
                                    $filename = \Illuminate\Support\Str::random(8) . '_' . str_replace(' ', '', $request->file('file')->getClientOriginalName());
                                    $request->file('file')->storeAs('documents/answer', $filename);
                                    $ListExercises->fileUrl = $filename;
                                    $ListExercises->email = Auth::user()->email;
                                    $ListExercises->submitted = true;
                                    $ListExercises->save();
                                    return response()->json([
                                        'isError'   => false,
                                        'message'   => 'Nộp bài tập thành công'
                                    ]);
                                } else {
                                    return response()->json([
                                        'isError'   => true,
                                        'message'   => 'Bạn đã nộp bài tâp này từ trước rồi'
                                    ]);
                                }
                            }
                        } else {
                            return response()->json([
                                'isError'   => true,
                                'message'   => 'Đã quá thời hạn nộp bài'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Giáo viên không thể làm bài'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Bài tập này không tòn tại'
                ]);
            }
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nameExercise'  => 'required|string|max:255',
            'file'  => 'required|mimes:doc,docx,pdf,txt|max:2048',
            'idClass'   => 'required|max:255',
            'expires_at' => 'required|date'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $classRoom = new ClassRoom;
            if ($classRoom->where('idClass', $request->idClass)->count() > 0) {
                if (Auth::user()->role_id == 1) {
                    if ($classRoom->where('userId', Auth::user()->id)->count() > 0) {
                        if ($request->file('file')) {
                            $Exam = new Exam;
                            $idExam = \Illuminate\Support\Str::uuid();
                            if ($Exam->where('idExam', $idExam)->count() > 0) {
                                $idExam = \Illuminate\Support\Str::uuid();
                            }
                            $filename = \Illuminate\Support\Str::random(8) . '_' . str_replace(' ', '', $request->file('file')->getClientOriginalName());
                            $request->file('file')->storeAs('documents', $filename);
                            $Exam->name = $request->nameExercise;
                            $Exam->idClass = $request->idClass;
                            $Exam->idExam = $idExam;
                            $Exam->fileUrl = $filename;
                            $Exam->expires_at = $request->expires_at;
                            $Exam->save();
                            return response()->json([
                                'isError'   => false,
                                'message'   => 'Tạo bài tập thành công'
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
                        'isError' => true,
                        'message' => 'Bạn không thể thực hiện hành động này'
                    ]);
                }
            } else {
                return response()->Json([
                    'isError'   => true,
                    'message'   => 'Phòng học này không tồn tại'
                ]);
            }
        }
    }

    public function downloadFile($filename)
    {
        $file_path = storage_path('app/documents') . "/" . $filename;

        return Response::download($file_path);
    }

    public function show($idExam)
    {
        $Exam = new Exam;
        $classRoom = new ClassRoom;
        if ($Exam->where('idExam', $idExam)->count() > 0) {
            $Exam = Exam::where('idExam', $idExam);
            if ($classRoom->where('idClass', $Exam->first()->idClass)->count() > 0) {
                if ($this->CheckExists(Auth::user()->email, $Exam->first()->idClass)) {
                    if (Auth::user()->role_id == 1) {
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Lấy bài tập thành công',
                            'data'      => $Exam->first()->fileUrl
                        ]);
                    } else {
                        if (Carbon::now()->lessThan($Exam->first()->expires_at)) {
                            return response()->json([
                                'isError'   => false,
                                'message'   => 'Lấy bài tập thành công',
                                'data'      => $Exam->first()->fileUrl
                            ]);
                        } else {
                            return response()->json([
                                'isError'   => true,
                                'message'   => 'Đã hết thời hạn nộp bài'
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Phòng học này không tồn tại'
                ]);
            }
        } else {
            return response()->json([
                'isError'   => true,
                'message'   => 'Bài tập này không tồn tại'
            ]);
        }
    }

    public function delete($idExam)
    {
        $Exam = new Exam;
        $classRoom = new ClassRoom;
        if ($Exam->where('idExam', $idExam)->count() > 0) {
            $Exam = Exam::where('idExam', $idExam);
            if ($classRoom->where('idClass', $Exam->first()->idClass)->count() > 0) {
                if ($this->CheckExists(Auth::user()->email, $Exam->first()->idClass)) {
                    if (Auth::user()->role_id == 1) {
                        Storage::disk('document')->delete($Exam->first()->fileUrl);
                        $Exam->delete();
                        return response()->json([
                            'isError'    => false,
                            'message'    => 'Đã xóa bài tập này'
                        ]);
                    } else {
                        return response()->json([
                            'isError'     => true,
                            'message'     => 'Bạn không thể thực hiện hành động này'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Bạn chưa tham gia phòng học này'
                    ]);
                }
            } else {
                return response()->json([
                    'isError'   => true,
                    'message'   => 'Phòng học này không tồn tại'
                ]);
            }
        } else {
            return response()->json([
                'isError'   => true,
                'message'   => 'Bài tập này không tồn tại'
            ]);
        }
    }
}
