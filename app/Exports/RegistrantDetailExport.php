<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistrantDetailExport implements FromQuery, WithHeadings
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }


    public function headings() : array
    {
        return ["ID","Role","Region","Batch","NIM","Name","Major", "Gender", "Birth Place", 
        "Birth Date", "Address", "Domicile", "Email", "Phone Number", "Line Id", "Ticket Number",
        "Created At","Updated At"];
    }

    public function query() {
        return User::query()
        ->select('users.id','role','regions.region','batch','nim','name','majors.major_name', 'gender', 'birth_place', 
        'birth_date', 'address', 'domicile', 'email', 'phone_number', 'line_id', 'ticket_number',
        'users.created_at','users.updated_at')
        ->join('regions', 'regions.id', 'users.region_id')
        ->join('majors', 'majors.id', 'users.major_id')
        ->where([['role', '!=', 'ADMIN'], ['term_id', $this->term]]);
    }

}
