<?php

namespace App\Http\Controllers\Api\ClassRoom;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\ClassRoom;
use App\Models\ListExercises;
use App\Models\Exam;
use Carbon\Carbon;

class ExamController extends Controller
{

    protected function CheckExistFile($file_name)
    {
        $ListExercises = new ListExercises();
        if ($ListExercises->count() > 0) {
            foreach ($ListExercises->get() as $row) {
                $arrayFile = json_decode($row->fileUrl, true);
                if (in_array($file_name, $arrayFile)) {
                    if (Storage::disK('answer')->exists($file_name)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

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

                            if ($ListExercises->count() <= 0) {

                                return response()->json([
                                    'isError'   => true,
                                    'message'   => 'Không thể hoàn tác do bạn chưa nộp bài'
                                ]);
                            } else {
                                Storage::disk('answer')->delete($ListExercises->first()->fileUrl);
                                $ListExercises->delete();
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

    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idExam'   => 'required|string|max:255',
            'file'  => 'required|string',
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
                    if (Carbon::now()->lessThan($Exam->first()->expires_at)) {
                        $ListExercises = ListExercises::where('idExam', $request->idExam)->where('email', Auth::user()->email);

                        $i = 0;
                        $index = 0;
                        $isContinue = 'true';
                        if ($isContinue == 'true') {
                            $arrayFile = json_decode($ListExercises->first()->fileUrl, true);
                            foreach ($arrayFile as $rowFile) {
                                if ($request->file == $rowFile) {
                                    $isContinue = 'false';
                                    $index = $arrayFile[$i];
                                    unset($arrayFile[$i]);

                                    break;
                                }
                                $i++;
                            }
                            if ($isContinue == 'true') {
                                return response()->json([
                                    'isError'   => true,
                                    'message'   => 'Không tìm thấy file bài làm của bạn'
                                ]);
                            } else {
                                Storage::disk('answer')->delete($index);
                                $ListExercises->update([
                                    'fileUrl'   => json_encode($arrayFile)
                                ]);
                                return response()->json([
                                    'isError'   => false,
                                    'message'   => 'Xóa file bài làm thành công',
                                    $arrayFile
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
                        'message'   => 'Bạn chưa tham gia phòng học này'
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

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idExam'   => 'required|string|max:255',
            'file'  => 'required|mimes:doc,docx,pdf,txt,png,jpg,gif,tiff,bmp,psd|max:2048',
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
                    if (Carbon::now()->lessThan($Exam->first()->expires_at)) {
                        if ($request->file('file')) {

                            $file_name = \Illuminate\Support\Str::random(8) . '_' . str_replace(' ', '', $request->file('file')->getClientOriginalName());
                            $ListExercises = new ListExercises;
                            $arrayFile = [];
                            if ($ListExercises->where('idExam', $request->idExam)->where('email', Auth::user()->email)->count() > 0) {
                                $ListExercises = $ListExercises->where('idExam', $request->idExam)->where('email', Auth::user()->email);
                                $arrayFile = json_decode($ListExercises->first()->fileUrl, true);
                                if ($this->CheckExistFile($file_name)) {
                                    return response()->json([
                                        'isError'   => true,
                                        'message'   => 'File này đã tồn tại trong hệ thống'
                                    ]);
                                } else {
                                    if (Storage::disk('answer')->exists($file_name)) {
                                        return response()->json([
                                            'isError'   => true,
                                            'message'   => 'File này đã tồn tại trong hệ thống'
                                        ]);
                                    } else {
                                        $arrayFile = Arr::prepend($arrayFile, $file_name);
                                        $ListExercises->update([
                                            'fileUrl'   => json_encode($arrayFile)
                                        ]);

                                        $request->file('file')->storeAs(
                                            'answer',
                                            $file_name,
                                            'document'
                                        );

                                        return response()->json([
                                            'isError' => false,
                                            'message' => 'Tải file bài tập lên thành công'
                                        ]);
                                    }
                                }
                            } else {
                                $ListExercises->create([
                                    'idExam'    => $request->idExam,
                                    'fileUrl'   => json_encode([$file_name]),
                                    'email'     => Auth::user()->email,
                                    'submitted' => false
                                ]);
                                $request->file('file')->storeAs(
                                    'answer',
                                    $file_name,
                                    'document'
                                );

                                return response()->json([
                                    'isError' => false,
                                    'message' => 'Tải file bài tập lên thành công'
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
                        'message'   => 'Bạn chưa tham gia phòng học này'
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

    public function returnExercise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idExam'   => 'required|string|max:255',
            'id'       => 'required|integer',
            'point'    => 'required|min:1|max:11',
            'description'   => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {
            $Exam = Exam::where('idExam', $request->idExam);
            if ($Exam->count() > 0) {
                $classRoom = ClassRoom::where('idClass', $Exam->first()->idClass);
                if ($classRoom->count() > 0) {
                    if ($this->CheckExists(Auth::user()->email, $Exam->first()->idClass)) {
                        if (Auth::user()->role_id == 1) {
                            if (Auth::user()->id == $classRoom->userId) {
                                $ListExercises = ListExercises::where('id', $request->id);
                                if ($ListExercises->count() > 0) {
                                    $ListExercises->update([
                                        'point' => $request->point,
                                        'description'   => $request->description
                                    ]);
                                    return response()->json([
                                        'isError'   => true,
                                        'messgae'   => 'Đã trả bài tập về cho học sinh'
                                    ]);
                                } else {
                                    return response()->json([
                                        'isError'   => true,
                                        'message'   => 'Không tìm thấy bài làm của học sinh này'
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
                                'message' => 'Bạn không thể thực hiện hành động này'
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
                    'message'   => 'Bài tập này không tòn tại'
                ]);
            }
        }
    }

    public function sendExercise(Request $request)
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
                            $ListExercises = ListExercises::where('idExam', $request->idExam)->where('email', Auth::user()->email);
                            if ($ListExercises->count() > 0) {
                                $ListExercises->update([
                                    'submitted' => true,
                                    'thoigiannop' => now()
                                ]);
                                return response()->json([
                                    'isError'   => false,
                                    'message'   => 'Nộp bài thành công'
                                ]);
                            } else {
                                return response()->json([
                                    'isError'   => true,
                                    'message'   => 'Bạn chưa tải lên file bài làm nào'
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
            'file'  => 'required|mimes:doc,docx,pdf,txt,png,gif,jpg,tiff,bmp|max:2048',
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
            $count = count(explode(',', trim($request->idClass)));
            $i = 0;
            foreach (explode(',', trim($request->idClass)) as $rowClass) {
                if ($classRoom->where('idClass', $rowClass)->count() > 0) {
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
                                $Exam->idClass = $rowClass;
                                $Exam->idExam = $idExam;
                                $Exam->fileUrl = $filename;
                                $Exam->expires_at = $request->expires_at;
                                $Exam->save();
                                $i++;
                                if ($count == $i) {
                                    return response()->json([
                                        'isError'   => false,
                                        'message'   => 'Tạo bài tập thành công'
                                    ]);
                                }
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
    }

    public function downloadFile($filename)
    {
        $file = Storage::disk('document')->path($filename);
        return response()->download($file);
    }

    public function downloadFileExercise($filename)
    {
        return response()->download(Storage::disk('answer')->path($filename));
    }

    public function show($idClass)
    {

        if (ClassRoom::where('idClass', $idClass)->count() > 0) {

            if (Exam::where(['idClass' => $idClass])->count() > 0) {
                $Exam = Exam::where('idClass', $idClass);
                if ($this->CheckExists(Auth::user()->email, $idClass)) {
                    if (Auth::user()->role_id == 1) {
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Lấy bài tập thành công',
                            'data'      => $Exam->first()
                        ]);
                    } else {
                        if (Carbon::now()->lessThan($Exam->first()->expires_at)) {
                            $ListExercises = ListExercises::where('idExam', $Exam->first()->idExam)->where('email', Auth::user()->email);
                            $arraySubmitted = [];
                            $arrayExercise = [];
                            foreach ($ListExercises->get() as $row) {
                                $arraySubmitted[$row->idExam] = $row->submitted == "1" ? true : false;
                            }
                            foreach ($Exam->get() as $row) {
                                $row['submitted'] = $arraySubmitted != [] ? $arraySubmitted[$row->idExam] : false;
                                $arrayExercise[] = $row;
                            }
                            return response()->json([
                                'isError'   => false,
                                'message'   => 'Lấy danh sách bài tập thành công',
                                'data'      => $arrayExercise
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
                    'message'   => 'Bài tập này không tồn tại'
                ]);
            }
        } else {

            return response()->json([
                'isError'   => true,
                'message'   => 'Phòng học này không tồn tại'
            ]);
        }
    }

    public function listStudentDoExercise($idExam)
    {
        if (!$idExam) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Thiếu thông tin gửi lên'
            ]);
        } else {
            $classRoom = new ClassRoom;
            if (Exam::where('idExam', $idExam)->count() > 0) {
                $Exam = Exam::where('idExam', $idExam);
                $classRoom = $classRoom->where('idClass', $Exam->first()->idClass);
                if ($classRoom->count() > 0) {
                    if ($classRoom->first()->userId == Auth::user()->id) {
                        $ListExercises = ListExercises::where('idExam', $idExam);
                        $array = [];
                        foreach ($ListExercises->get() as $row) {
                            $arrayFile = json_decode($row->fileUrl, true);
                            $data = [
                                'name'  => \App\Models\User::whereEmail($row->email)->first()->name,
                                'email' => $row->email,
                                'file'  => $arrayFile
                            ];
                            $array = Arr::prepend($array, $data);
                        }
                        return response()->json([
                            'isError'   => false,
                            'message'   => 'Lấy danh sách học sinh làm bài thành công',
                            'data'      => $array
                        ]);
                    } else {
                        return response()->json([
                            'isError'   => true,
                            'message'   => 'Bạn không phải chủ phòng học này'
                        ]);
                    }
                } else {
                    return response()->json([
                        'isError'   => true,
                        'message'   => 'Phòng học không tồn tại'
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

    public function delete($idExam)
    {
        if (!$idExam) {
            return response()->json([
                'isError'   => true,
                'message'   => 'Thiếu thông tin gửi lên'
            ]);
        }
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
