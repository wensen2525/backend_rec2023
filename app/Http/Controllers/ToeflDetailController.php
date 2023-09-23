<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\User;
use App\Models\TOEFLShift;
use App\Models\TOEFLDetail;
use Illuminate\Http\Request;
use App\Mail\EditTOEFLShiftMail;
use App\Exports\TOEFLDetailExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\MessageController;
use App\Http\Resources\toeflDetails\ToeflDetailResource;
use App\Http\Resources\toeflDetails\ToeflDetailCollection;

class ToeflDetailController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except(['export']);
        $this->middleware('IsAdmin')->except(['edit', 'show', 'update', 'export']);
        $this->middleware('IsShowed:ENV002')->only(['validateEnvironment']);
    }

     public function validateEnvironment(){
        return response()->json([
            'success'=> true,
            'message' => 'Page is opened'
        ]);
    }

    public function getAll($term = 7)
    {
        $toeflDetails = TOEFLDetail::select('toefl_details.*')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->where('toefl_details.term_id', $term)->orderBy('users.name')->paginate(10);
        if (!$toeflDetails->count()) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }

    public function getRecycleds($term = 7)
    {
        $toeflDetails = TOEFLDetail::onlyTrashed()->where('term_id', $term)->orderBy('id')->paginate(10);
        if (!$toeflDetails) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }

    public function getEditRequests()
    {
        $toeflDetails = TOEFLDetail::select('toefl_details.*')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->where('edit_status', '1')->orderBy('users.name')->paginate(10);
        if (!$toeflDetails) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }

    public function getConfirmeds($shift)
    {
        $toeflDetails = TOEFLDetail::select('toefl_details.*')
            ->join('toefl_payments','toefl_payments.id','toefl_details.payment_id')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->where('toefl_details.shift_id', $shift)
            ->where('toefl_payments.is_confirmed', 1)
            ->orderBy('users.name')
            ->paginate(10);
        if (!$toeflDetails) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }

    public function getPendings($shift)
    {
        $toeflDetails = TOEFLDetail::select('toefl_details.*')
            ->join('toefl_payments','toefl_payments.id','toefl_details.payment_id')
            ->join('users', 'users.id', 'toefl_details.user_id')
            ->where('toefl_details.shift_id', $shift)
            ->where('toefl_payments.is_confirmed', 0)
            ->orderBy('users.name')
            ->paginate(10);
        if (!$toeflDetails) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }

    public function index()
    {
        $toeflDetails = TOEFLDetail::withTrashed()->get();
        if (!$toeflDetails->count()) return MessageController::error('No data available');
        return new ToeflDetailCollection($toeflDetails);
    }
    
    public function show(string $id)
    {
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');
        return new ToeflDetailResource($toeflDetail);
    }
 
    public function update(Request $request, string $id)
    {
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');
        $request->validate([
            'request_edit_shift_id' => 'required|integer',
            'edit_reason' => 'required|string',
        ]);

        $toeflDetail->update([
            'request_edit_shift_id' => $request->request_edit_shift_id,
            'edit_reason' => $request->edit_reason,
            'edit_status' => 1
        ]);
        
        return response()->json([
            'data'=>$request->all(),
            'status' => "success",
            'message' => 'TOEFL Detail has successfully updated'
        ],200);
    }

    public function confirm (string $id){
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');
        
        if ($toeflDetail->request_edit_shift_id != NULL) {

            $oldToeflShift = TOEFLShift::find($toeflDetail->shift_id);

            $oldToeflShift->update([
                'quota' => $oldToeflShift->quota + 1
            ]);

            $toeflShift = TOEFLShift::find($toeflDetail->request_edit_shift_id);

            $toeflShift->update([
                'quota' => $toeflShift->quota - 1
            ]);

            $user = User::find($toeflDetail->user_id);

            $toeflDetail->update([
                'shift_id' => $toeflDetail->request_edit_shift_id,
                'edit_reason' => null,
                'edit_status' => 2,
                'request_edit_shift_id' => null
            ]);


            Mail::to($user->email)->send(new EditTOEFLShiftMail('accepted', $user->name));
            return response()->json([
                'status' => 'success',
                'message' => 'Request Accepted'
            ],200);
        }
        else{
            return MessageController::error('No toefl request shift');
        }
    }

    public function reject(string $id){
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');

        $user = User::find($toeflDetail->user_id);

        $toeflDetail->update([
            'edit_reason' => null,
            'edit_status' => -1,
            'request_edit_shift_id' => null
        ]);

        Mail::to($user->email)->send(new EditTOEFLShiftMail('rejected', $user->name));

        return response()->json([
            'status' => 'success',
            'message'=> 'Request Rejected'
        ],200);
    }

    public function updateDetail(Request $request, string $id){
        $toeflDetail = TOEFLDetail::find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');
        $request->validate([
            'shift_id' => 'required|integer',
            'score' => 'nullable|integer',
        ]);

        $oldToeflShift = TOEFLShift::find($toeflDetail->shift_id);

        $oldToeflShift->update([
            'quota' => $oldToeflShift->quota + 1
        ]);

        $toeflShift = TOEFLShift::find($request->shift_id);

        $toeflShift->update([
            'quota' => $toeflShift->quota - 1
        ]);

        $toeflDetail->update([
            'shift_id' => $request->shift_id,
            'score' => $request->score
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => "Toefl Shift has successfully updated"
        ],200);
    }

    public function destroy(TOEFLDetail $toeflDetail)
    {
        if(!$toeflDetail) return MessageController::error('Query not found');
        $toeflDetail->delete();
        $toeflShift = TOEFLShift::find($toeflDetail->shift_id);
        $toeflShift->update([
            'quota' => $toeflShift->quota + 1
        ]);

        return response()->json([
            'data' => $toeflDetail,
            'status' => 'success',
            'message' => 'Toefl Detail has successfully deleted'
        ],200);
    }

    public function restore($id)
    {
         
        $toeflDetail = TOEFLDetail::onlyTrashed()->find($id);
        if (!$toeflDetail) return MessageController::error('Query not found');
        $toeflDetail->restore();

        $toeflDetail = TOEFLDetail::find($id);
        $toeflShift = TOEFLShift::find($toeflDetail->shift_id);

        $newQuota = $toeflShift->quota - 1;

        $toeflShift->update([
            'quota' => $newQuota
        ]);

        return response()->json([
            'data' => $toeflDetail,
            'status' => 'success',
            'message' => 'Toefl Detail has successfully restored'
        ],200);
    }

    public function delete($id)
    {
        
        $toeflDetail = TOEFLDetail::onlyTrashed()->find($id);
        if(!$toeflDetail) return MessageController::error('Query not found');
        $toeflDetail->forceDelete();
        return response()->json([
            'data' => $toeflDetail,
            'status' => 'success',
            'message' => 'Toefl Detail has successfully deleted'
        ],200);
     
    }

    public function export($term = null)
    {
        if (!$term) abort(404);
        $termData = Term::find($term);
        return Excel::download(new TOEFLDetailExport($term), 'TOEFL Details - ' . $termData->semester . ' ' . $termData->year . '.xlsx');
         
    }
}
