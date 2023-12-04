<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use PDO;

class apisetiquetadosController extends Controller
{
    public function AltaPromecapJV()
    {
        return "ALTA PROMECAP JUCAVI";

        $strFechaCierre = "";
        $strJucavi = "";
        $strSP = "";
        $decSaldo = 0.0;
        $intDiasMora = 0;

    }

    public function BajaPromecapMambu()
    {


        $strPacth = "";
        $decSaldo = 0;
        $decTasa = 0;
        $intDiasMora = 0;
        $intNumPagosMin = 0;
        $intNumPagosMax = 0;
        $strUser = 69;

        //List<CreditoMambu> lstCredito = new List<CreditoMambu>();
        //ResponseLista response = new ResponseLista();

        try
        {
            //EtiquetadoBusiness etiquetaBursa = new EtiquetadoBusiness(_appSettingsConnection);
            //EtiquetadoMambuPromecap etiquetaPromecap = new EtiquetadoMambuPromecap(_appSettingsConnection);

            //return "BAJA PROMECAP MAMBU";
            $response = $this->getListaBajaPromecap($strUser);
            //return $response;
            //return $response["strResult"];
            //return $response["strJson"];
            //return $response=>strResult;
            if ($response["strResult"] == "OK") {
                $strPacth = $this->PatchBajaPromecapMambu($response["strJson"], date("Y-m-d"));
                return $strPacth;
                if ($strPacth == "OK") {
                    return array("Proceso terminado correctamente.");
                } else {
                    return array($strPacth);
                }
            } else {
                return array($response->strResult);
            }
        } catch (\Exception $exc) {
            $response->strResult = 'ERROR: ' . $exc->getMessage();
        }
    }

    public function getListaBajaPromecap($strUser)
    {
        try {

            // $stringMambu = '{"customInformation":[{"customFieldID":"_Fecha_baja_etiquetado","value":"2023-11-16"},{"customFieldID":"_IdFondeador","value":"CREDITO' . $nvar . 'REAL"}]}';
            $respuesta = this->enviarSolicitudAPI('_IdFondeador','CREDITO REAL','8a443b7087b44a740187b57d5b272961');

            $respuesta = this->enviarSolicitudAPI('_Fecha_baja_etiquetado','2023-11-16','8a443b7087b44a740187b57d5b272961');
            return $respuesta;
        } catch (\Throwable $th) {
            //throw $th;
        }


        try
        {
            $response = new ResponseLista();
            //Server=fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com;Port=5439;User Id=marcadodev;Password=marcadoDev00;Database=mambu_prod
            $curl = curl_init();
            $hostPSTG = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $dbNamePSTG = 'mambu_prod';
            $userPSTG = 'marcadodev';
            $passwordPSTG = 'marcadoDev00';
            $portPSTG = 5439;

            // Conexión a la base de datos
            $pdoPSTG = new PDO("pgsql:host=$hostPSTG;port=$portPSTG;dbname=$dbNamePSTG", $userPSTG, $passwordPSTG);

            $sqlStatementPostgres = "DROP TABLE IF EXISTS Principal;";

            $statementPostgres = $pdoPSTG->query($sqlStatementPostgres);

            $results = $statementPostgres->fetchAll(PDO::FETCH_ASSOC);

            //return $results;

            // Conexión a la base de datos
            $pdoPSTG = new PDO("pgsql:host=$hostPSTG;port=$portPSTG;dbname=$dbNamePSTG", $userPSTG, $passwordPSTG);

            $sqlStatementPostgres = " create table Principal as " .
                " ( " .
                "    SELECT distinct " .
                "    LC.ENCODEDKEY " .
                "    FROM mambu_prod.client C " .
                "    INNER JOIN mambu_prod.branch B " .
                "    ON B.ENCODEDKEY = C.ASSIGNEDBRANCHKEY " .
                "    inner join camposPersonalizados SUC " .
                "    ON SUC.PARENTKEY = B.ENCODEDKEY " .
                "    INNER JOIN mambu_prod.lineofcredit LC " .
                "    ON LC.CLIENTKEY = C.ENCODEDKEY " .
                "    and LC.id in " .
                "    ( " .
                "       select id_acuerdocredito from mambu_prod.mambu_prod.creditosbaja_excel_promecap " .
                "    ) " .
                "    inner join camposPersonalizados FOND " .
                "    ON FOND.PARENTKEY = LC.ENCODEDKEY " .
                " ); ";

            $statementPostgres = $pdoPSTG->query($sqlStatementPostgres);

            $results = $statementPostgres->fetchAll(PDO::FETCH_ASSOC);

            //return $results;

            // Conexión a la base de datos
            $pdoPSTG = new PDO("pgsql:host=$hostPSTG;port=$portPSTG;dbname=$dbNamePSTG", $userPSTG, $passwordPSTG);

            $sqlStatementPostgres = "select * from Principal; ";

            $statementPostgres = $pdoPSTG->query($sqlStatementPostgres);

            $results = $statementPostgres->fetchAll(PDO::FETCH_ASSOC);

            //return $results;

            if (!empty($results)) {
                //$response->strResult = "OK";
                //$response->strJson = $results;
                return (["strResult" => "OK", "strJson" => $results]);
                //return response()->json(["strResult" => "OK", "strJson" => $results], 200);
            } else {
                //$response->strResult = "No existen créditos por etiquetar.";
                //$response->strJson = "";
                return (["strResult" => "No existen créditos por etiquetar.", "strJson" => $results]);
                //return response()->json(["strResult" => "No existen créditos por etiquetar.", "strJson" => $results], 200);
            }

            //return $response;

        } catch (\Exception $exc) {
            //$response->strResult = 'ERROR: ' . $exc->getMessage();
            return $exc->getMessage();
            // Log the error here if needed
        }

        //return response()->json($response);
    }

