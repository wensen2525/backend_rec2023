<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentField extends Model
{
    use HasFactory;
    protected $table = 'talent_fields';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function first_preferences()
    {
        return $this->hasMany('App\Models\TalentRecruitment', 'first_talent_field_id', 'id');
    }

    public function second_preferences()
    {
        return $this->hasMany('App\Models\TalentRecruitment', 'second_talent_field_id', 'id');
    }
}
