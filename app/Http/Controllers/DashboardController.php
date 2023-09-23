<?php

namespace App\Http\Controllers;

use App\Models\AccessControl;
use App\Models\BOMDivision;
use App\Models\ContactPerson;
use App\Models\Environment;
use App\Models\MemberPayment;
use App\Models\Region;
use App\Models\Term;
use App\Models\TOEFLDetail;
use App\Models\TOEFLPayment;
use App\Models\PaymentProvider;
use App\Models\TOEFLShift;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('IsAdmin');
    }

    public function search($term = 7)
    {
    
        $toeflRegistrants = TOEFLPayment::join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
            ->join('users', 'toefl_details.user_id', 'users.id')
            ->whereNull('toefl_payments.deleted_at')
            ->where('users.term_id', $term)
            ->where('is_confirmed', 1)
            ->get();
        
        $memberRegistrants = MemberPayment::join('users', 'member_payments.user_id', 'users.id')
            ->whereNull('member_payments.deleted_at')
            ->where('users.term_id', $term)
            ->where('is_confirmed', 1)
            ->get();
        
        $regions = Region::all();
        $toeflRegistrantSummaries = [];
        $memberRegistrantSummaries = [];

        foreach($regions as $region) {
            $tempSummaries = (object) [];
            $tempSummaries->regionName = $region->region;

            $tempSummaries->registrantsCount = $toeflRegistrants->where('region_id', $region->id)->count();
            $lastRegistered = $toeflRegistrants->where('region_id', $region->id)->sortByDesc('created_at')->first();
            if ($lastRegistered) {
                $tempSummaries->lastRegistered = date('Y-m-d', strtotime($lastRegistered->created_at));
            }
            $toeflRegistrantSummaries[] = $tempSummaries;
        }

        foreach($regions as $region) {
            $tempSummaries = (object) [];
            $tempSummaries->regionName = $region->region;

            $tempSummaries->registrantsCount = $memberRegistrants->where('region_id', $region->id)->count();
            $lastRegistered = $memberRegistrants->where('region_id', $region->id)->sortByDesc('created_at')->first();
            if ($lastRegistered) {
                $tempSummaries->lastRegistered = date('Y-m-d',strtotime($lastRegistered->created_at));
            }
            $memberRegistrantSummaries[] = $tempSummaries;
        }

        return response()->json([
            'data' => [
                'toeflRegistrants' => $toeflRegistrants,
                'memberRegistrants' => $memberRegistrants,
                'toeflRegistrantSummaries' => $toeflRegistrantSummaries,
                'memberRegistrantSummaries' => $memberRegistrantSummaries,
            ],
            'status' => 'success'
        ]);
    }
}