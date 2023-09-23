<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmMail;
use Illuminate\Http\Request;
use App\Models\WelcomingParty;
use App\Exports\WelcomePartyExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\MessageController;
use App\Http\Resources\welcomingParties\WelcomingPartyResource;
use App\Http\Resources\welcomingParties\WelcomingPartyCollection;

class WelcomingPartyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['export']);
        $this->middleware('IsAdmin')->except(['store', 'update', 'export']);
        $this->middleware('IsShowed:ENV005')->only(['validateEnvironment']);
    }

    public function validateEnvironment(){
        return response()->json([
            'success'=> true,
            'message' => 'Page is opened'
        ]);
    }
    public function index()
    {
        $welcomingParty =WelcomingParty::withTrashed()->get();
        if(!$welcomingParty->count() ) return MessageController::error('No data available');
        return new WelcomingPartyCollection($welcomingParty);
    }
    public function show(string $id)
    {
        $welcomingParty =WelcomingParty::find($id);
        if(!$welcomingParty) return MessageController::error("Query not found");
        return new WelcomingPartyResource($welcomingParty);
    }

    protected function validateWelcomingParty(Request $request)
    {   
        if($request->type == '0'){
            return request()->validate([
                'name' => 'required|string',
                'type' => 'required|boolean',
                'campus_location' => 'nullable|string',
                'nim' => 'nullable|numeric|digits:10',
                'major_id' => 'nullable|integer',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'line_id' => 'required|string',
                'instagram' => 'required|string',
                'proof' => 'required|image|max:1999|mimes:jpg,png,jpeg,webp',
                'shift_id' => 'required|integer',
            ]);
        } else {
            return request()->validate([
                'name' => 'required|string',
                'type' => 'required|boolean',
                'campus_location' => 'required|string',
                'nim' => 'required|integer',
                'major_id' => 'required|integer',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'line_id' => 'required|string',
                'instagram' => 'required|string',
                'proof' => 'required|image|max:1999|mimes:jpg,png,jpeg,webp',
                'shift_id' => 'required|integer',
            ]);
        }
    }

    public function store(Request $request)
    {
        $this->validateWelcomingParty($request);

        if($image = $request->file('proof')){
            $extension = $request->file('proof')->getClientOriginalExtension();
            $fileName = $request->name . '_' . time() . '.' . $extension;
            $destination = 'storage/welcoming-parties';
            $image->move($destination,$fileName);

        }
        WelcomingParty::create([
            'name' => $request->name,
            'type' => $request->type,
            'campus_location' => $request->campus_location,
            'nim' => $request->nim,
            'major_id' => $request->major_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'line_id' => $request->line_id,
            'instagram' => $request->instagram,
            'proof' => $fileName,
            'shift_id' => $request->shift_id,
            'is_confirmed' => false
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Registration success. Please scan the QR Code in this page or click the links for further information.'
        ],201);

    }
 
    public function update(Request $request, string $id)
    {
        $welcomingParty =WelcomingParty::find($id);
        if(!$welcomingParty) return MessageController::error("Query not found");

        if($request->type == '0'){
            $request->validate([
                'name' => 'required|string',
                'type' => 'required|boolean',
                'campus_location' => 'nullable|string',
                'nim' => 'nullable|integer',
                'major_id' => 'nullable|integer',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'line_id' => 'required|string',
                'instagram' => 'required|string',
                'proof' => 'nullable|image|max:1999|mimes:jpg,png,jpeg,webp',
                'shift_id' => 'required|integer',
            ]);
        } else {
            $request->validate([
                'name' => 'required|string',
                'type' => 'required|boolean',
                'campus_location' => 'required|string',
                'nim' => 'required|integer',
                'major_id' => 'required|integer',
                'email' => 'required|string',
                'phone_number' => 'required|string',
                'line_id' => 'required|string',
                'instagram' => 'required|string',
                'proof' => 'nullable|image|max:1999|mimes:jpg,png,jpeg,webp',
                'shift_id' => 'required|integer',
            ]);
        }

        if ($image = $request->file('proof')) {
            $extension = $request->file('proof')->getClientOriginalExtension();
            $fileName = $request->name . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/welcoming-parties';
            $image->move($destinationPath,$fileName);
            if(File::exists($destinationPath.'/'.$welcomingParty->proof)) {
                File::delete($destinationPath.'/'.$welcomingParty->proof);
            }
            
        } else {
            $fileName = $welcomingParty->proof;
        }
        $welcomingParty->update([
            'name' => $request->name,
            'type' => $request->type,
            'campus_location' => $request->campus_location,
            'nim' => $request->nim,
            'major_id' => $request->major_id,
            'line_id' => $request->line_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'instagram' => $request->instagram,
            'proof' => $fileName,
            'shift_id' => $request->shift_id,
        ]);
        
        return response()->json([
            'data'=> $request->all(),
            'status' => 'success',
            'message' => 'Welcoming Party data  has successfully updated'
        ],200);
    }

   
    public function destroy(string $id)
    {
        $welcomingParty =WelcomingParty::find($id);
        if(!$welcomingParty) return MessageController::error("Query not found");
        $confirmMail = [
            'subject' => "Welcoming Party Shift Rejection",
            'body' => "Sorry, your Welcoming Party Shift has been rejected. Please contact our contact person for further information.",
            'link' => "Try to register again at https://www.recruitment.mybnec.org/welcoming-parties/register"
        ];

        Mail::to($welcomingParty->email)->send(new ConfirmMail($confirmMail));
        
        $welcomingParty->delete();

        return response()->json([
            'data'=>  $welcomingParty,
            'status' => 'success',
            'message' => 'Welcoming Party data  has successfully deleted'
        ],200);
    }

    public   function export()
    {
        return Excel::download(new WelcomePartyExport, 'welcoming-parties.xlsx');
    }

    public function restore($id = NULL)
    {
        if ($id != NULL) {
            $welcomingParty = WelcomingParty::onlyTrashed()->find($id);
            if(!$welcomingParty ) return MessageController::error('Query not found');
            $welcomingParty->restore();
            return response()->json([
                'data'=>  $welcomingParty,
                'status' => 'success',
                'message' => 'Welcoming Party data  has successfully restored'
            ],200);

        } else {
            WelcomingParty::onlyTrashed()->restore();
        }
    }

    public function delete($id = NULL)
    {
        if ($id != NULL) {
            $welcomingParty = WelcomingParty::onlyTrashed()->find($id);
            if(!$welcomingParty ) return MessageController::error('Query not found');
            
            $imagePath = 'storage/welcoming-parties/'.$welcomingParty->proof;
            if(File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $welcomingParty->forceDelete();
            return response()->json([
                'data'=>  $welcomingParty,
                'status' => 'success',
                'message' => 'Welcoming Party data  has successfully deleted'
            ],200);
        } else {
            WelcomingParty::onlyTrashed()->forceDelete();
        }
    }

    public function confirm(string $id)
    {
        $welcomingParty =WelcomingParty::find($id);
        if(!$welcomingParty) return MessageController::error("Query not found");

        $welcomingParty->update([
            'is_confirmed' => 1
        ]);

        $confirmMail = [
            'subject' => "Welcoming Party Shift Confirmation",
            'body' => "Congratulations! Your Welcoming Party Shift has been confirmed.",
            'link' => "Please join the line group here: https://line.me/ti/g/6V6pq9BdTq"
        ];

        Mail::to($welcomingParty->email)->send(new ConfirmMail($confirmMail));
        return response()->json([
            'data'=>  $welcomingParty,
            'status' => 'success',
            'message' => 'Welcoming Party data  has successfully confirmed'
        ],200);
        
    }

    public function confirmAll() {
        WelcomingParty::where('is_confirmed', 0)->update(  ['is_confirmed' => 1] );
        return response()->json([
            'status' => 'success',
            'message' => 'All welcoming party registrant have successfully confirmed'
        ],200);
        
    }

}

 