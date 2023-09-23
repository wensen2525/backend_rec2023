<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomePartyShift extends Model
{
    use HasFactory;
    protected $table = 'welcome_party_shifts';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];
}
