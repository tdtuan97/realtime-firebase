<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    public function realtimeData(){
        return view('home_realtime');
    }
    public function chartData(){
        return view('home');
    }
    public function regression(){
        $arrayYear = [];
        for ($i = 1960; $i < 2015; $i++){
            array_push($arrayYear, $i);
        }
        return view('regression')->with('arrayYear', $arrayYear);
    }
    public function cloneData(){
        return view('clone_data');
    }
}
