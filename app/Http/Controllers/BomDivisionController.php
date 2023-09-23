<?php

namespace App\Http\Controllers;

use App\Models\BOMDivision;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\MessageController;
use App\Http\Resources\bomDivisions\BomDivisionResource;
use App\Http\Resources\bomDivisions\BomDivisionCollection;
use App\Http\Resources\bomRecruitments\BomRecruitmentResource;
use App\Http\Resources\bomRecruitments\BomRecruitmentCollection;

class BomDivisionController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
        $this->middleware('IsAdmin')->except('index', 'show');
    }
    
    public function index()
    {
        $bomDivisions = BOMDivision::all();
        if(!$bomDivisions->count() ) return MessageController::error('No data available');
        return new BomDivisionCollection($bomDivisions);
    }

    public function show(string $id)
    {
        $bomDivision = BOMDivision::find($id);
        if(!$bomDivision) return MessageController::error("Query not found");
        return new BomDivisionResource($bomDivision);
    }

    protected function validateBOMDivision()
    {
        return request()->validate([
            'div_init' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'task_link' => 'required|string',
            'link_line_group' => 'required|string',
            'qr_line_group' => 'image|required|max:1999|mimes:jpg,png,jpeg',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateBOMDivision();
        if ($image = $request->file('qr_line_group')) {
            $extension = $request->file('qr_line_group')->getClientOriginalExtension();
            $fileName = $request->div_init . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/division/qr-line-group';
            $image->move($destinationPath, $fileName);
        }

        BOMDivision::create([
            'div_init' => $request->div_init,
            'name' => $request->name,
            'region_id' => $request->region_id,
            'description' => $request->description,
            'task_link' => $request->task_link,
            'link_line_group' => $request->link_line_group,
            'qr_line_group' => $fileName,
        ]);

        return response()->json([
            'data'=>$request->all(),
            'status' => 'success',
            'message' => 'Bom Division has successfully created'
        ],201);
    }
    
    public function update(Request $request, string $id)
    {
        $bomDivision = BOMDivision::find($id);
        if(!$bomDivision) return MessageController::error("Query not found");

        $request->validate([
            'div_init' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'task_link' => 'required|string',
            'link_line_group' => 'required|string',
            'qr_line_group' => 'image|nullable|max:1999|mimes:jpg,png,jpeg',
        ]);

        if ($image = $request->file('qr_line_group')) {
            $extension = $request->file('qr_line_group')->getClientOriginalExtension();
            $fileName = $request->div_init . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/division/qr-line-group';
            $image->move($destinationPath,$fileName);
            if(File::exists($destinationPath.'/'.$bomDivision->qr_line_group )) {
                File::delete($destinationPath.'/'.$bomDivision->qr_line_group);
            }
        } else {
            $fileName = $bomDivision->qr_line_group;
        }

        $bomDivision->update([
            'div_init' => $request->div_init,
            'name' => $request->name,
            'description' => $request->description,
            'task_link' => $request->task_link,
            'link_line_group' => $request->link_line_group,
            'qr_line_group' => $fileName,
        ]);

        return response()->json([
            'data'=> $request->all(),
            'status' => 'success',
            'message' => 'Bom Division has successfully updated'
        ],200);
    }

   
    public function destroy(string $id)
    {
        $bomDivision = BOMDivision::find($id);
        if(!$bomDivision) return MessageController::error("Query not found");

        $imagePath = 'storage/division/qr-line-group/'. $bomDivision->qr_line_group;
        if(File::exists($imagePath)) {
            File::delete($imagePath);
        }


        $bomDivision->delete();
        return response()->json([
            'data'=> $bomDivision,
            'status' => 'success',
            'message' => 'Bom Division has successfully deleted'
        ],200);
    }
}


 