<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDO;
use PDOException;
use TheSeer\Tokenizer\Exception;

class reportController extends Controller
{
    public function recuperacioncartera()
    {
        $type = $this->getusertype();
        return view('reportes.reporterecuperacioncartera', compact('type'));
    }

    public function reportes()
    {
        $type = $this->getusertype();
        return view('reportes.reportes', compact('type'));
    }

    public function demografia()
    {
        $type = $this->getusertype();
        return view('reportes.demografia', compact('type'));
    }

    public function cartera()
    {
        $type = $this->getusertype();
        return view('reportes.cartera', compact('type'));
    }

    public function pagos()
    {
        $type = $this->getusertype();
        return view('reportes.pagos', compact('type'));
    }


    public function reportedemografia(Request $request){
        return "Prueba reporte Demografia";
    }
    public function reportecartera(Request $request){
        $date = $request->startDate;
        $resultado = [];
        try {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $database2 = 'movimientos';

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                $pdo = new PDO($dsn, $user, $password);
                // Configurar opciones adicionales si es necesario
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // echo 'Conexión exitosa a PostgreSQL';

                // Ejemplo de consulta
                $query1 = "select * from semantica.creditos where fechacorte = '".$date."';";

                $statement = $pdo->query($query1);
                $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);


                if ($resultado == '') {
                    return [];
                } else {
                    return $resultado;
                }

            } catch (PDOException $e) {
                echo 'Error en consulta: ' . $e->getMessage();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function reportepagos(Request $request){
        return "Prueba reporte Pagos";
    }


    public function sesioncartera()    {
        try {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database2 = 'movimientos';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $dsn2 = "pgsql:host=$host;port=$port;dbname=$database2";
            $pdo2 = new PDO($dsn2, $user, $password);
            // Configurar opciones adicionales si es necesario
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo 'Conexión exitosa a PostgreSQL';

            $query1 = "select  MIN(fecha_etiquetado)  from movimiento_no_nativo.transaccion where  idtipotransaccion in (1, 2)  and id_cuenta = 'SSP303'";
            $statement1 = $pdo2->query($query1);
            $fechamin = $statement1->fetchAll(PDO::FETCH_ASSOC);

            $query2 = "select  MAX(fecha_etiquetado)  from movimiento_no_nativo.transaccion where  idtipotransaccion in (1, 2)  and id_cuenta = 'SSP303'";
            $statement2 = $pdo2->query($query2);
            $fechamax = $statement2->fetchAll(PDO::FETCH_ASSOC);

            $type = $this->getusertype();
            // Crear un array con las variables que deseas pasar a la vista
            $data = [
                'type' => $type,
                'fechamin' => $fechamin,
                'fechamax' => $fechamax,
            ];
            $type = $this->getusertype();
            return view('reportes.reportesesioncartera', ["fechamin"=>$fechamin,"fechamax"=>$fechamax,"type"=>$type]);
        } catch (\Throwable $th) {
            $type = $this->getusertype();

            // En caso de excepción, pasamos la excepción a la vista
            $data = [
                'type' => $type,
                'th' => $th,
            ];

            return view('reportes.reportesesioncartera', $data);
        }
    }

    public function fondeadores()    {
        $type = $this->getusertype();
        return view('reportes.fondeadores.fondeadores', compact('type'));
    }
    public function fondeadoresreport()    {

        $infoReport = [];
        $clientInfo = [];
        $cli = "";
        $sUrlRequest1 = "https://fcontigo.mambu.com/api/creditarrangements:search?limit=700&offset=0&detailsLevel=FULL";

        $request = curl_init($sUrlRequest1);
        ini_set('MAX_EXECUTION_TIME', 600);
        curl_setopt($request, CURLOPT_USERAGENT, "Client Cert Sample");
        curl_setopt($request, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/vnd.mambu.v2+json"));
        curl_setopt($request, CURLOPT_POST, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, '{"filterCriteria":[{"field":"_Datos_Extra_Credit_Arrangements._preetiquetado","operator":"EQUALS","value":"PROMECAP","values":null}]}');
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($request, CURLOPT_USERPWD, "conFondeadores:N6#KutHI!p");

        $strResponse = curl_exec($request);

        if ($strResponse === false) {
            throw new Exception(curl_error($request));
        }

        $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        if ($httpCode == 200) {
            $lstclientesActivos = json_decode($strResponse);

            for ($i = 0; $i < count($lstclientesActivos); $i++) {
                $credito = [];

                // Sección del cliente y el grupo

                $cli = $lstclientesActivos[$i]->holderKey;

                $sUrlRequest = 'https://fcontigo.mambu.com/api/clients/' . $cli . '?detailsLevel=FULL';
                $Request = curl_init($sUrlRequest);
                ini_set('MAX_EXECUTION_TIME', 600);
                curl_setopt($Request, CURLOPT_USERAGENT, "Client Cert Sample");
                curl_setopt($Request, CURLOPT_HTTPGET, true);
                curl_setopt($Request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt(
                    $Request,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Accept: application/vnd.mambu.v2+json",
                        "Authorization: Basic " . base64_encode("conFondeadores:N6#KutHI!p"),
                    )
                );

                try {
                    $Response = curl_exec($Request);
                    $httpCode = curl_getinfo($Request, CURLINFO_HTTP_CODE);

                    if ($httpCode === 200) {
                        $clientInfo = json_decode($Response);
                        // Procesar el resultado...
                    } else {
                        throw new Exception("El método GetClientMAMBU generó el siguiente error: " . $Response . ", ");
                    }
                } catch (Exception $e) {
                    // Manejar la excepción...
                } finally {
                    curl_close($Request);
                }
                try {
                    array_push($credito, $lstclientesActivos[$i]->id);
                    array_push($credito, $lstclientesActivos[$i]->encodedKey);
                    $name = $clientInfo->firstName . " " . $clientInfo->lastName;
                    array_push($credito, $name);
                    if (isset($clientInfo->_IdGrupo_Clients->IdGrupo_Clients)) {
                        $grupo = $clientInfo->_IdGrupo_Clients->IdGrupo_Clients;
                        array_push($credito, $grupo);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_preetiquetado)) {
                        $_preetiquetado = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_preetiquetado;
                        array_push($credito, $_preetiquetado);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_fecha_preetiquetado)) {
                        $_fecha_preetiquetado = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_fecha_preetiquetado;
                        array_push($credito, $_fecha_preetiquetado);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_IdFondeador)) {
                        $_IdFondeador = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_IdFondeador;
                        array_push($credito, $_IdFondeador);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_Fecha_Etiquetado)) {
                        $_Fecha_Etiquetado = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_Fecha_Etiquetado;
                        array_push($credito, $_Fecha_Etiquetado);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_status_etiquetado)) {
                        $_status_etiquetado = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_status_etiquetado;
                        array_push($credito, $_status_etiquetado);
                    } else {
                        array_push($credito, "");
                    }
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_motivo_rechazo)) {
                        $_motivo_rechazo = $lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_motivo_rechazo;
                        array_push($credito, $_motivo_rechazo);
                    } else {
                        array_push($credito, "");
                    }
                    array_push($infoReport, $credito);
                    // array_push($infoReport,$lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_Fecha_baja_etiquetado);
                } catch (\Throwable $th) {
                    $tr = false;
                    if (isset($lstclientesActivos[$i]->_Datos_Extra_Credit_Arrangements->_Fecha_Etiquetado)) {
                        $tr = true;
                    } else {
                        $tr = false;
                    }
                    return $tr;
                }

            }

            return $infoReport;
            // return $lstclientesActivos;
        } else {
            throw new Exception("El método ConsultarTransaccionesBySearchMetod generó el siguiente error: " . $strResponse);
        }

    }

    public function reportesesioncartera(Request $request)    {

        $date = $request->date;
        $resultado = [];
        try {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $database2 = 'movimientos';

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                $pdo = new PDO($dsn, $user, $password);
                // Configurar opciones adicionales si es necesario
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // echo 'Conexión exitosa a PostgreSQL';

                // Ejemplo de consulta
                $query1 = "SELECT distinct LC.ID AS ACUERDO, CV.VALUE AS NoTitularCuenta, COALESCE(CLI.FIRSTNAME,'') || ' ' || COALESCE(CLI.LASTNAME, '') AS NombreTitulaCuenta, CV3.VALUE AS CICLO, B.NAME AS SUCURSAL, AD.REGION AS ESTADO, CASE WHEN CV2.VALUE IS NULL THEN 'CONTIGO' ELSE CV2.VALUE END AS Fondeador,( SELECT sum(LA.principalbalance) FROM mambu_marcado_bursa.loanaccount as LA WHERE LA.lineofcreditkey = min(LC.encodedkey)) AS SALDO_CAPITAL FROM mambu_marcado_bursa.lineofcredit AS LC JOIN mambu_marcado_bursa.client CLI ON CLI.ENCODEDKEY = LC.CLIENTKEY join mambu_marcado_bursa.customfieldvalue CV ON CV.PARENTKEY = CLI.ENCODEDKEY LEFT join mambu_marcado_bursa.customfieldvalue CV2 ON CV2.PARENTKEY = LC.ENCODEDKEY AND CV2.CUSTOMFIELDKEY = '8a44bf8d7f65a97a017f6bd14aa67d23' LEFT JOIN mambu_marcado_bursa.customfield cf ON cf.ENCODEDKEY = CV2.CUSTOMFIELDKEY and cf.type IN ('LINE_OF_CREDIT') LEFT join mambu_marcado_bursa.customfieldvalue CV3 ON CV3.PARENTKEY = LC.ENCODEDKEY AND CV3.CUSTOMFIELDKEY = '8a4422367be572b8017be640f98d5075' LEFT JOIN mambu_marcado_bursa.customfield cf2 ON cf.ENCODEDKEY = CV3.CUSTOMFIELDKEY and cf2.type IN ('Ciclo Grupo') JOIN mambu_marcado_bursa.loanaccount LA_B ON LA_B.ACCOUNTHOLDERKEY = LC.CLIENTKEY JOIN mambu_marcado_bursa.branch B ON B.ENCODEDKEY = LA_B.ASSIGNEDBRANCHKEY JOIN mambu_marcado_bursa.address AD ON AD.PARENTKEY = B.ENCODEDKEY where LC.ID = 'SSP303' group by lc.id, cv.value, cli.firstname, cli.lastname, cv3.value, b.name, ad.region, cv2.value; ";

                $statement = $pdo->query($query1);
                $join1 = $statement->fetchAll(PDO::FETCH_ASSOC);

                $dsn2 = "pgsql:host=$host;port=$port;dbname=$database2";
                $pdo2 = new PDO($dsn2, $user, $password);
                // Configurar opciones adicionales si es necesario
                $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // echo 'Conexión exitosa a PostgreSQL';

                $query2 = "select distinct encodedkey, id_cuenta, fecha_etiquetado  from movimiento_no_nativo.transaccion where to_timestamp(fecha_etiquetado, 'YYYY-MM-DD') = '" . $date . "' and idtipotransaccion in (1, 2) ";
                $statement2 = $pdo2->query($query2);
                $join2 = $statement2->fetchAll(PDO::FETCH_ASSOC);


                for ($i = 0; $i < count($join1); $i++) {
                    for ($j = 0; $j < count($join2); $j++) {
                        if ($join1[$i]["acuerdo"] == $join2[$j]["id_cuenta"]) {

                            $tmp = [];
                            array_push($tmp, $join2[$j]["fecha_etiquetado"]);
                            array_push($tmp, $join2[$j]["id_cuenta"]);
                            array_push($tmp, $join1[$i]["notitularcuenta"]);
                            array_push($tmp, $join1[$i]["nombretitulacuenta"]);
                            array_push($tmp, $join1[$i]["ciclo"]);
                            array_push($tmp, $join1[$i]["sucursal"]);
                            array_push($tmp, $join1[$i]["estado"]);
                            array_push($tmp, $join1[$i]["fondeador"]);
                            array_push($tmp, $join1[$i]["saldo_capital"]);
                            array_push($resultado, $tmp);
                        }
                    }

                }

                if ($resultado == '') {
                    return [];
                } else {
                    return $resultado;
                }

            } catch (PDOException $e) {
                echo 'Error en consulta: ' . $e->getMessage();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function reporterecuperacioncartera(Request $request)    {

        $startDate = $request->startDate . ' 00:00:00';
        $endDate = $request->endDate . ' 23:59:59';

        try {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            // "call  bursa.sp_reporteTransacciones('2023-06-05 00:00:00','2023-06-09 23:59:59')";
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            // Configurar opciones adicionales si es necesario
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo 'Conexión exitosa a PostgreSQL';
            // Ejemplo de consulta
            $queryValidador = "call  bursa.sp_reporteTransacciones('" . $startDate . "','" . $endDate . "')";
            // $query1 = "SELECT * FROM bursa.reporte_transacciones_deposito;";

            $statement = $pdo->query($queryValidador);
            $validador = $statement->fetchAll(PDO::FETCH_ASSOC);

            if ($validador[0]["strresult"] == "PROCEDE") {
                try {
                    $queryPrestamo = "SELECT * FROM bursa.reporte_transacciones;";
                    $statement = $pdo->query($queryPrestamo);
                    $postsPrestamo = $statement->fetchAll(PDO::FETCH_ASSOC);

                    $dataPrestamo = [];
                    foreach ($postsPrestamo as $post) {
                        $nestedData["strFecha"] = $post["fecha"];
                        $nestedData["strIdTransaccion"] = $post["idtransaccion"];
                        $nestedData["strAcuerdo"] = $post["acuerdo"];
                        $nestedData["strNoTitular"] = $post["notitularcuenta"];
                        $nestedData["strNombreTitularCuenta"] = $post["nombretitularcuenta"];
                        $nestedData["strCiclo"] = $post["ciclo"];
                        $nestedData["strSucursal"] = $post["sucursal"];
                        $nestedData["montoCapital"] = $post["montocapital"];
                        $nestedData["montoInteres"] = $post["montointeres"];

                        $nestedData["ivaInteres"] = $post["ivainteres"];
                        $nestedData["interesDiferido"] = $post["interesdiferido"];
                        $nestedData["ivaInteresDiferido"] = $post["ivainteresdiferido"];

                        $nestedData["strTipoTrx"] = $post["tipotrx"];
                        $nestedData["strProducto1"] = $post["producto1"];
                        $nestedData["strProducto2"] = $post["producto2"];
                        $nestedData["strCA"] = $post["ca"];
                        $nestedData["strTipoMov"] = $post["tipomov"];
                        $nestedData["strIdFondeador"] = $post["idfondeador"];
                        $nestedData["strFondeador"] = $post["fondeador"];
                        $nestedData["strTransaccion_vinculada"] = $post["transaccionvinculada"];
                        $nestedData["strIdCuenta"] = $post["idcuenta"];
                        $nestedData["strIdTransaccionPadre"] = $post["transaccionpadre"];
                        $nestedData["strCanalPagoTransaccionPadre"] = $post["canaltransaccionpadre"];
                        $dataPrestamo[] = $nestedData;

                    }

                } catch (\Throwable $th) {
                    echo 'Error en el query1: ' . $th;
                }
                try {
                    $queryDeposito = "SELECT * FROM bursa.reporte_transacciones_deposito;";
                    $statement = $pdo->query($queryDeposito);
                    $postsDeposito = $statement->fetchAll(PDO::FETCH_ASSOC);

                    $dataDeposito = [];
                    foreach ($dataDeposito as $post) {
                        $nestedData = [];
                        $nestedData["fecha"] = $post["fecha"];
                        $nestedData["idtransaccion"] = $post["idtransaccion"];
                        $nestedData["acuerdo"] = $post["acuerdo"];
                        $nestedData["notitularcuenta"] = $post["notitularcuenta"];
                        $nestedData["nombretitularcuenta"] = $post["nombretitularcuenta"];
                        $nestedData["ciclo"] = $post["ciclo"];
                        $nestedData["sucursal"] = $post["sucursal"];
                        $nestedData["importe"] = $post["importe"];
                        $nestedData["tipotrx"] = $post["tipotrx"];
                        $nestedData["producto1"] = $post["producto1"];
                        $nestedData["producto2"] = $post["producto2"];
                        $nestedData["ca"] = $post["ca"];
                        $nestedData["tipomov"] = $post["tipomov"];
                        $nestedData["canal"] = $post["canal"];
                        $nestedData["idfondeador"] = $post["idfondeador"];
                        $nestedData["fondeador"] = $post["fondeador"];
                        $nestedData["transaccionvinculada"] = $post["transaccionvinculada"];
                        $nestedData["idcuenta"] = $post["idcuenta"];
                        $nestedData["strIdTransaccionPadre"] = $post["transaccionpadre"];
                        $nestedData["strCanalPagoTransaccionPadre"] = $post["canaltransaccionpadre"];
                        $dataDeposito[] = $nestedData;
                    }

                } catch (\Throwable $th) {
                    echo 'Error en el query2: ' . $th;
                }
                // $dataPrestamo=[];
                // foreach ($postsPrestamo as $post) {
                //     $nestedData["strFecha"]=$post["fecha"];

                // }

                // return $postsDeposito;
                return response()->json([
                    'dataPrestamo' => $dataPrestamo,
                    'dataDeposito' => $postsDeposito,
                ], 200);

            } else {
                return [];
            }

        } catch (\Throwable $th) {
            echo 'Error al conectar a PostgreSQL: ' . $th;
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
