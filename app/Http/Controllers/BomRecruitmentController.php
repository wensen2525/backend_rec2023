<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\Region;
use App\Models\BOMDivision;
use Illuminate\Http\Request;
use App\Models\BOMRecruitment;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BOMRecruitmentExport;
use App\Http\Controllers\MessageController;
use App\Http\Resources\bomRecruitments\BomRecruitmentResource;
use App\Http\Resources\bomRecruitments\BomRecruitmentCollection;

class BomRecruitmentController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('export');
        $this->middleware('IsAdmin')->only('index', 'delete', 'getAll', 'getRecycleds', 'getSummaries');
        $this->middleware('IsMember')->only('store', 'update');
        $this->middleware('IsShowed:ENV005')->only(['validateEnvironment']);
    } 

    public function validateEnvironment(){
        return response()->json([
            'success'=> true,
            'message' => 'Page is opened'
        ]);
    }
    
    public function getSummaries($term){
        $divisions = BOMDivision::where('region_id', auth()->user()->region->region_init)->get();
        $recruitmentSummaries = [];

            foreach ($divisions as $division) {
                $tempSummaries = (object) [];
                $tempSummaries->divName = $division->name;
                $tempSummaries->firstPrefCount = BOMDivision::join('bom_recruitments', 'bom_recruitments.first_preference_id', 'bom_divisions.id')
                    ->where([['bom_recruitments.region_id', auth()->user()->region->region_init],['bom_divisions.id', $division->id], ['term_id', $term]])
                    ->count();
                $tempSummaries->secondPrefCount = BOMDivision::join('bom_recruitments', 'bom_recruitments.second_preference_id', 'bom_divisions.id')
                    ->where([['bom_recruitments.region_id', auth()->user()->region->region_init],['bom_divisions.id', $division->id], ['term_id', $term]])
                    ->count();
                $tempSummaries->thirdPrefCount = BOMDivision::join('bom_recruitments', 'bom_recruitments.third_preference_id', 'bom_divisions.id')
                    ->where([['bom_recruitments.region_id', auth()->user()->region->region_init],['bom_divisions.id', $division->id], ['term_id', $term]])
                    ->count();
                $tempSummaries->region = $division->region_id;
                $tempSummaries->totalRegistrants = $tempSummaries->firstPrefCount + $tempSummaries->secondPrefCount + $tempSummaries->thirdPrefCount;

                $recruitmentSummaries[] = $tempSummaries;
            }
            return response()->json([
                'data' => $recruitmentSummaries,
                'status' => 'Success',
            ]);
    }

    public function getAll($term){
        $bomRecruitments = BOMRecruitment::select('bom_recruitments.*')
            ->join('users', 'users.id', 'bom_recruitments.user_id')
            ->where('bom_recruitments.region_id', auth()->user()->region->region_init)
            ->where('bom_recruitments.term_id', $term)->orderBy('users.name')->paginate(10);
        if(!$bomRecruitments->count() ) return MessageController::error('No data available');
        return new BomRecruitmentCollection($bomRecruitments);
    }

    public function getRecycleds($term){
        $bomRecruitments = BOMRecruitment::select('bom_recruitments.*')
            ->join('users', 'users.id', 'bom_recruitments.id')
            ->where('bom_recruitments.term_id', $term)->onlyTrashed()->paginate(10);
        if(!$bomRecruitments->count() ) return MessageController::error('No data available');
        return new BomRecruitmentCollection($bomRecruitments);
    }

    public function index()
    {
        $bomRecruitments = BOMRecruitment::withTrashed()->get();
        if(!$bomRecruitments->count() ) return MessageController::error('No data available');
        return new BomRecruitmentCollection($bomRecruitments);
    }
    
    public function show(string $id)
    {
        $bomRecruitment = BOMRecruitment::find($id);
        if(!$bomRecruitment) return MessageController::error("Query not found");
        return new BomRecruitmentResource($bomRecruitment);
    }
    protected function validateBOMRecruitment(Request $request)
    {
        return $request->validate([

            'first_preference_id' => [

                'required', 'integer', Rule::notIn([$request->second_preference_id, $request->third_preference_id]),

            ],

            'second_preference_id' =>

            [

                'required', 'integer', Rule::notIn([$request->first_preference_id, $request->third_preference_id]),

            ],

            'third_preference_id' =>

            [

                'required', 'integer', Rule::notIn([$request->first_preference_id, $request->second_preference_id]),

            ],

            'first_preference_reason' => 'required|string',

            'second_preference_reason' => 'required|string',

            'third_preference_reason' => 'required|string',

        ], [

            'first_preference_id.not_in' => 'The selected 1st division preference cannot be the same as other preference.',

            'second_preference_id.not_in' => 'The selected 2nd division preference cannot be the same as other preference.',

            'third_preference_id.not_in' => 'The selected 3rd division preference cannot be the same as other preference.',

            'first_preference_reason' => 'You must state your reason for 1st division preference.',

            'second_preference_reason' => 'You must state your reason for 2nd division preference.',

            'third_preference_reason' => 'You must state your reason for 3rd division preference.'

        ]);
    }

    public function store(Request $request)
    {
        $this->validateBOMRecruitment($request);
        BOMRecruitment::create([
            'user_id' => $request->user_id, 
            'region_id' => $request->region_id, 
            'term_id' => $request->term_id, 
            'first_preference_id' => $request->first_preference_id,
            'second_preference_id' => $request->second_preference_id,
            'third_preference_id' => $request->third_preference_id,
            'first_preference_reason' => $request->first_preference_reason,
            'second_preference_reason' => $request->second_preference_reason,
            'third_preference_reason' => $request->third_preference_reason,
        ]);

        return response()->json([
            'data'=>$request->all(),
            'status' => 'success',
            'message' => 'Thank you for your registration. Please wait for the further information'
        ],201);
    }

    public function update(Request $request, string $id)
    {
        $bomRecruitment = BOMRecruitment::find($id);
        if(!$bomRecruitment) return MessageController::error("Query not found");
        $this->validateBOMRecruitment($request);

        $bomRecruitment->update([
            'first_preference_id' => $request->first_preference_id,
            'second_preference_id' => $request->second_preference_id,
            'third_preference_id' => $request->third_preference_id,
            'first_preference_reason' => $request->first_preference_reason,
            'second_preference_reason' => $request->second_preference_reason,
            'third_preference_reason' => $request->third_preference_reason,
        ]);

        return response()->json([
            'data'=> $request->all(),
            'status' => 'success',
            'message' => 'Registration data has succesfully updated'
        ],200);
    }
 
    public function destroy(string $id)
    {
        $bomRecruitment = BOMRecruitment::find($id);
        if(!$bomRecruitment) return MessageController::error("Query not found");
        $bomRecruitment->delete();
        return response()->json([
            'data'=> $bomRecruitment,
            'status' => 'success',
            'message' => 'Registration has canceled'
        ],200);
    }

    public function export($term = null, $region = 1){
        if (!$term) abort(404);
        $termData = Term::find($term);

        $regionData = Region::find($region);
        @ini_set('max_execution_time', 300);
        @ini_set("memory_limit", "512M");

        return Excel::download(new BOMRecruitmentExport($regionData->region_init, $term), 'BOM Recruitment - ' . $regionData->region_init . " - " . $termData->semester . " " . $termData->year . '.xlsx');
    }

    public function restore($id = NULL){
        if ($id != NULL) {
            $bomRecruitment = BOMRecruitment::onlyTrashed()->find($id); 
            if(!$bomRecruitment) return MessageController::error("Query not found");
            $bomRecruitment->restore();
            return response()->json([
                'data'=> $bomRecruitment,
                'status' => 'success',
                'message' => 'Registration data has successfully restored'
            ],200);
            
        } else {
            BOMRecruitment::onlyTrashed()->restore();
        }

        
    }

    public function delete($id) {
        $bomRecruitment = BOMRecruitment::onlyTrashed()->find($id);
        if(!$bomRecruitment) return MessageController::error('Query not found'); 
        $bomRecruitment->forceDelete();

        return response()->json([
            'data'=> $bomRecruitment,
            'status' => 'success',
            'message' => 'Registration has successfully deleted'
        ],200);
    }

}
