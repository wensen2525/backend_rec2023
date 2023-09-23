<?php

namespace App\Exports;

use App\Models\MemberPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberPaymentExport implements FromQuery, WithHeadings
{

    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function headings() : array
    {
        return ["ID","Term ID","Payment Type","Provider Name","Account Name","Account Number","Payment Amount","Confirmed Status","Created At","Updated At","Name","NIM" ,"Email","Phone Number","Line ID","Region"];
    }

    public function query() 
    {
        return MemberPayment::query()
        ->select(
            'member_payments.id', 
            'terms.semester',
            'payment_type',
            'payment_providers.name as providerName',
            'account_name',
            'account_number',
            'payment_amount',
            'is_confirmed',
            'member_payments.created_at',
            'member_payments.updated_at',
            'users.name',
            'users.nim',
            'users.email',
            'users.phone_number',
            'users.line_id',
            'regions.region',
            
        )
        ->join('payment_providers', 'payment_providers.id', 'member_payments.provider_id')
        ->join('terms', 'terms.id', 'member_payments.term_id')
        ->join('users', 'users.id', 'member_payments.user_id')
        ->join('regions', 'regions.id', 'users.region_id')
        ->where('member_payments.term_id', $this->term);

    }
}
