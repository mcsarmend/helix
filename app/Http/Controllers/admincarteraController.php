<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDO;
use PDOException;
use App\Models\fideicomisos;

class admincarteraController extends Controller
{

    public function preetiquetado()
    {
        $type = $this->getusertype();
        $fideicomisos = fideicomisos::select('id', 'nombre')
        ->orderBy('nombre', 'asc')
        ->get();
        return view('admincartera.preetiquetado',  ['fideicomisos' => $fideicomisos,'type' => $type ]);
    }
    public function etiquetado()
    {
        $type = $this->getusertype();
        $fideicomisos = fideicomisos::select('id', 'nombre')
        ->orderBy('nombre', 'asc')
        ->get();
        return view('admincartera.etiquetado',  ['fideicomisos' => $fideicomisos,'type' => $type ]);
    }
    public function baja()
    {
        $type = $this->getusertype();
        $fideicomisos = fideicomisos::select('id', 'nombre')
        ->orderBy('nombre', 'asc')
        ->get();
        return view('admincartera.baja',  ['fideicomisos' => $fideicomisos,'type' => $type ]);
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
