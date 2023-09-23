<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurClass extends Model
{
    use HasFactory;

    protected $table = 'our_classes';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}
