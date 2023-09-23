<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $fillable = [
        'role',
        'region_id',
        'term_id',
        'batch',
        'nim',
        'name',
        'major_id',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'domicile',
        'email',
        'phone_number',
        'line_id',
        'password',
        'campus_location',
    ];

    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    protected $with = ['region', 'term'];
  
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function toeflDetail()
    {
        return $this->hasOne('App\Models\TOEFLDetail', 'user_id', 'id')->latest()->withDefault();
    }

    public function memberPayment()
    {
        return $this->hasOne('App\Models\MemberPayment', 'user_id', 'id')->latest()->withDefault();
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id', 'id');
    }

    public function bomRecruitment()
    {
        return $this->hasOne('App\Models\BOMRecruitment', 'user_id', 'id')->withDefault();
    }

    public function talentRecruitment()
    {
        return $this->hasOne('App\Models\TalentRecruitment', 'nim', 'nim')->withDefault();
    }

    public function welcomingParty()
    {
        return $this->hasOne('App\Models\WelcomingParty', 'nim', 'nim')->latest()->withDefault();
    }

    public function major()
    {
        return $this->belongsTo('App\Models\Major', 'major_id', 'id');
    }

    public function term()
    {
        return $this->belongsTo('App\Models\Term', 'term_id', 'id');
    }
}
