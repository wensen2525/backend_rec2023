<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WelcomingParty extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'welcoming_parties';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function welcome_party_shifts()
    {
        return $this->hasOne('App\Models\WelcomePartyShift', 'id', 'shift_id');
    }
    
    public function major(){
        return $this->hasOne('App\Models\Major', 'id', 'major_id');
    }
}
