<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use HasFactory;
    protected $table = 'contact_persons';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function region()
    {
        return $this->hasOne('App\Models\Region', 'id', 'region_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
