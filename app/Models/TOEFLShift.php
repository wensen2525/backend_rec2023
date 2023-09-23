<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TOEFLShift extends Model
{
    use HasFactory;
    protected $table = 'toefl_shifts';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function term()
    {
        return $this->hasOne('App\Models\Term', 'id', 'term_id');
    }
}
