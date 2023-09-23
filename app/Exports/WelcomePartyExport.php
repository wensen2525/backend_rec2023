<?php

namespace App\Exports;

use App\Models\WelcomingParty;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WelcomePartyExport implements WithHeadings, FromQuery
{
    /**
    * @return \Illuminate\Support\Collection
    */
   

    public function headings() : array
    {
        return ["Name","Campus Region","NIM", "Major","Email","Phone Number","Line ID","Instagram","Created at","Updated at"];
    }

    public function query()
    {
        return WelcomingParty::query()
        ->select(
            'name',
            'campus_location' ,
            'nim' ,
            'major_name',
            'email' ,
            'phone_number' ,
            'line_id' ,
            'instagram',
            'welcoming_parties.created_at',
            'welcoming_parties.updated_at',
        )
        ->join('majors','majors.id','welcoming_parties.major_id');
    }
}
