<?php

namespace App\Exports;

use App\Models\TalentRecruitment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TalentRecruitmentExport implements FromQuery, WithHeadings
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
    
    public function headings() : array
    {
        return ["ID","Term ID","Region","NIM","Name","Gender","Major ID",
        "Email", "Phone Number", "Alt Phone Number", "Line ID", "Birth Place", "Birth Date",
        "Address", "Allergy", "First Talent ID", "Second Talent ID",
        "Created At","Updated At","Deleted At"];
    }

    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return TalentRecruitment::all();
    // }

    public function query()
    {
        return TalentRecruitment::query()
            ->select(
                'talent_recruitments.id',
                'talent_recruitments.term_id',
                'talent_recruitments.region',
                'talent_recruitments.nim',
                'talent_recruitments.name',
                'talent_recruitments.gender',
                'majors.major_name',
                'talent_recruitments.email',
                'talent_recruitments.phone_number',
                'talent_recruitments.alt_phone_number',
                'talent_recruitments.line_id',
                'talent_recruitments.birth_place',
                'talent_recruitments.birth_date',
                'talent_recruitments.address',
                'talent_recruitments.allergy',
                'talent_fields.name as firstPref',
                'talent_fields_second.name as secondPref',
                'talent_recruitments.created_at',
                'talent_recruitments.updated_at',
                'talent_recruitments.deleted_at',
            )
            ->leftJoin('talent_fields', 'talent_fields.id', 'talent_recruitments.first_talent_field_id')
            ->leftJoin('talent_fields as talent_fields_second', 'talent_fields_second.id', 'talent_recruitments.second_talent_field_id')
            ->join('majors', 'majors.id', 'talent_recruitments.major_id')
            ->where('talent_recruitments.term_id', $this->term);
    }
}
