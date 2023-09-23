<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counseling extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'counselings';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function region()
    {
        return $this->hasOne('App\Models\Region', 'id', 'region_id');
    }
}
