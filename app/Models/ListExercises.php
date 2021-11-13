<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListExercises extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'idExam',
        'fileUrl',
        'submitted',
        'point',
        'description'
    ];
}
