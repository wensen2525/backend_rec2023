<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TOEFLQuiz extends Model
{
    use HasFactory;
    protected $table = 'toefl_quizzes';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];
}
