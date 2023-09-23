<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\terms\TermCollection;

class TermController extends Controller
{
    public function __construct(){
        $this->middleware(['auth:api','IsAdmin'])->except(['index']);
    }
    
    public function index()
    {
        $terms = Term::all();
        if(!$terms->count()) return MessageController::error("No data available");
        return new TermCollection($terms);
    }
 

    /**
     * Store a newly created resource in storage.
     */
    public function validateRequest($request){
        $request->validate([
            'year' => 'required|numeric',
            'semester' => 'required|string',
        ]);
        
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
        Term::create([
            'semester' => $request->semester,
            'year' => $request->year,
        ]);
        return response()->json($request->all());
    }

    
    
    public function show(string $id)
    {
        $term = Term::find($id);
        if(!$term) return MessageController::error("Query not found");
        return response()->json($term);
    }

 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validateRequest($request);
        $terms = Term::find($id);
        if(!$terms)return MessageController::error("Query not found");

        $terms->update([
            'semseter' => $request->semester,
            'year' => $request->year,
        ]);
        return response()->json($terms);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $term = Term::find($id);
        if(!$term)return MessageController::error("Query not found");
        $term->delete();
        return response()->json($term);
        
    }
}
