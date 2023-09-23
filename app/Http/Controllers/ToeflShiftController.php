<?php

namespace App\Http\Controllers;

use App\Models\TOEFLShift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\toeflShifts\ToeflShiftResource;
use App\Http\Resources\toeflShifts\ToeflShiftCollection;

class ToeflShiftController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function index()
    {
        $toeflShifts = TOEFLShift::all();
        if(!$toeflShifts->count()) return MessageController::error('No available data');
        return new ToeflShiftCollection($toeflShifts);
    }

    public function show(string $id)
    {
        $toeflShift = TOEFLShift::find($id);
        if(!$toeflShift)return MessageController::error('Query not found');
        return new ToeflShiftResource($toeflShift);
    }

    public function updateVisibility(string $id){
        $toeflShift = TOEFLShift::find($id);
        if(!$toeflShift)return MessageController::error('Query not found');
        $toeflShift->update([
            'is_active'=> !$toeflShift->is_active
        ]);

        return response()->json([
            'is_active' => $toeflShift->is_active,
            'status' => 'success',
            'message' => "Toefl shift's visibility has successfuly updated"
        ]);
    }
 
    protected function validateToeflShift(Request $request)
    {
        $request->validate([
            'term_id' => 'required|integer',
            'shift' => 'required|string',
            'quota' => 'required|integer',
            'link' => 'nullable|string',
            'line_group' => 'nullable|string',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateToeflShift($request);
        TOEFLShift::create([
            'term_id' => $request->term_id,
            'shift' => $request->shift,
            'quota' => $request->quota,
            'link' => $request->link,
            'line_group' => $request->line_group,
        ]);
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Toefl Shift has successfully created'
        ],201);
    }
     
    public function update(Request $request, string $id)
    {
        $toeflShift = TOEFLShift::find($id);
        if(!$toeflShift)return MessageController::error('Query not found');
        $this->validateToeflShift($request);

        $toeflShift->update([
            'term_id' => $request->term_id,
            'shift' => $request->shift,
            'quota' => $request->quota,
            'link' => $request->link,
            'line_group' => $request->line_group,

        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Toefl Shift has successfully updated'
        ],200);
    }
     
    public function destroy(string $id)
    {
        $toeflShift = TOEFLShift::find($id);
        if(!$toeflShift)return MessageController::error('Query not found');
        $toeflShift->delete();
        return response()->json([
            'data' => $toeflShift,
            'status' => 'success',
            'message' => 'Toefl Shift has successfully deleted'
        ],200);
    }
}



