<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tokenController extends Controller
{
    //
    public function errortoken()
    {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

}
