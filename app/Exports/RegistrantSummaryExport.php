<?php

namespace App\Exports;

use App\Models\MemberPayment;
use App\Models\Region;
use App\Models\TOEFLPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistrantSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct ($term)
    {
        $this->term = $term;
    }

    public function collection()
    {
        $regions = Region::all();
        $result = [];

        $toeflRegistrants = TOEFLPayment::join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
                ->join('users', 'toefl_details.user_id', 'users.id')
                ->whereNull('toefl_payments.deleted_at')
                ->where('users.term_id', $this->term)
                ->where('is_confirmed', 1)
                ->get();
            
        $memberRegistrants = MemberPayment::join('users', 'member_payments.user_id', 'users.id')
            ->whereNull('member_payments.deleted_at')
            ->where('users.term_id', $this->term)
            ->where('is_confirmed', 1)
            ->get();

        foreach ($regions as $region) {
            $result[] = [
                'region' => $region->region,
                'toeflRegistrantsCount' => $toeflRegistrants->where('region_id', $region->id)->count(),
                'memberRegistrantsCount' => $memberRegistrants->where('region_id', $region->id)->count()
            ];  
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Region',
            'TOEFL Registrants',
            'Member Registrants',
        ];
    }
}
