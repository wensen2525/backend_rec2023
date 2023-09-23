<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public static function  error($message){
        return response()->json([
            'status' => 'error',
            'message' => $message
        ]);
    }
}
