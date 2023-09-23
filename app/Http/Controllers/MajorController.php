<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MessageController;
use App\Http\Resources\majors\MajorResource;
use App\Http\Resources\majors\MajorCollection;

class MajorController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function index()
    {
        $majors = Major::all();
        if (!$majors->count())return MessageController::error('No data Available');
        return new MajorCollection($majors);
    }

    
    protected function validateMajor()
    {
        return request()->validate([
            'region_id' => 'required|integer',
            'faculty' => 'required|string',
            'major_name' => 'required|string',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateMajor($request);
        Major::create([
            'region_id' => $request->region_id,
            'faculty' => $request->faculty,
            'major_name' => $request->major_name,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Major has successfully created'
        ],201);
    }

    
    public function show(string $id)
    {
        $major = Major::find($id);
        if($major == null)return MessageController::error('No data avaiable');
        return new MajorResource($major);
    }
 
    
    public function update(Request $request, string $id)
    {
        $major = Major::find($id);
        if (!$major)return MessageController::error("No query found"); 
        $this->validateMajor();
         
        $major->update([
            'region_id' => $request->region_id,
            'faculty' => $request->faculty,
            'major_name' => $request->major_name,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Major has successfully updated'
        ],200);
    }

 
    public function destroy(string $id)
    {
        $major = Major::find($id);
        if (!$major)return MessageController::error("No query found");
        $major->delete();
        return response()->json([
            'data'=> $major,
            'status' => 'success',
            'message' => 'Major '. $major->major_name.' has successfuly deleted',

        ],200);
    }
}





