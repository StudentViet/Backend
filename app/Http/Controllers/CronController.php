<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\ClassRoom;
use App\Models\ListExercises;
use App\Models\Exam;
use App\Models\Schedule;
use App\Mail\OfferMail;
use Carbon\Carbon;

class CronController extends Controller
{
    public function index()
    {
        $classRoom = new ClassRoom;
        $Exam = new Exam;
        $ListExercises = new ListExercises;
        $Schedule = new Schedule;
        foreach ($classRoom->get() as $rowClass) {
            //lấy danh sách các lớp học
            foreach ($Exam->where('idClass', $rowClass->idClass)->get() as $rowExam) {
                //lấy danh sách bài tập
                foreach ($ListExercises->where('idExam', $rowExam->idExam) as $rowExercise) {
                    if (Carbon::now()->addDays(1) == $rowExercise->expires_at) {
                        $offer = [
                            'content'   => '# Chào ' . \App\Models\User::whereEmail($rowExercise->email)->first()->name . ' ngày mai là đến hạn nộp bài tập ' . $rowExam->name . '',
                        ];
                        Mail::to($rowExercise->email)->send(new OfferMail($offer));
                    }
                }
            }
        }
    }
}
