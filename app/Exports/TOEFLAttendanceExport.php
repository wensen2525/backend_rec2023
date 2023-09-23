<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\TOEFLDetail;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TOEFLAttendanceExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TOEFLDetail::all();
    }
    public function headings() : array
    {
        return ["ID","Term ID","Payment ID","User ID","Shift ID","Edit Status","Edit Reason","Attendance Status","Score","Requested Edit Shift ID","Created At","Updated At","Deleted At"];
    }
}
