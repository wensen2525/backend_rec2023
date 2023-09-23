<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TOEFLDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TOEFLAttendanceExport;
use App\Http\Controllers\MessageController;
use App\Http\Resources\toeflAttendances\ToeflAttendanceResource;
use App\Http\Resources\toeflAttendances\ToeflAttendanceCollection;

class ToeflAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['store','export']);
        $this->middleware('IsAdmin')->except(['store','export']);
    }

    public function getAll($term){
        $toeflDetails = TOEFLDetail::select('toefl_details.*')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->where('toefl_details.term_id', $term)->orderBy('users.name')->paginate(10);
        if(!$toeflDetails->count()) return MessageController::error("No data available");
        return new ToeflAttendanceCollection($toeflDetails);
    }

    public function index(){
        $toeflDetails = TOEFLDetail::withTrashed()->get();
        if(!$toeflDetails->count()) return MessageController::error("No data available");
        return new ToeflAttendanceCollection($toeflDetails);
    }

    public function show(string $id){
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail)return MessageController::error('Query not found');
        return new ToeflAttendanceResource($toeflDetail);
    }

    public function store(Request $request){
        $request->validate([
            'nim' => 'required|numeric|digits:10',
            'password' => 'required|string',
        ]);

        $currUser = User::where('nim', $request->nim)->first();

        if (!$currUser) {
            return MessageController::error('NIM not found');
        } else {
            $password = 'BNEC' . substr($currUser->nim, -6);
            if ($password != $request->password) return MessageController::error('Invalid Password');
            else $currToeflDetail = TOEFLDetail::where('user_id', $currUser->id)->first();
        }

        $currToeflDetail->update([
            'is_attend' => 1
        ]);


        return response()->json([
            'data' => $currToeflDetail,
            'status' => 'success',
            'message' => 'Attendace From has successfully submitted',

        ]);
    }
    
    public function export(){
        return Excel::download(new TOEFLAttendanceExport, 'TOEFLAttendances.xlsx');
    }
}
