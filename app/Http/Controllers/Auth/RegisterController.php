<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Region;
use App\Mail\RegisterMail;
use App\Models\TOEFLShift;
use App\Models\TOEFLDetail;
use App\Models\TOEFLPayment;
use Illuminate\Http\Request;
use App\Models\MemberPayment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api')->only('createExtend', 'storeExtend');
        $this->middleware('IsShowed:ENV001')->only(['validateEnvironment']);
    } 

    public function validateEnvironment(){
        return response()->json([
            'success'=> true,
            'message' => 'Page is opened'
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'region_id' => 'required|integer',
            'campus_location.*' => 'required|string',
            'batch.*' => 'required|integer|min:1',
            'nim.*' => 'required|numeric|digits:10|distinct|unique:users,nim',
            'name.*' => 'required|distinct|string',
            'major_id.*' => 'required|integer',
            'gender.*' => 'required|string',
            'birth_place.*' => 'required|string',
            'birth_date.*' => 'required|date_format:Y-m-d',
            'address.*' => 'required|string',
            'domicile.*' => 'required|string',
            'email.*' => 'required|email|distinct|unique:users,email',
            'phone_number.*' => 'required|numeric|distinct|unique:users,phone_number',
            'line_id.*' => 'required|string|distinct|unique:users,line_id',
            'shift_id.*' => 'required|integer',
        ], [
            'nim.*.unique' => 'NIM has already been taken',
            'nim.*.digits' => 'NIM must be 10 digits',
            'phone_number.*.unique' => 'Phone number has already been taken',
            'line_id.*.unique' => 'LINE ID has already been taken',
            'email.*.unique' => 'Email has already been taken',
            'email.*.email' => 'Email must be a valid email address',
            'name.*.distinct' => 'Name has duplicate value',
            'nim.*.distinct' => 'NIM has duplicate value',
            'phone_number.*.distinct' => 'Phone number has duplicate value',
            'phone_number.*.numeric' => 'Phone number must be a number',
            'line_id.*.distinct' => 'LINE ID has duplicate value',
            'email.*.distinct' => 'Email has duplicate value',
        ]);

        $this->validate($request, [
            'payment_type' => 'required|string',
            'provider_id' => 'required|integer',
            'account_name' => 'required|string',
            'account_number' => 'required|numeric|string',
            'payment_amount' => 'required|integer',
            'payment_proof' => 'image|required|max:999|mimes:jpg,png,jpeg',
        ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 1 MB'
        ]);

 

        if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $proofNameToStore = $request->name[0] . '_' . $request->nim[0] . '_BNEC_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/toefl_payment_proofs';
            $image->move($destinationPath, $proofNameToStore);
        }

        if (count($request->name) > 0) {
            TOEFLPayment::create([
                'payment_type' => $request->payment_type,
                'provider_id' => $request->provider_id,
                'account_name' => strtoupper($request->account_name),
                'account_number' => $request->account_number,
                'payment_amount' => $request->payment_amount,
                'payment_proof' => $proofNameToStore,
                'receiver_id' => $request->receiver_id,
                'is_confirmed' => false
            ]);

            $lastToeflPayment = TOEFLPayment::latest()->first();
            $region = Region::find($request->region_id);

            foreach ($request->name as $index => $value) {
                User::create([
                    'term_id' => $region->current_term_id,
                    'role' => 'USER',
                    'region_id' => $request->region_id,
                    'campus_location' => $request->campus_location[$index],
                    'batch' => $request->batch[$index],
                    'nim' => $request->nim[$index],
                    'name' => ucwords($request->name[$index]),
                    'major_id' => $request->major_id[$index],
                    'gender' => $request->gender[$index],
                    'birth_place' => $request->birth_place[$index],
                    'birth_date' => $request->birth_date[$index],
                    'address' => $request->address[$index],
                    'domicile' => $request->domicile[$index],
                    'email' => $request->email[$index],
                    'phone_number' => $request->phone_number[$index],
                    'line_id' => $request->line_id[$index],
                    'password' => Hash::make('BNEC' . substr($request->nim[$index], -6)),
                ]);
                $recentUser = User::latest()->first();

                TOEFLDetail::create([
                    'payment_id' => $lastToeflPayment->id,
                    'term_id' => $region->current_term_id,
                    'user_id' => $recentUser->id,
                    'shift_id' => $request->shift_id[$index],
                    'edit_status' => 0,
                    'edit_reason' => null,
                    'is_attend' => 0,
                    'score' => null,
                ]);

                $toeflShift = TOEFLShift::find($request->shift_id[$index]);

                $newQuota = $toeflShift->quota - 1;

                $toeflShift->update([
                    'quota' => $newQuota
                ]);

                Mail::to($request->email[$index])->send(new RegisterMail($request->nim[$index], 'BNEC' . substr($request->nim[$index], -6), $request->name[$index]));
            }
        }
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => "Registration Success. Please wait 2-3 working days for our confirmation through your email regarding further information"
        ]);
    }

    public function storeExtend(Request $request)
    {
        $this->validate($request, [
            'shift_id' => 'required|integer',
        ]);

        $this->validate($request, [
            'shift_id' => 'required|integer',
            'payment_type' => 'required|string',
            'provider_id' => 'required_unless:payment_type,CASH|nullable|integer',
            'account_name' => 'required_unless:payment_type,CASH|nullable|string',
            'account_number' => 'required_unless:payment_type,CASH|nullable|string',
            'payment_amount' => 'required|integer',
            'payment_proof' => 'image|required|max:1999|mimes:jpg,png,jpeg',
            'receiver_id' => 'required_if:payment_type,CASH|nullable|integer'
        ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 2 MB'
        ]);

        if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $proofNameToStore = $request->input('region_id') . '_' . $request->input('account_number') . '_' . $request->input('account_name') . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/toefl_payment_proofs';
            $image->move($destinationPath, $proofNameToStore);
        }

        TOEFLPayment::create([
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'payment_proof' => $proofNameToStore,
            'receiver_id' => $request->receiver_id,
            'is_confirmed' => false
        ]);

        $lastToeflPayment = TOEFLPayment::latest()->first();
        $region = Region::find(auth()->user()->region_id);

        TOEFLDetail::create([
            'payment_id' => $lastToeflPayment->id,
            'term_id' => $region->current_term_id,
            'user_id' => auth()->user()->id,
            'shift_id' => $request->shift_id,
            'edit_status' => 0,
            'edit_reason' => null,
            'is_attend' => 0,
            'score' => null,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => "Registration Success. Please wait 2-3 working days for our confirmation through your email regarding further information"
        ]);
    }

    public function storeMember(Request $request)
    {
        $this->validate($request, [
            'region_id' => 'required|integer',
            'batch' => 'nullable|integer|min:1',
            'nim' => 'required|numeric|digits:10|distinct|unique:users,nim',
            'name' => 'required|distinct|string',
            'major_id' => 'required|integer',
            'gender' => 'required|string',
            'birth_place' => 'nullable|string',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'address' => 'nullable|string',
            'domicile' => 'nullable|string',
            'email' => 'required|email|distinct|unique:users,email',
            'phone_number' => 'required|numeric|distinct|unique:users,phone_number',
            'line_id' => 'required|string|distinct|unique:users,line_id',
        ], [
            'nim.unique' => 'NIM has already been taken',
            'nim.digits' => 'NIM must be 10 digits',
            'phone_number.unique' => 'Phone number has already been taken',
            'line_id.unique' => 'LINE ID has already been taken',
            'email.unique' => 'Email has already been taken',
            'email.email' => 'Email must be a valid email address',
            'name.distinct' => 'Name has duplicate value',
            'nim.distinct' => 'NIM has duplicate value',
            'phone_number.distinct' => 'Phone number has duplicate value',
            'phone_number.numeric' => 'Phone number must be a number',
            'line_id.distinct' => 'LINE ID has duplicate value',
            'email.distinct' => 'Email has duplicate value',
        ]);

        $this->validate($request, [
            'payment_type' => 'required|string',
            'provider_id' => 'required_unless:payment_type,CASH|nullable|integer',
            'account_name' => 'required_unless:payment_type,CASH|nullable|string',
            'account_number' => 'required_unless:payment_type,CASH|nullable|string',
            'payment_amount' => 'required|integer',
            'payment_proof' => 'image|required|max:1999|mimes:jpg,png,jpeg',
        ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 2 MB'
        ]);

        if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $proofNameToStore = $request->input('region_id') . '_' . $request->input('account_number') . '_' . $request->input('account_name') . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/member_payment_proofs';
            $image->move($destinationPath, $proofNameToStore);
        }

        $region = Region::find($request->region_id);

        User::create([
            'term_id' => $region->current_term_id,
            'role' => 'USER',
            'region_id' => $request->region_id,
            'batch' => $request->batch,
            'nim' => $request->nim,
            'name' => $request->name,
            'major_id' => $request->major_id,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'domicile' => $request->domicile,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'line_id' => $request->line_id,
            'password' => Hash::make('BNEC' . substr($request->nim, -6)),
        ]);

        $recentUser = User::latest()->first();
        MemberPayment::create([
            'term_id' => $region->current_term_id,
            'user_id' => $recentUser->id,
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'payment_proof' => $proofNameToStore,
            'is_confirmed' => false,
        ]);

        Mail::to($request->email)->send(new RegisterMail($request->nim, 'BNEC' . substr($request->nim, -6), $request->name));

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => "Registration Success. Please wait 2-3 working days for our confirmation through your email regarding further information"
        ]);
    }
     

}
