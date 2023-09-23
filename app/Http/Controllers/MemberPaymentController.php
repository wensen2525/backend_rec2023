<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\Term;
use App\Models\User;
use App\Models\Region;
use App\Mail\ConfirmMail;
use Illuminate\Http\Request;
use App\Models\MemberPayment;
use App\Exports\MemberPaymentExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\MessageController;
use App\Http\Resources\memberPayments\MemberPaymentResource;
use App\Http\Resources\memberPayments\MemberPaymentCollection;

class MemberPaymentController extends Controller
{
    public function __construct(){
      $this->middleware('auth:api')->except(['viewReceipt', 'export']);
      $this->middleware('IsAdmin')->except('create', 'store', 'show','update', 'viewReceipt', 'export');
      $this->middleware('IsShowed:ENV003')->only(['validateEnvironment']);
    } 

    public function validateEnvironment(){
      return response()->json([
          'success'=> true,
          'message' => 'Page is opened'
      ]);
    }

    public function getPendings($term){
      $memberPayments = MemberPayment::where('term_id', $term)->where('is_confirmed', 0)->orderBy('account_name')->paginate(10);
      if(!$memberPayments->count()) return MessageController::error("No data available");
      return new MemberPaymentCollection($memberPayments);
    }

    public function getConfirmeds($term){
      $memberPayments = MemberPayment::where('term_id', $term)->where('is_confirmed', 1)->orderBy('account_name')->paginate(10);
      if(!$memberPayments->count()) return MessageController::error("No data available");
      return new MemberPaymentCollection($memberPayments);
    }

    public function getRecycleds($term){
      $memberPayments = MemberPayment::onlyTrashed()->where('term_id', $term)->orderBy('account_name')->paginate(10);
      if(!$memberPayments) return MessageController::error("No data available");
      return new MemberPaymentCollection($memberPayments);
    }

    public function index()
    {
        $memberPayments = MemberPayment::withTrashed()->get();
        if(!$memberPayments->count()) return MessageController::error("No data available");
        return new MemberPaymentCollection($memberPayments);
    }

