<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeightNoteController extends Controller
{
    public function index()
    {
        return view('weight_note.index');
    }
}