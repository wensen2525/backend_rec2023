<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MessageController;
use App\Http\Resources\paymentProviders\PaymentProviderResource;
use App\Http\Resources\paymentProviders\PaymentProviderCollection;

class PaymentProviderController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except('index');
        $this->middleware('IsAdmin')->except('index');
    }
    
    public function index()
    {
        $paymentProviders = PaymentProvider::all();
        if(!$paymentProviders->count())return MessageController::error('No data available');
        return new PaymentProviderCollection($paymentProviders);
    }

    public function show(string $id)
    {
        $paymentProvider = PaymentProvider::find($id);
        if(!$paymentProvider) return MessageController::error("Query not found");
        return new PaymentProviderResource($paymentProvider);
    }

    protected function validatePaymentProvider()
    {
        return request()->validate([
            'name' => 'required|string',
            'type' => 'required|string',
        ]);
    }

    public function store(Request $request)
    {
        $this->validatePaymentProvider();

        PaymentProvider::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Payment Provider has successfully created'
        ],201);
    }

 
  
    public function update(Request $request, string $id)
    {
        $paymentProvider = PaymentProvider::find($id);
        if(!$paymentProvider) return MessageController::error("Query not found");

        $this->validatePaymentProvider();

        $paymentProvider->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

         return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Payment Provider has successfully updated'
        ],200);
    }
 
    public function destroy(string $id)
    {
        $paymentProvider = PaymentProvider::find($id);
        if(!$paymentProvider) return MessageController::error("Query not found");
        $paymentProvider->delete();
        return response()->json([
            'data' => $paymentProvider,
            'status' => 'success',
            'message' => 'Payment Provider has successfully deleted'
        ],200);
    }
}
