<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TOEFLDetail extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'toefl_details';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function term()
    {
        return $this->hasOne('App\Models\Term', 'id', 'term_id')->withDefault();
    }

    public function toeflPayment()
    {
        return $this->belongsTo('App\Models\TOEFLPayment', 'payment_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withDefault();
    }

    public function toeflShift()
    {
        return $this->belongsTo('App\Models\TOEFLShift', 'shift_id', 'id');
    }

    public function requestedShift()
    {
        return $this->belongsTo('App\Models\TOEFLShift', 'request_edit_shift_id', 'id');
    }
}
