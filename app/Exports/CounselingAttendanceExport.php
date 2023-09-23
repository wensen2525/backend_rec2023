<?php

namespace App\Exports;

use App\Models\Counseling;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CounselingAttendanceExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Counseling::all();
    }
    public function headings() : array
    {
        return ["ID","Region ID","Batch","NIM","Name","Created At","Updated At","Deleted At"];
    }
   
}
