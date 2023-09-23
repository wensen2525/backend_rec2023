<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactPerson;
use App\Http\Controllers\MessageController;
use App\Http\Resources\contacts\ContactPersonResource;
use App\Http\Resources\contacts\ContactPersonCollection;

class ContactPersonController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    } 

    public function index(){
        $contacts = ContactPerson::all();
        if(!$contacts->count()) return MessageController::error("No data available");
        return new ContactPersonCollection($contacts);
    }

    public function show(string $id ){
        $contact = ContactPerson::find($id);
        if(!$contact) return MessageController::error("Query not found");
        return new ContactPersonResource($contact);
    }

    public function store(Request $request){
        $this->validateContactPerson($request);

        ContactPerson::create([
            'region_id' => $request->region_id,
            'name' => $request->name,
            'line' => $request->line,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'type' => $request->type
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Contact Person has successfully created'
        ],201);
    }

    public function update(Request $request, string $id){
        $contact = ContactPerson::find($id);
        if (!$contact) return MessageController::error("Query not found");
        
        $this->validateContactPerson($request);
        $contact->update([
            'region_id' => $request->region_id,
            'name' => $request->name,
            'line' => $request->line,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'type' => $request->type
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Contact Person has successfully updated'
        ],200);
    }

    public function destroy(string $id){
        $contact = ContactPerson::find($id);
        if (!$contact) return MessageController::error("Query not found");

        $contact->delete();
        return response()->json([
            'data' => $contact,
            'status' => 'success',
            'message' => 'Contact Person has successfully deleted'
        ],200);
    }
    
    protected function validateContactPerson(Request $request)
    {
        $request->validate([
            'region_id' => 'nullable|integer',
            'name' => 'required|string',
            'line' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string',
            'type' => 'required|string'
        ]);
    }
}


 