<?php

namespace App\Http\Controllers;

use App\Models\Partnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\MessageController;
use App\Http\Resources\partnerships\PartnershipResource;
use App\Http\Resources\partnerships\PartnershipCollection;

class PartnershipController extends Controller
{
   
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    public function index()
    {
        $partnership = Partnership::all();
        if(!$partnership->count()) return MessageController::error('no data available');
        return new PartnershipCollection($partnership);
    }

    public function show(string $id)
    {
        $partnership = Partnership::find($id);
        if(!$partnership) return MessageController::error('Query not found');
        return new PartnershipResource($partnership);
    }
     
    private function validatePartnership(Request $request, $isStore)
    {
        $request->validate([
            'region_id' => 'nullable|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'promo' => 'nullable|string',
            'logo' => 'image|mimes:png,jpg,jpeg,webp|max:1999',
        ]);

        if ($isStore) {
            $request->validate([
                'logo' => 'required',
            ]);
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validatePartnership($request, true);

        if ($image = $request->file('logo')) {
            $extension = $request->file('logo')->getClientOriginalExtension();
            $fileName = $request->name . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/partnership/logo';
            $image->move($destinationPath, $fileName);
        }

        Partnership::create([
            'region_id' => $request->region_id,
            'name' => $request->name,
            'description' => $request->description,
            'promo' => $request->promo,
            'logo' => $fileName,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Partnership has successfully created'
        ],201);

        
    }
 
 
   
    public function update(Request $request, string $id)
    {
        $partnership = Partnership::find($id);
        if (!$partnership) return MessageController::error('Query not found');
        $this->validatePartnership($request,false);
        if ($image = $request->file('logo')) {
            $extension = $request->file('logo')->getClientOriginalExtension();
            $fileName = $request->name . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/partnership/logo';
            $image->move($destinationPath,$fileName);
            if(File::exists($destinationPath.'/'.$partnership->logo)) {
                File::delete($destinationPath.'/'.$partnership->logo);
            }
            
        } else {
            $fileName = $partnership->logo;
        }

        $partnership->update([
            'region_id' => $request->region_id,
            'name' => $request->name,
            'description' => $request->description,
            'promo' => $request->promo,
            'logo' => $fileName,
        ]);

        return response()->json([
            'data'=> $request->all(),
            'status' =>'success',
            'message' => 'Partnership has successfully updated'
        ]);
    }
 
    public function destroy(string $id)
    {
        $partnership = Partnership::find($id);
        if(!$partnership) return MessageController::error('Query not found');
        $imagePath = 'storage/partnership/logo/'. $partnership->logo;
        if(File::exists($imagePath)) {
            File::delete($imagePath);
        }

        $partnership->delete();
        return response()->json([
            'data' => $partnership,
            'status' => 'success', 
            'message' => 'Partnership has successfuly deleted', 
        ]);
    }
}



 