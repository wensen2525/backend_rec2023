<?php

namespace App\Exports;

use App\Models\BOMRecruitment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;


class BOMRecruitmentExport implements FromQuery, WithHeadings
{
    public $term;


    public function __construct ($region, $term)
    {
        $this->region = $region;
        $this->term = $term;
    }

    public function headings() : array
    {
        return ["ID","Name","NIM","Email","Phone Number","Line ID","First Preference","Second Preference","Third Preference","First Preference Reason","Second Preference Reason","Third Preference Reason","Created At","Updated At"];
    }

    public function query()
    {
        return BOMRecruitment::query()
        ->select(
            'bom_recruitments.id',
            'users.id',
            'users.name',
            'users.nim',
            'users.email',
            'users.phone_number',
            'users.line_id',
            'bom_divisions.name as firstPref',
            'bom_divisionsSecond.name as secondPref',
            'bom_divisionsThird.name as thirdPref',
            'first_preference_reason',
            'second_preference_reason',
            'third_preference_reason',
            'bom_recruitments.created_at',
            'bom_recruitments.updated_at',

        )
        ->join('users', 'users.id', 'bom_recruitments.user_id')
        ->join('bom_divisions', 'bom_divisions.id', 'first_preference_id', )
        ->join('bom_divisions as bom_divisionsSecond', 'bom_divisionsSecond.id', 'second_preference_id', )
        ->join('bom_divisions as bom_divisionsThird', 'bom_divisionsThird.id', 'third_preference_id', )
        ->where('bom_recruitments.region_id', $this->region)
        ->where('bom_recruitments.term_id', $this->term);

    }
}
