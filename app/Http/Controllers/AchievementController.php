<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;
use App\Http\Controllers\MessageController;
use App\Http\Resources\achievements\AchievementResource;
use App\Http\Resources\achievements\AchievementCollection;

class AchievementController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function  errorResponse($message){
        return response()->json([
            'status' => 'error',
            'message' => $message
        ]);
    }

    public function index(){
        $achievements = Achievement::all();
        if (!$achievements->count())return MessageController::error('No data Available');
        return new AchievementCollection($achievements);    
    }

    public function show(string $id){
        $achievement = Achievement::find($id);
        if (!$achievement) return MessageController::error("Query not found");
        return new AchievementResource($achievement);
    }

    protected function validateAchievement(Request $request)
    {
        $request->validate([
            'title' => 'required|string'
        ]);
    }

    public function store(Request $request){
        $this->validateAchievement($request);
        Achievement::create([
            'title' => $request->title
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Achievement has successfully created'
        ],201);
    }

    public function update(Request $request, string $id ){
        $achievement = Achievement::find($id);
        if(!$achievement) return MessageController::error('Query not found');
        $this->validateAchievement($request);

        $achievement->update([
            'title' => $request->title
        ]);
        
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Achievement has successfully updated'
        ],200);
    }

    public function destroy(string $id){
        $achievement = Achievement::find($id);
        if (!$achievement) return MessageController::error('Query not found');
        $achievement->delete();
        return response()->json([
            'data'=> $achievement,
            'status' => 'success',
            'message' => 'Achievement has successfuly deleted',

        ],200);
    }

}


