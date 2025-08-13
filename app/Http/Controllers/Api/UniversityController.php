<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function getUniversities()
    {
        $universities = University::select('id', 'name')->get();
        return response()->json($universities, 200);
    }
}