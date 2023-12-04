<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class anexosController extends Controller
{
    public function anexos()
    {
        $type = $this->getusertype();
        return view('anexos', compact('type'));
    }
    public function getusertype()
    {
        if (Auth::check()) {
            $type = Auth::user()->type;
            return $type;
        } else {
            return "Usuario no autenticado.";
        }
    }
}
