<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentRecruitment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'talent_recruitments';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function term(){
        return $this->hasOne('App\Models\Term', 'id', 'term_id');
    }

    public function region(){
        return $this->hasOne('App\Models\Region', 'id', 'region_id');
    }

    public function major(){
        return $this->hasOne('App\Models\Major', 'id', 'major_id');
    }

    public function first_talent_field(){
        return $this->hasOne('App\Models\TalentField', 'id', 'first_talent_field_id');
    }

    public function second_talent_field(){
        return $this->hasOne('App\Models\TalentField', 'id', 'second_talent_field_id');
    }
}
