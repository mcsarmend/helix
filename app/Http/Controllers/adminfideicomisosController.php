<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use PDO;
use PDOException;
use App\Models\fideicomisos;

class adminfideicomisosController extends Controller
{
    public function alta()
    {
        $type = $this->getusertype();
        return view('adminfideicomisos.alta', compact('type'));
    }
    public function baja()
    {
        $type = $this->getusertype();

        $fideicomisos = fideicomisos::select('id', 'nombre')
        ->orderBy('nombre', 'asc')
        ->get();
        return view('adminfideicomisos.baja',  ['fideicomisos' => $fideicomisos,'type' => $type ]);
    }
    public function edicion()
    {
        $type = $this->getusertype();

        $fideicomisos = fideicomisos::select('id', 'nombre')
        ->orderBy('nombre', 'asc')
        ->get();
        return view('adminfideicomisos.edicion',  ['fideicomisos' => $fideicomisos,'type' => $type ]);
    }

    public function accionalta(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required',
            ]);
            $fideicomiso = new fideicomisos();
            $fideicomiso->nombre = $request->name;
            $fideicomiso->save();
            return response()->json(['message' => 'Fideicomiso creado correctamente'], 200);
        } catch (Exception $e) {
            // Devolver una respuesta de error
            return response()->json(['message' => 'Error al crear el fideicomiso'], 500);
        }
    }
    public function accionedicion(Request $request)
    {

        try {
            $idEncriptado = $request->id;
            $idDesencriptado = Crypt::decrypt($idEncriptado);
            $fideicomiso = fideicomisos::findOrFail($idDesencriptado);
            $fideicomiso->nombre = $request->newname;
            $fideicomiso->save();

            return response()->json(['message' => 'Fideicomiso actualizado correctamente'], 200);
        } catch (Exception $e) {

            return response()->json(['message' => 'Error al actualizar el fideicomiso'], 500);
        }
    }
    public function accionbaja(Request $request)
    {

        try {

            $idEncriptado = $request->id;
            $idDesencriptado = Crypt::decrypt($idEncriptado);
            $fideicomiso = fideicomisos::findOrFail($idDesencriptado)->delete();
            $mess = 'Fideicomiso eliminado correctamente';
            return response()->json(['message' => $mess], 200);
        } catch (Exception $e) {
            // Devolver una respuesta de error
            return response()->json(['message' => 'Error al eliminar el fideicomiso'], 500);
        }
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
