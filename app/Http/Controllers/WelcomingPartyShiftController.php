<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WelcomePartyShift;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MessageController;
use App\Http\Resources\welcomingParties\WelcomingPartyShiftResource;
use App\Http\Resources\welcomingParties\WelcomingPartyShiftCollection;

class WelcomingPartyShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('IsAdmin')->except('index');
    }
    public function index()
    {
        $partyShifts =WelcomePartyShift::all();
        if(!$partyShifts->count() ) return MessageController::error('No data available');
        return new WelcomingPartyShiftCollection($partyShifts);
    }

    public function show(string $id)
    {
        $partyShift = WelcomePartyShift::find($id);
        if(!$partyShift) return MessageController::error("Query not found");
        return new WelcomingPartyShiftResource($partyShift);
    }

    protected function validateWelcomePartyShift(Request $request)
    {
        $request->validate([
            'shift' => 'required|string',
            'quota' => 'required|integer',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateWelcomePartyShift($request);

        WelcomePartyShift::create([
            'shift' => $request->shift,
            'quota' => $request->quota,
        ]);

        return response()->json([
            'data'=>$request->all(),
            'status' => 'success',
            'message' => 'Welcoming Party Shift has successfully created'
        ],201);
    }

   
    public function update(Request $request, string $id)
    {
        $partyShift =WelcomePartyShift::find($id);
        if(!$partyShift) return MessageController::error("Query not found");
        $this->validateWelcomePartyShift($request);

        $partyShift->update([
            'shift' => $request->shift,
            'quota' => $request->quota,
        ]);

        return response()->json([
            'data'=> $request->all(),
            'status' => 'success',
            'message' => 'Welcoming Party Shift has successfully updated'
        ],200);
    }

     
    public function destroy(string $id)
    {
        $partyShift =WelcomePartyShift::find($id);
        if(!$partyShift) return MessageController::error("Query not found");
        $partyShift->delete();
        return response()->json([
            'data'=> $partyShift,
            'status' => 'success',
            'message' => 'Welcoming Party Shift has successfully deleted'
        ],200);
    }
}
