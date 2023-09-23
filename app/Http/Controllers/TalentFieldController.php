<?php

namespace App\Http\Controllers;

use App\Models\TalentField;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\talentFields\TalentFieldResource;
use App\Http\Resources\talentFields\TalentFieldCollection;

class TalentFieldController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function index()
    {
        $fields = TalentField::all();
        if(!$fields->count() ) return MessageController::error('No data available');
        return new TalentFieldCollection($fields);
    }

    public function show(string $id)
    {
        $field = TalentField::find($id);
        if(!$field) return MessageController::error("Query not found");
        return new TalentFieldResource($field);
    }
    
    protected function validateTalentField()
    {
        return request()->validate([
            'name' => 'required|string',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateTalentField();

        TalentField::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Talent Field has successfully created'
        ],201);
    }
 
    public function update(Request $request, string $id)
    {
        $this->validateTalentField();

        $talentField = TalentField::find($id);
        if(!$talentField) return MessageController::error("Query not found");

        $talentField->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Talent Field has successfully updated'
        ],200);   
    }
 
    public function destroy(string $id)
    {
        $field = TalentField::find($id);
        if(!$field) return MessageController::error('Query not found');
        $field->delete();
        return response()->json([
            'data' => $field,
            'status' => 'success',
            'message' => 'Talent Field has successfully deleted'
        ],200); 
    }
}
