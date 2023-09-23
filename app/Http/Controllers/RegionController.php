<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\MessageController;
use App\Http\Resources\regions\RegionResource;
use App\Http\Resources\regions\RegionCollection;

class RegionController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function index()
    {
        $regions = Region::all();
        if (!$regions->count())return MessageController::error("No data available");
        return new RegionCollection($regions);
    }

    private function validateregion(Request $request)
    {
        $request->validate([
            'region' => 'required|string',
            'region_init' => 'required|string',
            'current_batch' => 'required|integer',
            'toefl_payment_detail' => 'required|string',
            'member_payment_detail' => 'required|string',
            'toefl_price_one' => 'required|integer',
            'toefl_price_two' => 'nullable|integer',
            'toefl_price_three' => 'nullable|integer',
            'member_price' => 'required|integer',
            'current_term_id' => 'required|integer',
            'status' => 'required|boolean'
        ]);
    }

    public function store(Request $request)
    {
        $this->validateregion($request);

        Region::create([
            'region' => $request->region,
            'region_init' => $request->region_init,
            'current_batch' => $request->current_batch,
            'toefl_payment_detail' => $request->toefl_payment_detail,
            'member_payment_detail' => $request->member_payment_detail,
            'toefl_price_one' => $request->toefl_price_one,
            'toefl_price_two' => $request->toefl_price_two,
            'toefl_price_three' => $request->toefl_price_three,
            'member_price' => $request->member_price,
            'current_term_id' => $request->current_term_id,
            'status' => $request->status
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Region has successfuly created'

        ],201);
    }
 
    public function show(string $id)
    {
        $region = Region::find($id);
        if(!$region)return MessageController::error("Query not found");
        return new RegionResource ($region);
    }

    public function update(Request $request, string $id)
    {
        $this->validateregion($request);
        $region = Region::find($id);
        if(!$region)return MessageController::error("Query not found");
        $region->update([
            'region' => $request->region,
            'region_init' => $request->region_init,
            'current_batch' => $request->current_batch,
            'toefl_payment_detail' => $request->toefl_payment_detail,
            'member_payment_detail' => $request->member_payment_detail,
            'toefl_price_one' => $request->toefl_price_one,
            'toefl_price_two' => $request->toefl_price_two,
            'toefl_price_three' => $request->toefl_price_three,
            'member_price' => $request->member_price,
            'current_term_id' => $request->current_term_id,
            'status' => $request->status
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Region has successfuly updated',
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $region = Region::find($id);
        if(!$region)return MessageController::error("Query not found");
        $region->delete();
        return response()->json([
            'data'=> $region,
            'status' => 'success',
            'message' => 'Region '. $region->region.' has successfuly deleted',

        ],200);
    }
}


