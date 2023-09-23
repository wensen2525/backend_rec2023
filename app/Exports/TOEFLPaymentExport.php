<?php

namespace App\Exports;

use App\Models\TOEFLPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TOEFLPaymentExport implements FromQuery, WithHeadings
{

    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
    
    public function headings(): array
    {
        return ["TOEFL Payment ID", "Region", "Registrant Name", "Phone Number","Email", "Line ID", "Shift", "Payment Type", "Provider Name", "Account Name", "Account Number", "Payment Amount", "Is Confirmed", "Created At", "Updated At"];
    }

    public function query()
    {
        return TOEFLPayment::query()
            ->select(
                'toefl_payments.id',
                'regions.region',
                'users.name as userName',
                'users.email',
                'users.phone_number',
                'users.email',
                'users.line_id',
                'toefl_shifts.shift',
                'payment_type',
                'payment_providers.name as providerName',
                'account_name',
                'account_number',
                'payment_amount',
                'is_confirmed',
                'toefl_payments.created_at',
                'toefl_payments.updated_at',
            )
            ->join('payment_providers', 'payment_providers.id', 'toefl_payments.provider_id')
            ->join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
            ->join('toefl_shifts', 'toefl_details.shift_id', 'toefl_shifts.id')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->join('regions', 'regions.id', 'users.region_id')
            ->where('toefl_details.term_id', $this->term);
    }
}
