<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\logs;

class logsController extends Controller
{
    // Método Get
    public function get(){
        try {
            $data = logs::get();
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Métdo Get BY id
    public function getById($id){
        try {
            $data = logs::find($id);
            if(!$data) {
                return response()->json(['error' => 'Bitácora no encontrada'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