    public function PatchBajaPromecapMambu($dtParaEtiquetar, $strFechaCierreFondeador)
    {


        // URL de la API
        $url = 'https://fcontigo.mambu.com/api/linesofcredit/8a443b7087b44a740187b57d5b272961/custominformation';

        // Cabeceras de la solicitud
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic Y29uRm9uZGVhZG9yZXM6TjYjS3V0SEkhcA==',
        ];

        // Cuerpo de la solicitud en formato JSON
        $body = [
            'customInformation' => [
                [
                    'customFieldID' => '_IdFondeador',
                    'value' => 'CREDITO REAL',
                ],
            ],
        ];

        // Inicializar el cliente Guzzle
        $client = new Client();

        // Realizar la solicitud PATCH con Guzzle
        $response = $client->patch($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        // Obtener el contenido de la respuesta
        $contenidoRespuesta = $response->getBody()->getContents();

        return $contenidoRespuesta;
        //**********************************************************ALTERNATIVA NO FUNCIONAL */

        $responceCustom = new ResponceCustomInformation();
        $respuestaGral = new RespuestaGral();
        $strResponse = "";
        $strRutaMambu = "https://fcontigo.mambu.com/api/";
        $strUserMambu = "conFondeadores";
        $strPassMambu = "N6#KutHI!p";
        $contadorCreditosExito = 0;
        $contadorCreditosError = 0;

        try {
            $custom = new RequestCustomInformation();
            $custom->customInformation = array();
            $custom1 = new CustomInformation();
            $custom1->customFieldID = "_Fecha_baja_etiquetado";
            $custom1->Value = $strFechaCierreFondeador;
            $custom2 = new CustomInformation();
            $custom2->customFieldID = "_IdFondeador";
            $custom2->Value = "CREDITO REAL";

            $custom->customInformation[] = $custom1;
            $custom->customInformation[] = $custom2;

            foreach ($dtParaEtiquetar as $item) {
                $sUrlRequest = $strRutaMambu . "linesofcredit/" . $item["encodedkey"] . "/custominformation";

                //return $sUrlRequest;

                $Request = curl_init($sUrlRequest);
                curl_setopt($Request, CURLOPT_USERAGENT, "Client Cert Sample");
                curl_setopt($Request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($Request, CURLOPT_CUSTOMREQUEST, "PATCH");
                $encoded = base64_encode($strUserMambu . ":" . $strPassMambu);
                curl_setopt($Request, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $encoded));

                //return $custom;

                $json = json_encode($custom);
                $json = str_replace("\"Value\"", "\"value\"", $json);
                //$byteArray = utf8_encode($json);

                $byteArray = $string;

                return "exito2";

                //return $string;
                //return $json;

                curl_setopt($Request, CURLOPT_POSTFIELDS, $byteArray);
                curl_setopt($Request, CURLOPT_RETURNTRANSFER, true);

                try
                {
                    return "exito";
                    $Response = curl_exec($Request);

                    if (curl_errno($Request)) {
                        return 'Error al realizar la solicitud: ' . curl_error($Request);
                    } else {
                        // Procesa la respuesta
                        $decodedResponse = json_decode($Response, true);

                        // Haz algo con la respuesta
                        return $decodedResponse;
                        //return $decodedResponse["returnStatus"];
                    }

                    $responceCustom = json_decode($Response);

                    return $responceCustom;

                    if ($responceCustom->returnStatus == "SUCCESS") {
                        return "proceso terminado con exito";
                        $respuestaGral->Mensaje = "";
                        $respuestaGral->Status = true;
                        $contadorCreditosExito = $contadorCreditosExito + 1;
                    } else {
                        $contadorCreditosError = $contadorCreditosError + 1;
                    }
                } catch (\Exception $e) {
                    $contadorCreditosError = $contadorCreditosError + 1;
                }
            }
            return "Creditos por Etiquetar/Desetiquetar: " . count($dtParaEtiquetar->rows) . " Creditos Correctos: " . $contadorCreditosExito . " Creditos Erroneos:" . $contadorCreditosError;
        } catch (\Exception $exc) {
            return "Error al realizar patch";
        }
    }

    public function enviarSolicitudAPI($customFieldID,$value,$encodedkey)
    {
        // URL de la API
        $url = 'https://fcontigo.mambu.com/api/linesofcredit/'.$encodedkey.'/custominformation';

        // Cabeceras de la solicitud
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic Y29uRm9uZGVhZG9yZXM6TjYjS3V0SEkhcA==',
        ];

        // Cuerpo de la solicitud en formato JSON
        $body = [
            'customInformation' => [
                [
                    'customFieldID' => $customFieldID,
                    'value' => $value,
                ],
            ],
        ];

        // Inicializar el cliente Guzzle
        $client = new Client();

        // Realizar la solicitud PATCH con Guzzle
        $response = $client->patch($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        // Obtener el contenido de la respuesta
        $contenidoRespuesta = $response->getBody()->getContents();

        // Puedes imprimir o retornar la respuesta según tus necesidades
        echo $contenidoRespuesta;
    }
}

class ResponceCustomInformation
{
    public $returnCode;
    public $returnStatus;
    public $returnDate;
    public $returnDescription;
}

class RespuestaGral
{
    public $Status;
    public $Mensaje;
    public $ProcesoTerminado;
}
class RequestCustomInformation
{
    public $customInformation;
}
class CustomInformation
{
    public $customFieldID;
    public $Value;
}
class ResponseLista
{
    public $strResult = "";
    public $strJson = array('encodedkey' => '');
}
