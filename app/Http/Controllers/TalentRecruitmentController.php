<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TalentRecruitment;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TalentRecruitmentExport;
use App\Http\Controllers\MessageController;
use App\Http\Resources\talentRecruitments\TalentRecruitmentResource;
use App\Http\Resources\talentRecruitments\TalentRecruitmentCollection;

class TalentRecruitmentController extends Controller
{
    public function __construct(){
        $this->middleware(['auth:api'])->except(['export', 'store']);
        $this->middleware(['IsAdmin'])->only(['index', 'edit', 'update'])->except(['export, store']);
        $this->middleware('IsShowed:ENV007')->only(['validateEnvironment']);
    } 

    public function validateEnvironment(){
        return response()->json([
            'success'=> true,
            'message' => 'Page is opened'
        ]);
    }
    
    public function index()
    {
        $talentRecruitments = TalentRecruitment::withTrashed()->get();
        if(!$talentRecruitments->count() ) return MessageController::error('No data available');
        return new TalentRecruitmentCollection($talentRecruitments);
    }

    public function show(string $id)
    {
        $talentRecruitment = TalentRecruitment::find($id);
        if(!$talentRecruitment) return MessageController::error("Query not found");
        return new TalentRecruitmentResource($talentRecruitment);
    }

    protected function validateTalentRecruitment(Request $request)
    {   

        $this->asFields=array('2', '3');
        $this->bksFields=array('1', '2');
        $this->jwcFields=array('2');

        if($request->region == 'AS' || $request->region == 'ASO'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric|unique:talent_recruitments,nim',
                'name' => 'required|string|unique:talent_recruitments,name',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string|unique:talent_recruitments,phone_number',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string|unique:talent_recruitments,line_id',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->asFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->asFields)
                ],
            ], [
                'nim.unique' => 'NIM has already been taken.', 
                'name.unique' => 'Name has already been taken.', 
                'phone_number.unique' => 'Phone Number has already been taken.', 
                'line_id.unique' => 'Line ID has already been taken.', 
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } elseif ($request->region == 'BKS'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric|unique:talent_recruitments,nim',
                'name' => 'required|string|unique:talent_recruitments,name',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string|unique:talent_recruitments,phone_number',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string|unique:talent_recruitments,line_id',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->bksFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->bksFields)
                ],
            ], [
                'nim.unique' => 'NIM has already been taken.', 
                'name.unique' => 'Name has already been taken.', 
                'phone_number.unique' => 'Phone Number has already been taken.', 
                'line_id.unique' => 'Line ID has already been taken.', 
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } elseif ($request->region == 'JWC'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric|unique:talent_recruitments,nim',
                'name' => 'required|string|unique:talent_recruitments,name',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string|unique:talent_recruitments,phone_number',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string|unique:talent_recruitments,line_id',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->jwcFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->jwcFields)
                ],
            ], [
                'nim.unique' => 'NIM has already been taken.', 
                'name.unique' => 'Name has already been taken.', 
                'phone_number.unique' => 'Phone Number has already been taken.', 
                'line_id.unique' => 'Line ID has already been taken.', 
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } else {
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric|unique:talent_recruitments,nim',
                'name' => 'required|string|unique:talent_recruitments,name',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string|unique:talent_recruitments,phone_number',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string|unique:talent_recruitments,line_id',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id)
                ],
            ], [
                'nim.unique' => 'NIM has already been taken.', 
                'name.unique' => 'Name has already been taken.', 
                'phone_number.unique' => 'Phone Number has already been taken.', 
                'line_id.unique' => 'Line ID has already been taken.', 
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
            ]);
        }
    }
    
    protected function validateEditTalentRecruitment(Request $request)
    {   

        $this->asFields=array('2', '3');
        $this->bksFields=array('1', '2');
        $this->jwcFields=array('2');

        if($request->region == 'AS' || $request->region == 'ASO'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric',
                'name' => 'required|string',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->asFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->asFields)
                ],
            ], [
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } elseif ($request->region == 'BKS'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric',
                'name' => 'required|string',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->bksFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->bksFields)
                ],
            ], [
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } elseif ($request->region == 'JWC'){
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric',
                'name' => 'required|string',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id), Rule::in($this->jwcFields)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id), Rule::in($this->jwcFields)
                ],
            ], [
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
                'first_talent_field_id.in' => 'The selected 1st talent field is not available in your region.',
                'second_talent_field_id.in' => 'The selected 2nt talent field is not available in your region.',
            ]);
        } else {
            return $request->validate([
                'region' => 'required|string',
                'nim' => 'required|numeric',
                'name' => 'required|string',
                'gender' => 'required|string',
                'major_id' => 'required|integer',
                'email' => 'nullable',
                'phone_number' => 'required|string',
                'alt_phone_number' => 'nullable|string',
                'line_id' => 'required|string',
                'birth_place' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'address' => 'required|string',
                'allergy' => 'nullable|string',
                'first_talent_field_id' =>
                [
                    'required', 'integer', Rule::notIn($request->second_talent_field_id)
                ],
                'second_talent_field_id' =>
                [
                    'nullable', 'integer', Rule::notIn($request->first_talent_field_id)
                ],
            ], [
                'first_talent_field_id.not_in' => 'The selected 1st talent field cannot be the same as the other field.',
                'second_talent_field_id.not_in' => 'The selected 2nd talent field cannot be the same as the other field.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $this->validateTalentRecruitment($request);

        if($request->region == 'AS' || $request->region == 'ASO')
            $term = Region::where('region_init', 'AS')->first()->current_term_id;

        elseif ($request->region == 'BKS')
            $term = Region::where('region_init', 'BKS')->first()->current_term_id;

        elseif ($request->region == 'JWC')
            $term = Region::where('region_init', 'JWC')->first()->current_term_id;

        else 
            $term = Region::where('region_init', 'KMG')->first()->current_term_id;
        

        TalentRecruitment::create([
            'term_id' => $term,
            'region' => $request->region,
            'nim' => $request->nim,
            'name' => $request->name,
            'gender' => $request->gender,
            'major_id' => $request->major_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'alt_phone_number' => $request->alt_phone_number,
            'line_id' => $request->line_id,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'allergy' => $request->allergy,
            'first_talent_field_id' => $request->first_talent_field_id,
            'second_talent_field_id' => $request->second_talent_field_id,

        ]);
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Thank you for your registration. Please read the information in this page for the next step.'
        ],201);

    }

 
 
    public function update(Request $request, string $id)
    {
        $talentRecruitment = TalentRecruitment::find($id);
        if(!$talentRecruitment) return MessageController::error("Query not found");

        $this->validateEditTalentRecruitment($request);
        $talentRecruitment->update([
            'term_id' => $request->term_id,
            'region' => $request->region,
            'nim' => $request->nim,
            'name' => $request->name,
            'gender' => $request->gender,
            'major_id' => $request->major_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'alt_phone_number' => $request->alt_phone_number,
            'line_id' => $request->line_id,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'allergy' => $request->allergy,
            'first_talent_field_id' => $request->first_talent_field_id,
            'second_talent_field_id' => $request->second_talent_field_id,
        ]);
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Talent has successfully updated'
        ],200);
    }

  
    public function destroy(string $id)
    {
        $talentRecruitment = TalentRecruitment::find($id);
        if(!$talentRecruitment) return MessageController::error("Query not found");
        $talentRecruitment->delete();
        return response()->json([
            'data' => $talentRecruitment,
            'status' => 'success',
            'message' => 'Talent Recruitment has successfully deleted'
        ],200);
    }

    public function export($term = null)
    {
        @ini_set('max_execution_time', 300);
        @ini_set("memory_limit", "512M");
        if (!$term) abort(404);
        $termData = Term::find($term);
        return Excel::download(new TalentRecruitmentExport($term), 'talent-recruitments-'.$termData->year.'-'.$termData->semester.'.xlsx');
    }

    public function restore($id)
    {
        $talentRecruitment = TalentRecruitment::onlyTrashed()->find($id);
        if(!$talentRecruitment) return MessageController::error("Query not found");
        $talentRecruitment->restore();
 
        return response()->json([
            'data' => $talentRecruitment,
            'status' => 'success',
            'message' => 'Talent Recruitment has successfully restored'
        ],200);
         
    }

    public function delete($id)
    {
        $talentRecruitment = TalentRecruitment::onlyTrashed()->find($id);
        if(!$talentRecruitment) return MessageController::error("Query not found");
        $talentRecruitment->forceDelete();
  
        return response()->json([
            'data' => $talentRecruitment,
            'status' => 'success',
            'message' => 'Talent Recruitment has successfully restored'
        ],200);
    }
}