    public function show(string $id)
    {
        $memberPayment = MemberPayment::find($id);
        if (!$memberPayment) return MessageController::error('Query not found');
        return new MemberPaymentResource($memberPayment);

    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'payment_type' => 'required|string',
            'provider_id' => 'required|integer',
            'account_name' => 'required|string',
            'account_number' => 'required|string|numeric',
            'payment_amount' => 'required|integer',
            'payment_proof' => 'image|required|max:999|mimes:jpg,png,jpeg',
          ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 1 MB'
          ]);
          if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $fileName =$request->input('region_id') . '_' . $request->input('account_number') . '_' . $request->input('account_name') . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/member_payment_proofs';
            $image->move($destinationPath, $fileName);
        }
 
      
        $region = Region::find(auth()->user()->region_id);
      
        MemberPayment::create([
            'term_id' => $region->current_term_id,
            'user_id' => auth()->user()->id,
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => strtoupper($request->account_name),
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'payment_proof' => $fileName,
            'is_confirmed' => false
        ]);
    
        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Register Member Success. Please wait for the confirmation from email'
        ],201);
    }

    public function update(Request $request, string $id)
    {
        $memberPayment = MemberPayment::find($id);
        if (!$memberPayment) return MessageController::error('Query not found');

        $this->validate($request, [
            'payment_type' => 'required|string',
            'provider_id' => 'required|integer',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'payment_amount' => 'required|integer',
            'payment_proof' => 'image|max:1999|mimes:jpg,png,jpeg',
        ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 2 MB'
        ]);
        
        
        if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $fileName =$request->input('region_id') . '_' . $request->input('account_number') . '_' . $request->input('account_name') . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/member_payment_proofs';
            $image->move($destinationPath,$fileName);
            if(File::exists($destinationPath.'/'.$memberPayment->payment_proof)) {
                File::delete($destinationPath.'/'.$memberPayment->payment_proof);
            }
            
        } else {
            $fileName = $memberPayment->payment_proof;
        }

        $memberPayment->update([
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'payment_proof' =>$fileName,
        ]);

        
    
        return response()->json([
            'data' => $memberPayment,
            'status' => 'success',
            'message' => 'Member Payment has successfuly updated',
        ],200);
    }
    public function updateByUser(Request $request, MemberPayment $memberPayment)
    {
      $this->validate($request, [
        'payment_type' => 'required|string',
        'provider_id' => 'required|integer',
        'account_name' => 'required|string',
        'account_number' => 'required|string',
        'payment_amount' => 'required|integer',
        'payment_proof' => 'image|max:1999|mimes:jpg,png,jpeg',
      ], [
        'payment_proof.uploaded' => 'Maximum transfer proof file size is 2 MB'
      ]);
  
      $memberPayment->update([
        'payment_type' => $request->payment_type,
        'provider_id' => $request->provider_id,
        'account_name' => $request->account_name,
        'account_number' => $request->account_number,
        'payment_amount' => $request->payment_amount,
      ]);
  
      if ($request->hasFile('payment_proof')) {
        if ($memberPayment->payment_proof) {
          $oldImage = storage_path("/public/images/member_payment_proofs/{{$memberPayment->payment_proof}}");
          if (File::exists($oldImage)) {
            Storage::delete($oldImage);
            unlink($oldImage);
            rmdir($oldImage);
          }
          $extension = $request->file('payment_proof')->getClientOriginalExtension();
          $filename = pathinfo($memberPayment->payment_proof, PATHINFO_FILENAME);
  
          $proofNameToStore = $filename . '.' . $extension;
          $request->file('payment_proof')->storeAs('public/images/member_payment_proofs', $proofNameToStore);
        }
  
        $memberPayment->update([
          'payment_proof' => $proofNameToStore,
        ]);
      }
  
      return redirect()->route('dashboard')->with('success', 'Update Success');
    }
    
 
    public function delete (string $id)
    {
        $memberPayment = MemberPayment::find($id);
        if (!$memberPayment) return MessageController::error('Query not found');

        $imagePath = 'storage/member_payment_proofs/'. $memberPayment->payment_proof;
        if(File::exists($imagePath)) {
            File::delete($imagePath);
        }
        $memberPayment->forceDelete();
        return response()->json([
            'data' => $memberPayment,
            'status' => 'success',
            'message' => 'Member Payment has successfuly deleted'
        ],200);

    }
    public function destroy (string $id)
    {
        $memberPayment = MemberPayment::find($id);
        if (!$memberPayment) return MessageController::error('Query not found');
        $memberPayment->delete();
        return response()->json([
            'data' => $memberPayment,
            'status' => 'success',
            'message' => 'Member Payment has successfuly deleted'
        ],200);

    }
    public function restore(string $id){
        $memberPayment = MemberPayment::onlyTrashed()->find($id); 
        if(!$memberPayment) return MessageController::error("Query not found");
        $memberPayment->restore();
        return response()->json([
            'data'=> $memberPayment,
            'status' => 'success',
            'message' => 'Member Payment has successfully restored'
        ],200);
    }

    public function viewReceipt($id)
    {
      @ini_set('max_execution_time', 300);
      @ini_set("memory_limit", "512M");
  
      $registrant = MemberPayment::withTrashed()->where('id', $id)->first();
      $invoiceId = str_pad($registrant->id, 3, '0', STR_PAD_LEFT);
  
      $output = '
          <!DOCTYPE html>
            <html lang="en">
              <head>
                <meta charset="utf-8">
                <title>MEMBER Payment Receipt - ' . $registrant->account_name . ' - ' . $invoiceId . '</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
                <style>
                @page{
                  margin-top: 0;
                  margin-left: 1.2cm;
                  width: 21cm;
                  height: 29.7cm;
                  margin-bottom: 0;
                }
  
                .clearfix:after {
                  content: "";
                  display: table;
                  clear: both;
                }
  
                a {
                  color: #0087C3;
                  text-decoration: none;
                }
  
                body {
                  position: relative;
                  width: 21cm;
                  height: 29.7cm;
                  margin: 0 1px 0 1px;
                  color: #555555;
                  background: #FFFFFF;
                  font-family: "Nunito", sans-serif;
                  font-size: 13px;
                  margin-bottom: 0;
                }
  
                header {
                  width: 95%;
                 
                }
  
                .text-heading  {
  
                   position: absolute;
                   top: 12rem;
                   left: 11rem;
                   right: 11rem;
  
                }
  
                .signText{
                  margin-top:10px;
                  margin-right: 15%;
                  text-align: center;
                }
  
                #logo {
                  float: left;
                 
                }
  
                #logo img {
                  width: 100%;
                 
                }
  
                #details {
                  margin-bottom: 5px;
                }
  
                #client {
                  padding-left: 3px;
                  border-left: 7px solid #6AA84F;
                  float: left;
                }
  
                #client .to {
                  color: #777777;
                }
  
                h2.name {
                  font-size: 1em;
                  font-weight: normal;
                  margin: 0;
                }
  
                #invoice {
                  float: right;
                  text-align: right;
                }
  
                #invoice h1 {
                  color: #0087C3;
                  font-size: 1.6em;
                  line-height: 1em;
                  font-weight: normal;
                  margin: 0  0 8px 0;
                }
  
                #invoice .date {
                  font-size: 1em;
                  color: #777777;
                }
  
                table {
                  width: 90%;
                  border-collapse: collapse;
                  border-spacing: 0;
                  margin-bottom: 1px;
                }
  
                table th,
                table td {
                  padding: 3px;
                  background: #D9D9D9;
                  text-align: center;
                  border-bottom: 1px solid #FFFFFF;
                }
  
                table th {
                  font-weight: normal;
                }
  
                table td {
                  text-align: left;
                }
  
                table td h3{
                  color: #777777;
                  font-size: 1.2em;
                  font-weight: normal;
                  margin: 0 0 0.2em 0;
                }
  
                table .no {
                  color: #FFFFFF;
                  font-size: 1em;
                  background: #D9D9D9;
                }
  
                table .desc {
                  text-align: left;
                }
  
                table .unit {
                  background: #DA8F2C;
                  color: #FFFFFF;
                }
  
                table .total {
                  background: #6AA84F;
                  color: #FFFFFF;
                }
  
                table td.unit,
                table td.qty,
                table td.total {
                  font-size: 1.3em;
                }
  
                table tbody tr:last-child td {
                  border: none;
                }
  
                #signature {
                    display: block;
                    margin-left: auto;
                    margin-right: auto;
                    width: 50%;
                    height: 70px;
                }
  
                table tfoot td{
                  padding: 2px 5px;
                  background: #FFFFFF;
                  border-bottom: none;
                  font-size: 1.3em;
                  white-space: nowrap;
                  border-top: 1px solid #78C9B0;
                }
                
                table tfoot .sub-total{
                  padding: 2px 5px;
                  background: #FFFFFF;
                  border-bottom: none;
                  font-size: 1.3em;
                  white-space: nowrap;
                  /* border-top: 1px solid #78C9B0; */
                }
  
                footer {
                    color: #777777;
                    width: 90%;
                    height: 20px;
                    position: absolute;
                    bottom: 0;
                    padding: 5px 0;
                    text-align: center;
                }
  
                table tfoot tr:first-child td {
                  border-top: none;
                }
  
                table tfoot tr:last-child td {
                  color: #57B223;
                  font-size: 1.3em;
                  border-top: 1px solid #6AA84F
  
                }
  
                table tfoot tr td:first-child {
                  border: none;
                }
  
                #thanks{
                  font-size: 2em;
                  margin-bottom: 50px;
                }
  
                #notices{
                  page-break-before: always;
                  margin-top: 40px;
                  padding-left: 5px;
                  border-left: 5px solid #6AA84F;
                }
  
                #notices .notice {
                  font-size: 1.2em;
                }
  
                .letterFoot{
                  margin-top: 70px;
                  margin-right: 15%;
                  text-align: center;
                  font-size: 10px;
                  display: flex;
                  align-items: end;
                }
                </style>
              </head>
              <header class="clearfix">
                <div id="logo">
                  <img src="https://recruitment.mybnec.org/storage/images/assets/letterhead-presidency-crop.png">
                </div>
              </header>
              <body>
                <main>
                <div class="text-heading">
                  <h1>MEMBER PAYMENT RECEIPT</h1>
               </div>
                  <br>
                  <div id="details" class="clearfix">
                  <br>
                    <div id="client">
                      <div class="to">MEMBER PAYMENT RECEIPT TO:</div>
                      <h2 class="name">' . $registrant->account_name . '</h2>
                      <div class="address">Receipt ID - ' . $invoiceId . '</div>
                      <div class="email">Time: ' . $registrant->created_at . ' (GMT +7)</div>
                    </div>
                  </div>
                  <br><br>
                  <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                      <tr>
                        <th class="unit">PRICE</th>
                        <th class="qty">QUANTITY</th>
                        <th class="total">TOTAL</th>
                      </tr>
                    </thead>	
                    <tbody>
                      <tr>
                        <td class="unit">Rp. ' . number_format($registrant->payment_amount, 0, ',', '.') . '</td>
                        <td class="qty">1 Person</td>
                        <td class="total">Rp. ' . number_format($registrant->payment_amount, 0, ',', '.') . '</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td style="padding-bottom: 25px"></td>
                      </tr>
                      <tr>
                        <td colspan="2"></td>
                        <td colspan="2"><h3>GRAND TOTAL</h3></td>
                        <td>Rp. ' . number_format($registrant->payment_amount, 0, ',', '.') . '</td>
                      </tr>
                    </tfoot>
                  </table>
                  <br>
                    <p>The payment proof has been approved on ' . date("F j, Y H:i", strtotime($registrant->updated_at)) . ' (GMT+7).</p>
                    <br>
                    <div class="signText">
                      Approved By,<br><br>
                      <img src="https://recruitment.mybnec.org/storage/images/assets/pm-liesa-signature.jpg" width="100"><br>
                      <h3>Liesa Aprilianty</h3>
                      Project Manager <br>
                      The 2022 BNEC New Member Recruitment
                    </div>
                   
                    <div class="letterFoot">
                     <b>BINUS English Club (BNEC)</b><br>
                      Jl. U No. D10 Kemanggisan, Palmerah<br>
                      Jakarta Barat 11480<br>
                      www.mybnec.org
                    </div>
                    </main>
              </body>
            </html>
        ';
      $dompdf = new Dompdf();
      $options = $dompdf->getOptions();
      $options->setIsRemoteEnabled(true);
      $dompdf->setOptions($options);
      $dompdf->loadHtml($output);
      $dompdf->setPaper('A4', 'potrait');
      $dompdf->render();
  
      $dompdf->stream("Receipt - " . $registrant->name . ".pdf", array("Attachment" => false));
    }

    public function export($term = null)
    {

        if (!$term) abort(404);

        $termData = Term::find($term);
        @ini_set('max_execution_time', 300);
        @ini_set("memory_limit", "512M");

        return Excel::download(new MemberPaymentExport($term), 'Member Payment - ' . $termData->semester . ' ' . $termData->year . '.xlsx');
    
    }

    public function confirm(string $id)
    {
      $memberPayment = MemberPayment::find($id); 
      if(!$memberPayment) return MessageController::error("Query not found");

      $memberPayment->update(['is_confirmed' => 1]);
      User::find($memberPayment->user_id)->update([
          'role' => 'MEMBER',
          'term_id' => $memberPayment->term_id,
      ]);
      
      $link = route('member-payments.view-receipt', $memberPayment->id);
      $confirmMail = [
          'subject' => "Member Payment Confirmation",
          'body' => "Congratulations! Your Member Payment has been confirmed. We are glad to have you as our new extraordinary member. Check out for more benefits as our member on our website.",
          'link' => "Here is the link to your payment receipt: $link"

      ];
      Mail::to($memberPayment->user->email)->send(new ConfirmMail($confirmMail));
      return response()->json([
          'data' => $memberPayment,
          'status' => 'success',
          'message' => 'Member payment has successfully confirmed' 
      ]);
    }
    public function cancelConfirmation(string $id)
    {
        $memberPayment = MemberPayment::find($id); 
        if(!$memberPayment) return MessageController::error("Query not found");
        
        $memberPayment->update(['is_confirmed' => 0]);
        User::find($memberPayment->user_id)->update([
            'role' => 'USER',
            'term_id' => $memberPayment->term_id,


        ]);

        return response()->json([
            'data' => $memberPayment,
            'status' => 'success',
            'message' => 'Cancel Confirmation Success, Change status back to USER' 
         ]);
        
    }
}
