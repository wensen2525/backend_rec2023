<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MessageController;
use App\Http\Resources\environments\EnvironmentResource;
use App\Http\Resources\environments\EnvironmentCollection;

class EnvironmentController extends Controller
{
    public function __construct(){
        $this->middleware(['auth:api'])->except(['index', 'show']);
        $this->middleware(['IsAdmin'])->except(['index', 'show']);

    }
    
    public function index()
    {
        $environments = Environment::all();
        if(!$environments->count()) return MessageController::error("No data available");
        return new EnvironmentCollection($environments);
    }

    public function show(string $id)
    {
        $environment = Environment::find($id);
        if (!$environment) return MessageController::error('Query not found');
        return new EnvironmentResource($environment);

    }

    protected function validateEnvironment(Request $request)
    {
        $request->validate([
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string'
        ]);
    }

 
    public function update(Request $request, string $id)
    {
        $this->validateEnvironment($request);
        $environment = Environment::find($id);
        if (!$environment) return MessageController::error('Query not found');

        $environment->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ]);

        return response()->json([
            'data' => $request->all(),
            'status' => 'success',
            'message' => 'Environment has succesfully updated'
        ]);
    }

}
