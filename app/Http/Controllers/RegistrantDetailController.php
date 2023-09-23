<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\User;
use App\Models\TOEFLShift;
use App\Models\TOEFLDetail;
use App\Models\TOEFLPayment;
use Illuminate\Http\Request;
use App\Models\MemberPayment;
use App\Models\BOMRecruitment;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RegistrantDetailExport;
use App\Http\Resources\users\UserResource;
use App\Http\Controllers\MessageController;
use App\Http\Resources\users\UserCollection;

class RegistrantDetailController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('export');
        $this->middleware('IsAdmin')->except('show', 'export');
    }
    
    public function getAll($term){
        $registrants = User::where([['role', '!=', 'ADMIN'],['term_id', $term]])->orderBy('name')->paginate(10);
        if(!$registrants->count()) return MessageController::error("No data available");
        return new UserCollection($registrants);
    }

    public function index(){
        // isset($_GET['term_id']) ? $term = $_GET['term_id'] : $term = 0;
        $registrants = User::where([['role', '!=', 'ADMIN']])->get();
        if(!$registrants->count()) return MessageController::error("No data available");
        return new UserCollection($registrants);
    }

    public function show(string $id ){
        $registrant = User::find($id);
        if(!$registrant) return MessageController::error('Query not found');
        return new UserResource ($registrant);
    }

    public function update(Request $request, string $id){
        $request->validate([
            'term_id' => 'required|integer',
            'region_id' => 'required|integer',
            'campus_location' => 'required|string',
            'batch' => 'required|integer|min:1',
            'nim' => 'required|numeric|digits:10',
            'name' => 'required|string',
            'major_id' => 'required|integer',
            'gender' => 'required|string',
            'birth_place' => 'required|string',
            'birth_date' => 'required|date_format:Y-m-d',
            'address' => 'required|string',
            'domicile' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
            'line_id' => 'required|string',
        ]);

        $registrant = User::find($id);
        $registrant->update([
            'term_id' => $request->term_id,
            'region_id' => $request->region_id,
            'campus_location' => $request->campus_location,
            'batch' => $request->batch,
            'nim' => $request->nim,
            'name' => ucwords($request->name),
            'major_id' => $request->major_id,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'domicile' => $request->domicile,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'line_id' => $request->line_id,
        ]);
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Registrant detail data has successfully updated'
        ],200);
    }

    public function destroy(string $id){
        $selectedUser = User::find($id);
        $bomRecs = BOMRecruitment::withTrashed()->where('user_id', $selectedUser->id)->get();
        $memberPayments = MemberPayment::withTrashed()->where('user_id', $selectedUser->id)->get();
        $toeflDetail = TOEFLDetail::withTrashed()->where('user_id', $selectedUser->id)->first();
        if ($bomRecs->count() > 0) {
            foreach ($bomRecs as $bomRec) {
                $bomRec->forceDelete();
            }
        }

        if($memberPayments->count() > 0) {
            foreach ($memberPayments as $memberPayment) {
                $memberPayment->forceDelete();
            }
        }

        if ($toeflDetail) {
            $toeflPayment = TOEFLPayment::withTrashed()->where('id', $toeflDetail->payment_id);
            $selectedShift = TOEFLShift::where('id', $toeflDetail->shift_id)->first();

            if($toeflPayment->count() == 1) {
                $toeflPayment->first()->forceDelete();
            }else if ($toeflPayment->count() > 1) {
                foreach ($toeflPayment->get() as $toeflPaymentData) {
                    $toeflPaymentData->forceDelete();
                }
            }
            $toeflDetail->forceDelete();
            $selectedShift->update([
                'quota' => $selectedShift->quota += 1
            ]);
        }
        $selectedUser->delete();
        return response()->json([
            'data' => $selectedUser,
            'status' => 'success',
            'message' => 'User data successfully deleted'
        ],200);
    }

    public function export($term = NULL){
        if (!$term) abort(404);
        $termData = Term::find($term);

        return Excel::download(new RegistrantDetailExport($term), 'Registrant Details - '  . $termData->semester . ' ' . $termData->year .  '.xlsx');
    }
    
}
