<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TOEFLPayment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'toefl_payments';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $guarded = [];

    public function paymentProvider()
    {
        return $this->hasOne('App\Models\PaymentProvider', 'id', 'provider_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'receiver_id');
    }

    public function details()
    {
        return $this->hasMany('App\Models\TOEFLDetail', 'payment_id', 'id');
    }
}

  
