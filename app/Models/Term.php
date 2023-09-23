<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;
    protected $table = 'terms';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public static function getCurrentActiveTerm ()
    {
        return Region::where('id', auth()->user()->region_id)->first()->current_term_id;
    }

    public static function getLatestTerms ()
    {
        return Self::orderBy('id', 'DESC')->take(5)->get();
    }
}
