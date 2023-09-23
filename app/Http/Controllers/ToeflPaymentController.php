<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\Term;
use App\Models\TOEFLDetail;
use App\Models\TOEFLPayment;
use Illuminate\Http\Request;
use App\Mail\TOEFLPaymentMail;
use App\Exports\TOEFLPaymentExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\MessageController;
use App\Http\Resources\toeflPayments\ToeflPaymentResource;
use App\Http\Resources\toeflPayments\ToeflPaymentCollection;

class ToeflPaymentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api')->except(['export']);
    $this->middleware('IsAdmin')->except('viewReceipt', 'export');
  }

  public function getPendings($term){
    $toeflPayments = TOEFLPayment::select('toefl_payments.*')
        ->join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
        ->where('toefl_payments.is_confirmed', 0)->where('toefl_details.term_id', $term)->distinct('toefl_payments.id')->orderBy('toefl_payments.account_name')->paginate(10);
    if(!$toeflPayments->count()) return MessageController::error("No data available");
    return new ToeflPaymentCollection($toeflPayments);
  }

  public function getConfirmeds($term){
    $toeflPayments = TOEFLPayment::select('toefl_payments.*')
        ->join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
        ->where('toefl_payments.is_confirmed', 1)->where('toefl_details.term_id', $term)->distinct('toefl_payments.id')->orderBy('toefl_payments.account_name')->paginate(10);
    if(!$toeflPayments->count()) return MessageController::error("No data available");
    return new ToeflPaymentCollection($toeflPayments);
  }

  public function getRecycleds($term){
    $toeflPayments = TOEFLPayment::select('toefl_payments.*')
        ->join('toefl_details', 'toefl_details.payment_id', 'toefl_payments.id')
        ->where('toefl_details.term_id', $term)->onlyTrashed()->distinct('toefl_payments.id')->orderBy('toefl_payments.account_name')->paginate(10);
    if(!$toeflPayments) return MessageController::error("No data available");
    return new ToeflPaymentCollection($toeflPayments);
  }

  public function index()
  {
      $toeflPayments = TOEFLPayment::withTrashed()->get();
      if(!$toeflPayments->count()) return MessageController::error("No data available");
      return new ToeflPaymentCollection($toeflPayments);
  }

    
    public function show(string $id)
    {
        $toeflPayment = TOEFLPayment::find($id);
        if(!$toeflPayment)return MessageController::error("Query not found");
        return new ToeflPaymentResource($toeflPayment);
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

        if ($image = $request->file('payment-proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $fileName = $request->name[0] . '_' . $request->nim[0] . '_BNEC_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/toefl_payment_proofs';
            $image->move($destinationPath, $fileName);
        }

        TOEFLPayment::create([
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => strtoupper($request->account_name),
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'payment_proof' => $fileName,
            'receiver_id' => $request->receiver_id,
            'is_confirmed' => false
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => "success",
            'message' => "Toefl Payment has successfully created"
        ]);
    }
   
 
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'payment_type' => 'required|string',
            'provider_id' => 'nullable|integer',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'payment_amount' => 'nullable|integer',
            'payment_proof' => 'image|nullable|max:1999|mimes:jpg,png,jpeg',
            'receiver_id' => 'nullable|integer'
          ], [
            'payment_proof.uploaded' => 'Maximum transfer proof file size is 2 MB'
          ]);
          
          $toeflPayment = TOEFLPayment::find($id);
          if(!$toeflPayment) return MessageController::error("Query not found");

          if ($image = $request->file('payment_proof')) {
            $extension = $request->file('payment_proof')->getClientOriginalExtension();
            $fileName = $request->account_name . '_' . time() . '.' . $extension;
            $destinationPath = 'storage/images/toefl_payment_proofs';
            $image->move($destinationPath,$fileName);
            if(File::exists($destinationPath.'/'.$toeflPayment->payment_proof)) {
                File::delete($destinationPath.'/'.$toeflPayment->payment_proof);
            }
            
        } else {
            $fileName = $toeflPayment->payment_proof ;
        }

        $toeflPayment->update([
            'payment_type' => $request->payment_type,
            'provider_id' => $request->provider_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'payment_amount' => $request->payment_amount,
            'receiver_id' => $request->receiver_id,
            'payment_proof' => $fileName,
          ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'TOEFL Payment has successfully updated'
        ]);
    }
 
    public function destroy(string $id)
    {
        $toeflPayment = TOEFLPayment::find($id);
        if(!$toeflPayment)return MessageController::error("Query not found");
        $toeflPayment->delete();
    }
    
    public function restore($id){
        $toeflPayment = TOEFLPayment::onlyTrashed()->find($id);
        if(!$toeflPayment)return MessageController::error("Query not found");
        $toeflPayment->restore();
    }   
  

  public function delete($id)
  {
    $toeflPayment = TOEFLPayment::find($id);
    if(!$toeflPayment)return MessageController::error("Query not found");
    $toeflPayment->forceDelete();
  }

  public function cancel($id)
  {
    $toeflPayment = TOEFLPayment::find($id);
    if(!$toeflPayment)return MessageController::error("Query not found");
      $toeflPayment->update([
        'is_confirmed' => 0
      ]);

      return response()->json([
        'data' => $toeflPayment,
        'status' => 'success',
        'message' => 'TOEFL payment has successfully cancelled' 
      ]);
  }

  public function confirm($id)
  {
    $toeflPayment = TOEFLPayment::find($id);
    if(!$toeflPayment)return MessageController::error("Query not found");
      $toeflPayment->update([
        'is_confirmed' => 1
      ]);
      $guidebook = "https://bit.ly/englishvitGuideBook";
      $confirmationForm = "https://bit.ly/EnglishvitAccountConfirmation";



      foreach ($toeflPayment->details as $detail) {

        $receipt = route('toefl-payments.receipt', $detail->id);

        $toeflMail = [
          'subject' => "TOEFL Payment Confirmation",
          'body' => "Congratulations! Your TOEFL Payment has been confirmed. Check out for more information on our website.",
          'receipt' =>  "Here is the link to your payment receipt: <br/>" . $receipt,
          'guidebook' => "In this TOEFL test, we are collaborating with Englishvit. Therefore you can make an Englishvit account first by following the steps in this guidebook <br/> " . $guidebook,
          'confirmationForm' => "After creating your Englishvit account, please upload the screenshot of your account in this Google Form <br/>" . $confirmationForm,
          'lineGroup' => "For more information, you can join this Line group:<br/> " . $detail->toeflShift->line_group,

        ];
        Mail::to($detail->user->email)->send(new TOEFLPaymentMail($toeflMail));
        return response()->json([
          'data' => $toeflPayment,
          'status' => 'success',
          'message' => 'TOEFL payment has successfully confirmed' 
        ]);
      }
  }

  public function export($term = null)
  {
    @ini_set('max_execution_time', 300);
    @ini_set("memory_limit","512M");
    if (!$term) abort(404);
    
    $termData = Term::find($term);

    // if ($this->validateAccess()) {

      @ini_set('max_execution_time', 300);
      @ini_set("memory_limit", "512M");

      return Excel::download(new TOEFLPaymentExport($term), 'TOEFL Payment - ' . $termData->semester . ' ' . $termData->year . '.xlsx');
    // } else {
      // return redirect()->route('dashboard')->with('error', 'Unauthorized Access');
    // }
  }
  public function viewReceipt(TOEFLDetail $toeflDetail)
  {


    @ini_set('max_execution_time', 300);
    @ini_set("memory_limit", "512M");

    $registrant = $toeflDetail->toeflPayment;

    $toeflSchedule = $toeflDetail->toeflShift->shift;

    $invoiceId = str_pad($registrant->id, 3, '0', STR_PAD_LEFT);
    if ($registrant->payment_amount > 86000) {
      $qty = 2;
    } else {
      $qty = 1;
    }


    $output = '
          <!DOCTYPE html>
            <html lang="en">
              <head>
                <meta charset="utf-8">
                <title>TOEFL Payment Receipt - ' . $registrant->account_name . ' - ' . $invoiceId . '</title>
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
                  <h1>TOEFL PAYMENT RECEIPT</h1>
               </div>
                  <br>
                  <div id="details" class="clearfix">
                  <br>
                    <div id="client">
                      <div class="to">TOEFL PAYMENT RECEIPT TO:</div>
                      <h2 class="name">' . $registrant->account_name . '</h2>
                      <div class="address">Receipt ID - ' . $invoiceId . '</div>
                      <div class="email">Time: ' . $registrant->created_at . ' (GMT +7)</div>
                    </div>
                  </div>
                  <br><br>
                  <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                      <tr>
                        <th class="schedule">TEST SCHEDULE</th>
                        <th class="unit">PRICE</th>
                        <th class="qty">QUANTITY</th>
                        <th class="total">TOTAL</th>
                      </tr>
                    </thead>	
                    <tbody>
                      <tr>
                        <td class="schedule"> ' .  $toeflSchedule . '</td>
                        <td class="unit">Rp. ' . number_format($registrant->payment_amount, 0, ',', '.') . '</td>
                        <td class="qty">' . $qty . ' Person</td>
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

  
}
