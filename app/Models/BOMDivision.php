<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOMDivision extends Model
{
    use HasFactory;
    protected $table = 'bom_divisions';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];
    protected $with = ['region'];

    public function region ()
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_init');
    }

    public function first_preferences()
    {
        return $this->hasMany('App\Models\BomRecruitment', 'first_preference_id', 'id');
    }

    public function second_preferences()
    {
        return $this->hasMany('App\Models\BomRecruitment', 'second_preference_id', 'id');
    }

    public function third_preferences()
    {
        return $this->hasMany('App\Models\BomRecruitment', 'third_preference_id', 'id');
    }
}
