<?php

namespace App\Exports;

use App\Models\TOEFLDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TOEFLDetailExport implements FromQuery, WithHeadings
{

    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }


    public function headings(): array
    {
        return ["ID", "NIM", "Name", "Region", "Shift", "Is Attend", "Score"];
    }

    public function query()
    {
        return TOEFLDetail::query()
            ->select(
                'toefl_details.id',
                'users.nim',
                'users.name',
                'regions.region',
                'toefl_shifts.shift',
                'is_attend',
                'score',
            )
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->join('toefl_shifts', 'toefl_shifts.id', 'toefl_details.shift_id')
            ->join('regions', 'regions.id', 'users.region_id')
            ->where([['toefl_details.term_id', $this->term]]);
    }
}
