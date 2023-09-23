<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPayment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'member_payments';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function paymentProvider()
    {
        return $this->hasOne('App\Models\PaymentProvider', 'id', 'provider_id');
    }

    public function receiver()
    {
        return $this->hasOne('App\Models\User', 'id', 'receiver_id');
    }

    public function term()
    {
        return $this->hasOne('App\Models\Term', 'id', 'term_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
