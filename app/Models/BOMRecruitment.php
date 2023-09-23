<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BOMRecruitment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'bom_recruitments';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function term()
    {
        return $this->hasOne('App\Models\Term', 'id', 'term_id');
    }

    public function region()
    {
        return $this->hasOne('App\Models\Region', 'id', 'region_id');
    }

    public function first_preference()
    {
        return $this->hasOne('App\Models\BomDivision', 'id', 'first_preference_id');
    }

    public function second_preference()
    {
        return $this->hasOne('App\Models\BomDivision', 'id', 'second_preference_id');
    }

    public function third_preference()
    {
        return $this->hasOne('App\Models\BomDivision', 'id', 'third_preference_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
