<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class respuestapreetiquetadoController extends Controller
{
    //End Point de Respuesta de Pre Etiquetado
    public function RecibePreEtiquetado(Request $request)
    {
        try {
            $encode = json_encode($request->data);
            $data = json_decode($encode);
            $resp = "";

            for ($i = 0; $i < count($data); $i++) {
                //echo $json_encode;
                $customFieldID['customFieldID'] = '_fecha_preetiquetado';
                $value['value'] = $data[$i]->fecha_preetiquetado;

                $customFieldID2['customFieldID'] = '_status_etiquetado';
                $value2['value'] = $data[$i]->estatus;

                $customFieldID3['customFieldID'] = '_motivo_rechazo';
                $value3['value'] = $data[$i]->motivo;

                $merge = array_merge($customFieldID, $value);
                $merge2 = array_merge($customFieldID2, $value2);
                $merge3 = array_merge($customFieldID3, $value3);

                $push = [];
                array_push($push, $merge);
                array_push($push, $merge2);
                array_push($push, $merge3);
                $customInformation['customInformation'] = $push;

                //Inicia PATCH por crédito recibido en el JSON de la peticion

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://fcontigodev.sandbox.mambu.com/api/linesofcredit/' . $data[$i]->credito . '/custominformation',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'PATCH',
                    CURLOPT_POSTFIELDS => json_encode($customInformation),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Basic anRvbGVudGlubzo1NGp0Z2tuaFAk'
                    ),
                ));

                $response = curl_exec($curl);

                $resp = $resp . $response;

                curl_close($curl);
            }
            return $resp;
        } catch (\Throwable $th) {
        }

        //return $strEstatus;

    }
}
