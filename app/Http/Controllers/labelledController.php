<?php

namespace App\Http\Controllers;

use App\Models\historicoetiquetados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDO;

class labelledController extends Controller
{

    private $fechaActual;
    private $fechaMenosUnDia;

    public function __construct()
    {
        // Obtiene la fecha y hora actual
        $fechaActual = date("Y-m-d H:i:s");

        // Resta 6 horas a la fecha actual
        $fechaMenos6Horas = date("Y-m-d H:i:s", strtotime($fechaActual . " -6 hours"));

        // Resta 1 día y 6 horas a la fecha actual
        $fechaMenosUnDia6Horas = date("Y-m-d H:i:s", strtotime($fechaActual . " -1 day -6 hours"));

        $fechaMenos6HorasFormateada = substr($fechaMenos6Horas, 0, 10);
        $fechaMenosUnDia6HorasFormateada = substr($fechaMenosUnDia6Horas, 0, 10);

        // Calcular la fecha y hora actual
        $this->fechaActual = $fechaMenos6HorasFormateada;

        // Calcular la fecha actual menos 1 día y 6 horas
        $this->fechaMenosUnDia = $fechaMenosUnDia6HorasFormateada;
    }

    public function jucavibursa()
    {
        $type = $this->getusertype();
        return view('etiquetado.jucavi.bursa', compact('type'));
    }
    public function jucavipromecap()
    {
        $type = $this->getusertype();
        return view('etiquetado.jucavi.promecap', compact('type'));
    }
    public function jucaviblao()
    {
        $type = $this->getusertype();
        return view('etiquetado.jucavi.blao', compact('type'));
    }
    public function mambubursa()
    {
        $type = $this->getusertype();
        return view('etiquetado.mambu.bursa', compact('type'));
    }
    public function mambupromecap()
    {
        $type = $this->getusertype();
        return view('etiquetado.mambu.promecap', compact('type'));
    }
    public function mambublao()
    {
        $type = $this->getusertype();
        return view('etiquetado.mambu.blao', compact('type'));
    }
    public function mambumintos()
    {
        $type = $this->getusertype();
        return view('etiquetado.mambu.mintos', compact('type'));
    }
    public function promecap_preetiequetado_mambu()
    {
        try {

            $dayOfWeek = date('N');
            if ($dayOfWeek >= 6) {
                // Es fin de semana (sábado o domingo)
                return response()->json(['error' => 'No se realiza etiquetado con corte de fin de semana'], 400);
            } else {
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapM/PreetiquetadoPromecapMambu/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                    ),
                ));

                $response = curl_exec($curl);

                if (strpos($v[0], "Error") !== false) {
                    return response()->json(['error' => $v[0]], 400);

                } else {
                    return response()->json(['success' => $v[0]], 200);
                }
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }

    }
    public function bajapromecapmambu(Request $request)
    {

        /*Fechas*/
        $fechaActual = $this->fechaActual;
        $fechaMenosUnDia = $this->fechaMenosUnDia;

        $lstCreditos = $request->baja;
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

            $query = "DELETE FROM mambu_prod.mambu_prod.creditosbaja_excel_promecap; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($lstCreditos as $id) {
                $query = "INSERT INTO mambu_prod.mambu_prod.creditosbaja_excel_promecap (id_acuerdocredito) VALUES (" . $id . ")";
                $statement = $pdo->query($query);
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            //**************************************************API PARA LA BAJA */
            $curl = curl_init();
            $url = 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapM/BajaPromecapMambu/770';
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            // ************************* GUARDAR EN TABLA ****************

            $cadena = json_encode($lstCreditos);
            $array = "'" . $cadena . "'";
            $fechaActual = $this->fechaActual;
            $user = Auth::user();
            $registro = new historicoetiquetados;
            $registro->fecha = $fechaActual;
            $registro->creditos = $array;
            $registro->idusuario = $user->id;
            $registro->fondeadoranterior = '10';
            $registro->fondeadornuevo = '1';
            $registro->sistema = 'MAMBU';
            $registro->save();

            // ************************* GUARDAR EN TABLA ****************

            return response()->json(['success' => "Baja realizada correctamente"], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);

        }

    }

    public function etiquetadopromecapmambu(Request $request)
    {

        $mambu = $request->mambu;

        $curl = curl_init();
        try {
            // ------------------------------EMPIEZA ETIQUETADO MAMBU----------------------------------

            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlStatement = "delete from mambu_prod.creditos_excel_promecap;\n";

            $statement = $pdo->query($sqlStatement);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($mambu as $item) {
                $nsqlStatement = "insert into mambu_prod.creditos_excel_promecap (id_acuerdocredito) values ('" . $item . "');";

                $statement = $pdo->query($nsqlStatement);
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapM/AltaPromecapMambu/843',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                ),
            ));

            $response = curl_exec($curl);

            // ************************* GUARDAR EN TABLA ****************

            $cadena = json_encode($mambu);
            $array = "'" . $cadena . "'";
            $fechaActual = $this->fechaActual;
            $user = Auth::user();
            $registro = new historicoetiquetados;
            $registro->fecha = $fechaActual;
            $registro->creditos = $array;
            $registro->idusuario = $user->id;
            $registro->fondeadoranterior = '1';
            $registro->fondeadornuevo = '10';
            $registro->sistema = 'MAMBU';
            $registro->save();

            // ************************* GUARDAR EN TABLA ****************

            // ------------------------------TERMINA ETIQUETADO MAMBU----------------------------------

            return response()->json(['success' => "Etiquetado realizado correctamente"], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }
    }

    public function etiquetadopromecapjucavi(Request $request)
    {
        $creditos = 0;
        try {

            // ------------------------------EMPIEZA ETIQUETADO JUCAVI----------------------------------

            $jucavi = $request->jucavi;
            $creditos = 0;
            $creditosXetiquetarSF = implode(', ', $jucavi);

            // Formateamos la cadena resultante entre corchetes
            $creditosXetiquetar = "[$creditosXetiquetarSF]";

            // ------------------------------EMPIEZA ETIQUETADO JUCAVI----------------------------------

            $jucavi = $request->jucavi;
            /*Fechas*/
            $fechaActual = $this->fechaActual; // Accede a la propiedad correcta
            $fechaMenosUnDia = $this->fechaMenosUnDia; // Accede a la propiedad correcta

            $curl = curl_init();
            $hostODS = 'fcods.trafficmanager.net';
            $dbNameODS = 'cartera_ods';
            $userODS = 'hmonroy';
            $passwordODS = 'Monroy2011@';
            $portODS = 3306;
            // Conexión a la base de datos
            $pdoODS = new PDO("mysql:host=$hostODS;port=$portODS;dbname=$dbNameODS", $userODS, $passwordODS);

            foreach ($jucavi as $id) {
                $sqlStatementJucavi = 'INSERT INTO d_etiquetado_previopromecap (ep_num_credito, ep_fecha_etiquetado,ep_fechamov) VALUES ("' . $id . '", "' . $fechaMenosUnDia . '", "' . $fechaActual . '");';

                $statementJucavi = $pdoODS->query($sqlStatementJucavi);
                $resultJucavi = $statementJucavi->fetchAll(PDO::FETCH_ASSOC);
            }

            $curl = curl_init();
            // https://fcetiquetado.azurewebsites.net/api/EtiquetadoPromecapJ/AltaPromecapJV/770
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapJ/AltaPromecapJV/770',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            $creditos = count($jucavi);

            // ************************* GUARDAR EN TABLA ****************

            $cadena = json_encode($jucavi);
            $array = "'" . $cadena . "'";
            $fechaActual = $this->fechaActual;
            $user = Auth::user();
            $registro = new historicoetiquetados;
            $registro->fecha = $fechaActual;
            $registro->creditos = $array;
            $registro->idusuario = $user->id;
            $registro->fondeadoranterior = '1';
            $registro->fondeadornuevo = '10';
            $registro->sistema = 'JUCAVI';
            $registro->save();

            // ************************* GUARDAR EN TABLA ****************
            return response()->json(['success' => "Cantidad de creditos etiquetados:  " . $creditos], 200);

            curl_close($curl);
            // ------------------------------TERMINA ETIQUETADO JUCAVI----------------------------------
        } catch (\Throwable $th) {
            error_log($th->getMessage()); // Obtener el mensaje de error de la excepción
            return response()->json(['error' => 'Se produjo un error inesperado' . $th->getMessage()], 401);
        }
    }

    public function blao_preetiequetado_mambu()
    {
        try {

            $dayOfWeek = date('N');
            if ($dayOfWeek >= 6) {
                // Es fin de semana (sábado o domingo)
                return response()->json(['error' => 'No se realiza etiquetado con corte de fin de semana'], 400);
            } else {
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoBlaoM/PreetiquetadoBlaoMambu/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                    ),
                ));

                $response = curl_exec($curl);

                if (strpos($v[0], "Error") !== false) {
                    return response()->json(['error' => $v[0]], 401);

                } else {
                    return response()->json(['success' => $v[0]], 200);
                }
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }

    }
    public function bajablaomambu(Request $request)
    {

        /*Fechas*/
        $fechaActual = $this->fechaActual;
        $fechaMenosUnDia = $this->fechaMenosUnDia;
        $lstCreditos = $request->baja;
        $strFondedor = "CREDITO REAL";
        $strFondeadorAnterior = "BLAO";

        try {
            // Conexion a mambu Prod
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            // Configurar opciones adicionales si es necesario
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo 'Conexión exitosa a PostgreSQL';

            $queryValidaBaja = "select * from bursa.creditos_etiquetados" .
                " where fondeador = '" . $strFondedor . "'" .
                " and fondeadoranterior = '" . $strFondeadorAnterior . "'" .
                " and to_char(fechaetiquetado, 'YYYY-MM-DD') = '" . $fechaActual . "'";
            $statementValidaBaja = $pdo->query($queryValidaBaja);
            $resultValidaDia = $statementValidaBaja->fetchAll(PDO::FETCH_ASSOC);

            // Validar si es buen día para hacer baja

            $queryValidaDia = "call bursa.sp_validafechaejecucion(5, 'B','" . $fechaActual . "');";
            $statementValidaDia = $pdo->query($queryValidaDia);
            $resultValidaDia = $statementValidaDia->fetchAll(PDO::FETCH_ASSOC);
            if ($resultValidaDia[0]["strresult"] == "NO PROCEDE") {
                return response()->json(['error' => "Hoy no es un día para realizar la baja."], 400);
            } else {
                // Verifica si existe baja
                if ($resultValidaDia) {
                    $queryValidaBaja = "select * from bursa.creditos_etiquetados" .
                        " where fondeador = '" . $strFondedor . "'" .
                        " and fondeadoranterior = '" . $strFondeadorAnterior . "'" .
                        " and to_char(fechaetiquetado, 'YYYY-MM-DD') = '" . $fechaActual . "'";
                    $statementValidaBaja = $pdo->query($queryValidaBaja);
                    $resultValidaDia = $statementValidaBaja->fetchAll(PDO::FETCH_ASSOC);

                    if ($resultValidaDia != []) {
                        return response()->json(['error' => "La baja ya fue realizada."], 400);
                    } else {
                        // Se realizará la baja
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoBlaoJ/BajaPromecapBLaO/1/843',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_HTTPHEADER => array(
                                'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                            ),
                        ));

                        $response = curl_exec($curl);

                        return response()->json(['success' => $response], 200);

                    }

                }
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);

        }

    }
    public function etiquetadoblaomambu(Request $request)
    {

        $mambu = $request->mambu;
        $curl = curl_init();
        try {
            // ------------------------------EMPIEZA ETIQUETADO MAMBU----------------------------------

            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlStatement = "delete from mambu_prod.creditos_excel_blao;\n";

            $statement = $pdo->query($sqlStatement);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($mambu as $item) {
                $sqlStatement = "insert into mambu_prod.creditos_excel_blao (id_acuerdocredito) values ('" . $item . "');\n";
                $statement = $pdo->query($sqlStatement);
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoBlaoM/AltaBlaoMambu/770',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                ),
            ));

            $response = curl_exec($curl);

            // ************************* GUARDAR EN TABLA ****************

            $cadena = json_encode($mambu);
            $array = "'" . $cadena . "'";
            $fechaActual = $this->fechaActual;
            $user = Auth::user();
            $registro = new historicoetiquetados;
            $registro->fecha = $fechaActual;
            $registro->creditos = $array;
            $registro->idusuario = $user->id;
            $registro->fondeadoranterior = '1';
            $registro->fondeadornuevo = '17';
            $registro->sistema = 'MAMBU';
            $registro->save();

            // ************************* GUARDAR EN TABLA ****************

            // ------------------------------TERMINA ETIQUETADO MAMBU----------------------------------
            return response()->json(['success' => "Etiquetado realizado correctamente" . $response . $sqlStatement], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }
    }

    public function etiquetadomintos(Request $request)
    {
        return "Etquetado Mintos";

    }

    public function mintos_preetiquetado(Request $request)
    {
        try {
            // Conexion a mambu Prod
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            // Configurar opciones adicionales si es necesario
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo 'Conexión exitosa a PostgreSQL';

            $sqlLista = "select distinct CVF_E4.value, L.id, L.encodedkey, c2.value, c3.value, CVF_E4.value, L.amount " .
                "from mambu_prod.lineofcredit L " .
                "join mambu_prod.loanaccount lo " .
                "on lo.lineofcreditkey = L.encodedkey " .
                "join mambu_prod.customfieldvalue c2 " .
                "ON c2.PARENTKEY = L.ENCODEDKEY AND c2.CUSTOMFIELDKEY = '8a4442f87ffa68db017ffba97fc44ed3' " .
                "join mambu_prod.customfield C " .
                "ON C.ENCODEDKEY = C2.CUSTOMFIELDKEY and c.type IN ('LINE_OF_CREDIT') " .
                "left JOIN mambu_prod.customfield as CF_E " .
                "ON CF_E.ID = '_Fecha_Etiquetado' " .
                "join mambu_prod.customfieldvalue c3 " .
                "ON c3.PARENTKEY = L.ENCODEDKEY AND c3.CUSTOMFIELDKEY = '8a442e697ddb93e7017dddbcc99720bf' " .
                "left JOIN mambu_prod.customfield as CF_E3 " .
                "ON CF_E3.ID = '_IdFondeador' " .
                "left JOIN mambu_prod.customfieldvalue AS CVF_E " .
                "ON CVF_E.CUSTOMFIELDKEY = CF_E.ENCODEDKEY and CVF_E.PARENTKEY = L.ENCODEDKEY " .
                "left join mambu_prod.mambu_prod.client cli " .
                "on L.clientkey = cli.encodedkey " .
                "left join mambu_prod.customfield as c4 " .
                "on c4.id = 'IdGrupo_Clients' " .
                "left JOIN mambu_prod.customfieldvalue AS CVF_E4 " .
                "on CVF_E4.parentkey=cli.encodedkey and CVF_E4.customfieldkey = c4.encodedkey " .
                "where c3.value = 'CREDITO REAL' " .
                "and L.amount between 194.80 and 2366722.60";

            $statementLista = $pdo->query($sqlLista);
            $posts = $statementLista->fetchAll(PDO::FETCH_ASSOC);

            // Inicia preetiquetado
            /*Fechas*/
            $fechaActual = $this->fechaActual;
            $fechaMenosUnDia = $this->fechaMenosUnDia;
            $postfields = '{
            "customInformation": [
                {
                    "customFieldID": "_fecha_preetiquetado",
                    "value": ' . $fechaActual . '
                },
                {
                    "customFieldID": "_IdFondeador",
                    "value": "MINTOS"
                }
            ]
        }';
            $curl = curl_init();
            foreach ($posts as $post) {
                $url = 'https://fcontigo.mambu.com/api/linesofcredit/' . $post["encodedkey"] . '/custominformation';

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'PATCH',
                    CURLOPT_POSTFIELDS => $postfields,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Basic Y29uRm9uZGVhZG9yZXM6TjYjS3V0SEkhcA==',
                    ),
                ));

                $response = curl_exec($curl);
                return response()->json(['success' => "Preetiquetado realizado correctamente"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }
    }

    public function preeliminarjucaviblao(Request $request)
    {

        // Reporte previo
        $lista = [];
        $fechaActual = date("Y-m-d");
        $fechaMenosUnDia = date("Y-m-d", strtotime("-1 day", strtotime($fechaActual))); // Resta un día a la fecha actual
        $lista = $this->getListaAltaBlaoJ($fechaMenosUnDia); //
        return $lista;

    }

    public function preetiquetadoblaojucavi(Request $request)
    {

        try {
            $host = 'fcods.trafficmanager.net';
            $dbName = 'clientes_ods';
            $user = 'hmonroy';
            $password = 'Monroy2011@';
            $port = 3306;
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            $lstcreditos = $request->lstcreditos;
            $dayOfWeek = date('N');
            /*Fechas*/
            $fechaActual = $this->fechaActual;
            $fechaMenosUnDia = $this->fechaMenosUnDia;
            if ($dayOfWeek >= 6) {
                // Es fin de semana (sábado o domingo)
                return response()->json(['error' => 'No se realiza etiquetado con corte de fin de semana.'], 400);
            } else {
                $strValidaCierre = $this->verificaEtiquetado($fechaActual, 17);
                if ($strValidaCierre == "Continua.") {

                    foreach ($lstcreditos as $id) {
                        $sqlStatementJucaviBlao = "USE cartera_ods; INSERT INTO d_etiquetado_previoblao (ep_num_credito, ep_fecha_etiquetado, ep_fechamov) VALUES ('$id', '$fechaMenosUnDia', '$fechaActual');";
                        $statement = $pdo->query($sqlStatementJucaviBlao);
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoBlaoJ/AltaBlaoJV/770',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                    ));

                    $response = curl_exec($curl);

                    $creditos = count($jucavi);
                    if ($strResult == "ETIQUETADO OK") {
                        return response()->json(['success' => "Cantidad de creditos etiquetados:  " . $creditos], 200);
                    } else {
                        return response()->json(['error' => 'Se produjo un error inesperado' . $th->getMessage()], 401);
                    }

                } else {

                    return response()->json(['error' => 'El archivo con los créditos autorizados por ACFIN ya fueron enviados y etiquetados.'], 400);
                }

            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }

    }

    public function bajablaojucavi(Request $request)
    {
        try {
            /*Fechas*/
            $fechaActual = $this->fechaActual;
            $fechaMenosUnDia = $this->fechaMenosUnDia;

            $lstcreditos = $request->lstcreditos;
            $queryValidaDia = "call bursa.sp_validafechaejecucion(5, 'B','" . $fechaActual . "');";
            $statementValidaDia = $pdo->query($queryValidaDia);
            $resultValidaDia = $statementValidaDia->fetchAll(PDO::FETCH_ASSOC);
            if ($resultValidaDia[0]["strresult"] == "NO PROCEDE") {
                return response()->json(['error' => "Hoy no es un día para realizar la baja."], 400);
            } else {
                $strValidaCierre = $this->validaBaja($fechaActual, 1, 17);
                if ($strValidaCierre == "Continua.") {

                    $sqlStatementJucaviBlao = "use cartera_ods; INSERT INTO d_etiquetado_previoblao_baja (ep_num_credito, ep_fecha_etiquetado,ep_fechamov) VALUES  \n";

                    foreach ($lstcreditos as $id) {
                        $sqlStatementJucaviBlao .= '("' . $id . '", "' . $fechaMenosUnDia . '", "' . $fechaActual . '"),';
                    }

                    $sqlStatementJucaviBlao = rtrim($sqlStatementJucaviBlao, ',');
                    $sqlStatementJucaviBlao .= ';';

                    $statement = $pdo->query($sqlStatementJucaviBlao);
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                    return response()->json(['success' => "Baja realizada correctamente correctamente"], 200);

                } else {
                    return response()->json(['error' => 'El archivo con los créditos autorizados por ACFIN ya fueron enviados y etiquetados.'], 400);
                }

            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }

    }

    public function promecap_preetiequetado_jucavi()
    {
        //SECCION PARA LOS CALCULOS
        try {

            $fechaActual = date("Y-m-d");
            //***************************************************EMPIEZA PREPARACION DE LOS JSON ***************************************** */
            // CONEXION JUCAVI
            $hostODS = 'fcods.trafficmanager.net';
            $dbNameODS = 'clientes_ods';
            $userODS = 'hmonroy';
            $passwordODS = 'Monroy2011@';
            $portODS = 3306;
            $pdoODS = new PDO("mysql:host=$hostODS;port=$portODS;dbname=$dbNameODS", $userODS, $passwordODS);

            $queryODS = "SELECT NombreFondeador as fondeador, sh_nombresucursal as sucursal, sum(monto) as monto from ( SELECT ( select fo_nombre from clientes_ods.c_fondeadores where sh_fondeador = fo_numfondeador ) as NombreFondeador, sh_credito, case when sh_saldo_capital > 900000 then 900000 when Sh_num_dias_mora > 21 then 0 else sh_saldo_capital end as monto, sh_nombresucursal FROM clientes_ods.d_saldos_hist sh LEFT JOIN clientes_ods.d_saldos s ON ( Sh_numclientesicap = s.ib_numclientesicap AND Sh_numsolicitudsicap = s.ib_numsolicitudsicap AND sh.origsistema = s.origsistema ) LEFT JOIN clientes_ods.d_ciclos_grupales cg ON ( s.ib_numclientesicap = cg.ci_numgrupo AND s.ib_numsolicitudsicap = cg.ci_numciclo AND s.origsistema = cg.origsistema AND ci_origenmigracion = 0 ) LEFT JOIN clientes_ods.c_fondeadores ON (fo_numfondeador = sh_fondeo_garantia) LEFT JOIN clientes_ods.c_sucursales ON (sh_numsucursal = su_numsucursal) LEFT JOIN clientes_ods.c_estados ON (su_estado = es_numestado) LEFT JOIN clientes_ods.c_operaciones ON (op_numoperacion = Sh_producto) WHERE sh.sh_estatus in (1, 2) and sh_fecha_historico = DATE_FORMAT( DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d' ) AND sh_fecha_desembolso <= DATE_FORMAT( DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d' ) AND Sh_fondeador IN (10) AND sh_estatus IN (1, 2, 3) GROUP by Sh_credito, Sh_numclientesicap, Sh_numsolicitudsicap ) as subquery2 group by sh_nombresucursal;";
            $statementODS = $pdoODS->prepare($queryODS);
            $statementODS->execute();
            $sucursalesjucavi = $statementODS->fetchAll(PDO::FETCH_ASSOC);

            // CONEXION MAMBU
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT fondeador, sucursal, SUM(monto_total) as monto from ( SELECT fondeador, acuerdocreditoequipo, sucursal, SUM(monto) as monto_total FROM ( SELECT fondeador , acuerdocreditoequipo , sucursal, CASE WHEN saldocapital > 900000 THEN 900000 WHEN diasatraso > 21 THEN 0 ELSE saldocapital END as monto FROM mambu_prod.soh_mambu WHERE fechacorte = ( SELECT MAX(fechadesembolso) FROM mambu_prod.soh_mambu ) AND fondeador = 'PROMECAP' ) subconsulta GROUP BY fondeador, acuerdocreditoequipo, sucursal ORDER BY acuerdocreditoequipo ) as subquery group by fondeador, sucursal;";
            $statement = $pdo->query($query);
            $sucursalesmambu = $statement->fetchAll(PDO::FETCH_ASSOC);

            $verdaderassucursales = '[{"sucursal_jucavi":"Acambaro","sucursal_real":"Acambaro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Acapulco","sucursal_real":"Acapulco Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Acapulco Centro","sucursal_real":"Acapulco Centro Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Acapulco Renacimieto","sucursal_real":"Acapulco Renacimiento Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Aguascalientes","sucursal_real":"Aguascalientes Multiproducto","estado":"Aguascalientes"},{"sucursal_jucavi":"Apatzingan","sucursal_real":"Apatzingan Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Apatzingan Madero","sucursal_real":"Apatzingan Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Apizaco","sucursal_real":"Apizaco Multiproducto","estado":"Tlaxcala"},{"sucursal_jucavi":"Atlacomulco","sucursal_real":"Atlacomulco Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Autlan","sucursal_real":"Autlan Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Boca del Rio","sucursal_real":"Boca del Rio Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Bucerias","sucursal_real":"Bucerias Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"Celaya Centro","sucursal_real":"Celaya Centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Chilapa","sucursal_real":"Chilapa Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Chilpancingo Norte","sucursal_real":"Chilpancingo Norte Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Coatzacoalcos","sucursal_real":"Coatzacoalcos Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Colima","sucursal_real":"Colima Multiproducto","estado":"Colima"},{"sucursal_jucavi":"Comitan","sucursal_real":"Comitan Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Cosamaloapan","sucursal_real":"Cosamaloapan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Culiacan","sucursal_real":"Culiacan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Dolores Hidalgo","sucursal_real":"Dolores Hidalgo Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Durango","sucursal_real":"Durango Multiproducto","estado":"Durango"},{"sucursal_jucavi":"Guadalajara Centro","sucursal_real":"Guadalajara Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Guadalupe","sucursal_real":"Guadalupe Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Guanajuato","sucursal_real":"Guanajuato Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Hermosillo","sucursal_real":"Hermosillo Multiproducto","estado":"Sonora"},{"sucursal_jucavi":"Heroica Cardenas","sucursal_real":"Heroica Cardenas Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"Huajuapan de Leon","sucursal_real":"Huajuapan de Leon Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Iguala","sucursal_real":"Iguala Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Ixtlahuaca","sucursal_real":"Ixtlahuaca Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Lagos de Moreno","sucursal_real":"Lagos de Moreno Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Lazaro Cardenas","sucursal_real":"Lazaro Cardenas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Leon Centro","sucursal_real":"Leon centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Lerdo","sucursal_real":"Lerdo Multiproducto","estado":"Durango"},{"sucursal_jucavi":"Los Reyes","sucursal_real":"Los Reyes Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Manzanillo","sucursal_real":"Manzanillo Multiproducto","estado":"Colima"},{"sucursal_jucavi":"Martinez de la Torre","sucursal_real":"Martinez de la Torre Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Matehuala","sucursal_real":"Matehuala Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Mazatlan","sucursal_real":"Mazatlan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Monterrey","sucursal_real":"Monterrey Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Monterrey Centro","sucursal_real":"Monterrey Centro Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Morelia Camelinas","sucursal_real":"Morelia Camelinas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Morelia Madero","sucursal_real":"Morelia Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Navojoa","sucursal_real":"Navojoa Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Oaxaca","sucursal_real":"Oaxaca Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Ocotlan de Morelos","sucursal_real":"Ocotlan de Morelos Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Orizaba","sucursal_real":"Orizaba Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Papantla","sucursal_real":"Papantla Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Patzcuaro","sucursal_real":"Patzcuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Puebla","sucursal_real":"Puebla Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"Queretaro","sucursal_real":"Queretaro Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"Rio Verde","sucursal_real":"Rio Verde Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Sahuayo","sucursal_real":"Sahuayo Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Salamanca","sucursal_real":"Salamanca Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Saltillo","sucursal_real":"Saltillo Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"San Andres","sucursal_real":"San Andres Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"San Juan del Rio","sucursal_real":"San Juan Del Rio Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"San Luis","sucursal_real":"San Luis Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"San Luis Centro","sucursal_real":"San Luis Centro Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"San Luis Zona Industrial","sucursal_real":"San Luis Zona Industrial Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Santa Catarina","sucursal_real":"Santa Catarina Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Tacambaro","sucursal_real":"Tacambaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Tala","sucursal_real":"Tala Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tapachula","sucursal_real":"Tapachula Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Tecpan de Galeana","sucursal_real":"Tecpan de Galeana Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Tepic","sucursal_real":"Tepic Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"Tlajomulco","sucursal_real":"Tlajomulco Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tlaxiaco","sucursal_real":"Tlaxiaco Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Tonala Centro","sucursal_real":"Tonala Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tonala Chiapas","sucursal_real":"Tonala Chiapas Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Torreon","sucursal_real":"Torreon Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"Tuxtepec","sucursal_real":"Tuxtepec Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Tuxtla","sucursal_real":"Tuxtla Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Uruapan Centro","sucursal_real":"Uruapan Centro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Veracruz","sucursal_real":"Veracruz Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Villa Flores","sucursal_real":"Villaflores Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Villa Victoria","sucursal_real":"Villa Victoria Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Villahermosa Olmeca","sucursal_real":"Villahermosa Olmeca Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"Zacapu","sucursal_real":"Zacapu Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Zamora","sucursal_real":"Zamora Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Zihuatanejo","sucursal_real":"Zihuatanejo Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Zitacuaro","sucursal_real":"Zitacuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"CD Altamirano","sucursal_real":"CD Altamirano Multiproducto","estado":"Guerrero"}]';
            $estadosmambu = '[{"sucursal_jucavi":"Acambaro","sucursal_real":"Acambaro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Acapulco","sucursal_real":"Acapulco Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Acapulco Centro","sucursal_real":"Acapulco Centro Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Acapulco Renacimieto","sucursal_real":"Acapulco Renacimiento Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Aguascalientes","sucursal_real":"Aguascalientes Multiproducto","estado":"Aguascalientes"},{"sucursal_jucavi":"Apatzingan","sucursal_real":"Apatzingan Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Apatzingan Madero","sucursal_real":"Apatzingan Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Apizaco","sucursal_real":"Apizaco Multiproducto","estado":"Tlaxcala"},{"sucursal_jucavi":"Atlacomulco","sucursal_real":"Atlacomulco Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Autlan","sucursal_real":"Autlan Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Boca del Rio","sucursal_real":"Boca del Rio Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Bucerias","sucursal_real":"Bucerias Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"Celaya Centro","sucursal_real":"Celaya Centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Chilapa","sucursal_real":"Chilapa Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Chilpancingo Norte","sucursal_real":"Chilpancingo Norte Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Coatzacoalcos","sucursal_real":"Coatzacoalcos Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Colima","sucursal_real":"Colima Multiproducto","estado":"Colima"},{"sucursal_jucavi":"Comitan","sucursal_real":"Comitan Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Cosamaloapan","sucursal_real":"Cosamaloapan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Culiacan","sucursal_real":"Culiacan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Dolores Hidalgo","sucursal_real":"Dolores Hidalgo Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Durango","sucursal_real":"Durango Multiproducto","estado":"Durango"},{"sucursal_jucavi":"Guadalajara Centro","sucursal_real":"Guadalajara Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Guadalupe","sucursal_real":"Guadalupe Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Guanajuato","sucursal_real":"Guanajuato Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Hermosillo","sucursal_real":"Hermosillo Multiproducto","estado":"Sonora"},{"sucursal_jucavi":"Heroica Cardenas","sucursal_real":"Heroica Cardenas Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"Huajuapan de Leon","sucursal_real":"Huajuapan de Leon Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Iguala","sucursal_real":"Iguala Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Ixtlahuaca","sucursal_real":"Ixtlahuaca Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Lagos de Moreno","sucursal_real":"Lagos de Moreno Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Lazaro Cardenas","sucursal_real":"Lazaro Cardenas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Leon Centro","sucursal_real":"Leon centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Lerdo","sucursal_real":"Lerdo Multiproducto","estado":"Durango"},{"sucursal_jucavi":"Los Reyes","sucursal_real":"Los Reyes Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Manzanillo","sucursal_real":"Manzanillo Multiproducto","estado":"Colima"},{"sucursal_jucavi":"Martinez de la Torre","sucursal_real":"Martinez de la Torre Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Matehuala","sucursal_real":"Matehuala Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Mazatlan","sucursal_real":"Mazatlan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Monterrey","sucursal_real":"Monterrey Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Monterrey Centro","sucursal_real":"Monterrey Centro Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Morelia Camelinas","sucursal_real":"Morelia Camelinas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Morelia Madero","sucursal_real":"Morelia Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Navojoa","sucursal_real":"Navojoa Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"Oaxaca","sucursal_real":"Oaxaca Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Ocotlan de Morelos","sucursal_real":"Ocotlan de Morelos Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Orizaba","sucursal_real":"Orizaba Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Papantla","sucursal_real":"Papantla Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Patzcuaro","sucursal_real":"Patzcuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Puebla","sucursal_real":"Puebla Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"Queretaro","sucursal_real":"Queretaro Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"Rio Verde","sucursal_real":"Rio Verde Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Sahuayo","sucursal_real":"Sahuayo Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Salamanca","sucursal_real":"Salamanca Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"Saltillo","sucursal_real":"Saltillo Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"San Andres","sucursal_real":"San Andres Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"San Juan del Rio","sucursal_real":"San Juan Del Rio Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"San Luis","sucursal_real":"San Luis Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"San Luis Centro","sucursal_real":"San Luis Centro Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"San Luis Zona Industrial","sucursal_real":"San Luis Zona Industrial Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"Santa Catarina","sucursal_real":"Santa Catarina Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"Tacambaro","sucursal_real":"Tacambaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Tala","sucursal_real":"Tala Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tapachula","sucursal_real":"Tapachula Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Tecpan de Galeana","sucursal_real":"Tecpan de Galeana Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Tepic","sucursal_real":"Tepic Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"Tlajomulco","sucursal_real":"Tlajomulco Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tlaxiaco","sucursal_real":"Tlaxiaco Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Tonala Centro","sucursal_real":"Tonala Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"Tonala Chiapas","sucursal_real":"Tonala Chiapas Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Torreon","sucursal_real":"Torreon Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"Tuxtepec","sucursal_real":"Tuxtepec Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"Tuxtla","sucursal_real":"Tuxtla Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Uruapan Centro","sucursal_real":"Uruapan Centro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Veracruz","sucursal_real":"Veracruz Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"Villa Flores","sucursal_real":"Villaflores Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"Villa Victoria","sucursal_real":"Villa Victoria Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"Villahermosa Olmeca","sucursal_real":"Villahermosa Olmeca Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"Zacapu","sucursal_real":"Zacapu Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Zamora","sucursal_real":"Zamora Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"Zihuatanejo","sucursal_real":"Zihuatanejo Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"Zitacuaro","sucursal_real":"Zitacuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Xochimilco Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Santa Cruz Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Valle de Chalco Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Chalco Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tulyehualco Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ayotla Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Lorenzo Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Pantitlan Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Milpa Alta Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"La Joya Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Iztapalapa Oriente Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Iztacalco Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ixtapaluca Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Eduardo Molina Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Chimalhuacan Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cafetales Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ajusco Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Orizaba Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Pachuca Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Papantla Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Patzcuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Pijijiapan Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Puebla Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Puerto Vallarta Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Queretaro Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Rio Verde Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Sahuayo Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Salamanca Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Saltillo Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Andres Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Cristobal Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Juan Del Rio Multiproducto","estado":"Querétaro"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Luis Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Luis Centro Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Luis Zona Industrial Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"San Martin Texmelucan Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Santa Catarina Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Santiago Ixcuintla Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Silao Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Suchiate Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tacambaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Conmigo Vales Hermosillo","estado":"Sonora"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cuernavaca Multiproducto","estado":"Morelos"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Acambaro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Acayucan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Actopan Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Aguascalientes Multiproducto","estado":"Aguascalientes"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Apatzingan Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Apatzingan Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Atlacomulco Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tala Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tapachula Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tapachula Norte Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Taxco Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tecamac Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tecamachalco Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tecoman Multiproducto","estado":"Colima"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tecpan de Galeana Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tepic Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Teziutlan Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tierra Blanca Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tlajomulco Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tlaxiaco Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Toluca Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tonala Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tonala Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tonala Chiapas Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Torreon Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tula Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tulancingo Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tuxpan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tuxtepec Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tuxtla Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Tuxtla Libramiento Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Uruapan Centro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Veracruz Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Villa Cuauhtemoc Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Villaflores Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Villahermosa Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Villahermosa Olmeca Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Xalapa Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zacapu Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zacatecas Multiproducto","estado":"Zacatecas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zamora Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zapopan Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zihuatanejo Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zinacantepec Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zitacuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Zumpango Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Acapulco Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Acapulco Centro Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Acapulco Renacimiento Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Atoyac Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Autlan Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Boca del Rio Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Bucerias Multiproducto","estado":"Nayarit"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Catemaco Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cd Hidalgo Multiproducto","estado":"Hidalgo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cd Obregon Multiproducto","estado":"Sonora"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Celaya Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Celaya Centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Chilapa Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Chilpancingo Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Chilpancingo Norte Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Coatzacoalcos Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cocula Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Colima Multiproducto","estado":"Colima"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Comitan Multiproducto","estado":"Chiapas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cordoba Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cosamaloapan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cuajimalpa Multiproducto","estado":"CDMX"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cuautla Multiproducto","estado":"Morelos"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Cuernavaca Boulevard Multiproducto","estado":"Morelos"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Culiacan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Dolores Hidalgo Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Durango Multiproducto","estado":"Durango"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Etla Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Gomez Palacio Multiproducto","estado":"Durango"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Guadalajara Oblatos Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Guadalajara Centro Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Guadalupe Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Guamuchil Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Guanajuato Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Hermosillo Multiproducto","estado":"Sonora"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Heroica Cardenas Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Huahuchinango Multiproducto","estado":"Puebla"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Huajuapan de Leon Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Iguala Multiproducto","estado":"Guerrero"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Irapuato Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ixtlahuaca Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Jojutla Multiproducto","estado":"Morelos"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Juchitan Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"La Piedad Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Lagos de Moreno Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Lazaro Cardenas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Leon centro Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Lerdo Multiproducto","estado":"Durango"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Lerma Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Loreto Multiproducto","estado":"Zacatecas"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Los Mochis Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Los Reyes Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Macuspana Multiproducto","estado":"Tabasco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Manzanillo Multiproducto","estado":"Colima"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Martinez de la Torre Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Matehuala Multiproducto","estado":"San Luis Potosi"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Mazatlan Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Miahuatlan Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Minatitlan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Monterrey Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Monterrey Centro Multiproducto","estado":"Nuevo León"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Morelia Camelinas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Morelia Centro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Morelia Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Moroleon Multiproducto","estado":"Guanajuato"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Navojoa Multiproducto","estado":"Sinaloa"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Neza Multiproducto","estado":"Estado de México"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Oaxaca Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ocotlan Multiproducto","estado":"Jalisco"},{"sucursal_jucavi":"sin_sucursal","sucursal_real":"Ocotlan de Morelos Multiproducto","estado":"Oaxaca"},{"sucursal_jucavi":"CD Altamirano","sucursal_real":"CD Altamirano Multiproducto","estado":"Guerrero"}]';

            $sucursales = json_decode($verdaderassucursales, true);

            $countjucavi = count($sucursalesjucavi);
            $countsucursales = count($sucursales);

            // Se coloca la sucursal verdadera y estado de jucavi
            for ($i = 0; $i < $countjucavi; $i++) {
                for ($j = 0; $j < $countsucursales; $j++) {
                    if ($sucursalesjucavi[$i]["sucursal"] === $sucursales[$j]["sucursal_jucavi"]) {
                        $sucursalesjucavi[$i]["sucursal"] = $sucursales[$j]["sucursal_real"];
                        $sucursalesjucavi[$i]["estado"] = $sucursales[$j]["estado"];
                        break; // Terminamos la búsqueda una vez que se encuentra una coincidencia
                    }
                }
            }

            $estadosmambuarray = json_decode($estadosmambu, true);
            // Se colola el estado a mambu
            $countmambu = count($sucursalesmambu);
            $countsucursales = count($estadosmambuarray);

            for ($i = 0; $i < $countmambu; $i++) {
                for ($j = 0; $j < $countsucursales; $j++) {
                    if ($sucursalesmambu[$i]["sucursal"] === $estadosmambuarray[$j]["sucursal_real"]) {
                        $sucursalesmambu[$i]["estado"] = $estadosmambuarray[$j]["estado"];
                        break; // Terminamos la búsqueda una vez que se encuentra una coincidencia
                    }
                }
            }

            //***************************************************TERMINA PREPARACION DE LOS JSON ***************************************** */

            //***************************************************EMPIEZA CALCULOS DE SUCURSALES ***************************************** */

            $tsucursales = '[{"sucursal":"Acambaro Multiproducto","estado":"Guanajuato"},{"sucursal":"Acapulco Centro Multiproducto","estado":"Guerrero"},{"sucursal":"Acapulco Multiproducto","estado":"Guerrero"},{"sucursal":"Acapulco Renacimiento Multiproducto","estado":"Guerrero"},{"sucursal":"Actopan Multiproducto","estado":"Hidalgo"},{"sucursal":"Aguascalientes Multiproducto","estado":"Aguascalientes"},{"sucursal":"Apatzingan Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Apatzingan Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Apizaco Multiproducto","estado":"Tlaxcala"},{"sucursal":"Atlacomulco Multiproducto","estado":"Estado de México"},{"sucursal":"Autlan Multiproducto","estado":"Jalisco"},{"sucursal":"Ayotla Multiproducto","estado":"Estado de México"},{"sucursal":"Boca del Rio Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Bucerias Multiproducto","estado":"Nayarit"},{"sucursal":"CD Altamirano Multiproducto","estado":"Guerrero"},{"sucursal":"Celaya Centro Multiproducto","estado":"Guanajuato"},{"sucursal":"Chalco Multiproducto","estado":"Estado de México"},{"sucursal":"Chilapa Multiproducto","estado":"Guerrero"},{"sucursal":"Chilpancingo Norte Multiproducto","estado":"Guerrero"},{"sucursal":"Chimalhuacan Multiproducto","estado":"Estado de México"},{"sucursal":"Coatzacoalcos Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Colima Multiproducto","estado":"Colima"},{"sucursal":"Comitan Multiproducto","estado":"Chiapas"},{"sucursal":"Cosamaloapan Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Cuautla Multiproducto","estado":"Morelos"},{"sucursal":"Cuernavaca Multiproducto","estado":"Morelos"},{"sucursal":"Culiacan Multiproducto","estado":"Sinaloa"},{"sucursal":"Dolores Hidalgo Multiproducto","estado":"Guanajuato"},{"sucursal":"Durango Multiproducto","estado":"Durango"},{"sucursal":"Eduardo Molina Multiproducto","estado":"CDMX"},{"sucursal":"Guadalajara Centro Multiproducto","estado":"Jalisco"},{"sucursal":"Guadalupe Multiproducto","estado":"Nuevo León"},{"sucursal":"Guanajuato Multiproducto","estado":"Guanajuato"},{"sucursal":"Hermosillo Multiproducto","estado":"Sonora"},{"sucursal":"Heroica Cardenas Multiproducto","estado":"Tabasco"},{"sucursal":"Huajuapan de Leon Multiproducto","estado":"Oaxaca"},{"sucursal":"Iguala Multiproducto","estado":"Guerrero"},{"sucursal":"Ixtlahuaca Multiproducto","estado":"Estado de México"},{"sucursal":"Iztacalco Multiproducto","estado":"CDMX"},{"sucursal":"Iztapalapa Oriente Multiproducto","estado":"CDMX"},{"sucursal":"Jojutla Multiproducto","estado":"Morelos"},{"sucursal":"La Joya Multiproducto","estado":"CDMX"},{"sucursal":"Lagos de Moreno Multiproducto","estado":"Jalisco"},{"sucursal":"Lazaro Cardenas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Leon centro Multiproducto","estado":"Guanajuato"},{"sucursal":"Lerdo Multiproducto","estado":"Durango"},{"sucursal":"Lerma Multiproducto","estado":"Estado de México"},{"sucursal":"Los Reyes Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Manzanillo Multiproducto","estado":"Colima"},{"sucursal":"Martinez de la Torre Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Matehuala Multiproducto","estado":"San Luis Potosi"},{"sucursal":"Mazatlan Multiproducto","estado":"Sinaloa"},{"sucursal":"Monterrey Centro Multiproducto","estado":"Nuevo León"},{"sucursal":"Monterrey Multiproducto","estado":"Nuevo León"},{"sucursal":"Morelia Camelinas Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Morelia Madero Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Navojoa Multiproducto","estado":"Sinaloa"},{"sucursal":"Neza Multiproducto","estado":"Estado de México"},{"sucursal":"Oaxaca Multiproducto","estado":"Oaxaca"},{"sucursal":"Ocotlan de Morelos Multiproducto","estado":"Oaxaca"},{"sucursal":"Orizaba Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Pachuca Multiproducto","estado":"Hidalgo"},{"sucursal":"Pantitlan Multiproducto","estado":"CDMX"},{"sucursal":"Papantla Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Patzcuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Puebla Multiproducto","estado":"Puebla"},{"sucursal":"Queretaro Multiproducto","estado":"Querétaro"},{"sucursal":"Rio Verde Multiproducto","estado":"San Luis Potosi"},{"sucursal":"Sahuayo Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Salamanca Multiproducto","estado":"Guanajuato"},{"sucursal":"Saltillo Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal":"San Andres Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"San Juan Del Rio Multiproducto","estado":"Querétaro"},{"sucursal":"San Luis Centro Multiproducto","estado":"San Luis Potosi"},{"sucursal":"San Luis Multiproducto","estado":"San Luis Potosi"},{"sucursal":"San Luis Zona Industrial Multiproducto","estado":"San Luis Potosi"},{"sucursal":"Santa Catarina Multiproducto","estado":"Nuevo León"},{"sucursal":"Santa Cruz Multiproducto","estado":"CDMX"},{"sucursal":"Tacambaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Tala Multiproducto","estado":"Jalisco"},{"sucursal":"Tapachula Multiproducto","estado":"Chiapas"},{"sucursal":"Tecamac Multiproducto","estado":"Hidalgo"},{"sucursal":"Tecpan de Galeana Multiproducto","estado":"Guerrero"},{"sucursal":"Tepic Multiproducto","estado":"Nayarit"},{"sucursal":"Tlajomulco Multiproducto","estado":"Jalisco"},{"sucursal":"Tlaxiaco Multiproducto","estado":"Oaxaca"},{"sucursal":"Toluca Multiproducto","estado":"Estado de México"},{"sucursal":"Tonala Centro Multiproducto","estado":"Jalisco"},{"sucursal":"Tonala Chiapas Multiproducto","estado":"Chiapas"},{"sucursal":"Torreon Multiproducto","estado":"Coahuila de Zaragoza"},{"sucursal":"Tula Multiproducto","estado":"Hidalgo"},{"sucursal":"Tulyehualco Multiproducto","estado":"CDMX"},{"sucursal":"Tuxtepec Multiproducto","estado":"Oaxaca"},{"sucursal":"Tuxtla Multiproducto","estado":"Chiapas"},{"sucursal":"Uruapan Centro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Veracruz Multiproducto","estado":"Veracruz de Ignacio de la Llave"},{"sucursal":"Villa Victoria Multiproducto","estado":"Estado de México"},{"sucursal":"Villaflores Multiproducto","estado":"Chiapas"},{"sucursal":"Villahermosa Olmeca Multiproducto","estado":"Tabasco"},{"sucursal":"Xochimilco Multiproducto","estado":"CDMX"},{"sucursal":"Zacapu Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Zamora Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Zihuatanejo Multiproducto","estado":"Guerrero"},{"sucursal":"Zitacuaro Multiproducto","estado":"Michoacán de Ocampo"},{"sucursal":"Zumpango Multiproducto","estado":"Estado de México"}]';
            $sucursales = json_decode($tsucursales, true);

            for ($i = 0; $i < count($sucursales); $i++) {

                $sucursal = $sucursales[$i]; // Usamos una referencia para poder modificar el elemento actual

                // Buscar la sucursal en $tjucavi
                $tjucaviMatch = null;
                foreach ($sucursalesjucavi as $item) {
                    if ($item['sucursal'] === $sucursal['sucursal']) {
                        $tjucaviMatch = $item;
                        break; // Salir del bucle una vez que se encuentre una coincidencia
                    }
                }

                // Buscar la sucursal en $tmpmambu
                $tmpmambuMatch = null;
                foreach ($sucursalesmambu as $item) {
                    if ($item['sucursal'] === $sucursal['sucursal']) {
                        $tmpmambuMatch = $item;
                        break; // Salir del bucle una vez que se encuentre una coincidencia
                    }
                }

                // Calcular el saldo capital si se encuentran coincidencias
                $saldoCapital = 0;
                if (!is_null($tmpmambuMatch)) {
                    $saldoCapital += floatval($tmpmambuMatch['monto']);
                }
                if (!is_null($tjucaviMatch)) {
                    $saldoCapital += floatval($tjucaviMatch['monto']);
                }

                // Añadir el campo saldo_capital
                $sucursales[$i]['saldo_capital'] = number_format($saldoCapital, 2); // Formatear el número si es necesario
            }

            //***************************************************EMPIEZA CALCULOS DE SUCURSALES ***************************************** */

            //***************************************************EMPIEZA CALCULOS DE PORCENTAJES ***************************************** */

            $suma_total = 0.0; // Inicializa la suma total

            foreach ($sucursales as $item) {
                $saldo_capital = floatval(str_replace(',', '', $item['saldo_capital']));
                $suma_total += $saldo_capital;
            }

            $saldomaximo = $suma_total * 0.05;

            // Calcula el porcentaje proporcional para cada elemento y agrega el campo "porcentaje"
            for ($i = 0; $i < count($sucursales); $i++) {
                $saldo_capital = $sucursales[$i]['saldo_capital'];
                $saldocasteado = floatval(str_replace(',', '', $saldo_capital));

                if ($saldocasteado > $saldomaximo) {
                    $sucursales[$i]['saldo_capital'] = number_format($saldomaximo, 2);
                    $porcentaje = number_format(5.00, 2);
                    $sucursales[$i]["porcentaje"] = number_format($porcentaje, 2);
                } else {
                    $porcentaje = ($saldocasteado / $suma_total) * 100;
                    $sucursales[$i]["porcentaje"] = number_format($porcentaje, 2);
                }

            }

            //***************************************************TERMINA CALCULOS DE PORCENTAJES ***************************************** */

            //***************************************************TERMINA CALCULOS POR ESTADOS ***************************************** */
            $saldos_por_estado = array();

            // Recorre el arreglo de sucursales
            foreach ($sucursales as $item) {
                $estado = $item['estado'];
                $saldo_capital = floatval(str_replace(',', '', $item['saldo_capital'])); // Convierte el saldo a flotante

                // Si el estado ya existe en el arreglo $saldos_por_estado, suma el saldo al valor existente
                if (isset($saldos_por_estado[$estado])) {
                    $saldos_por_estado[$estado] += $saldo_capital;
                } else {
                    // Si el estado no existe, crea una nueva entrada con el saldo actual
                    $saldos_por_estado[$estado] = $saldo_capital;
                }
            }

            $suma_total_estados = array_sum($saldos_por_estado);

            $arreglo_estados_porcentajes = array();

            foreach ($saldos_por_estado as $estado => $valor) {
                $porcentaje = ($valor / $suma_total_estados) * 100;
                $tmparray = [
                    "estado" => $estado,
                    "saldo" => $valor,
                    "porcentaje" => $porcentaje,
                ];
                array_push($arreglo_estados_porcentajes, $tmparray);
            }

            for ($i = 0; $i < count($arreglo_estados_porcentajes); $i++) {
                switch ($arreglo_estados_porcentajes[$i]["estado"]) {
                    case 1:
                        echo "CDMX";
                        if ($value["porcentaje"] > 30.00) {
                            $arreglo_estados_porcentajes[$i]["saldo"] = $suma_total_estados * 0.3;
                            $arreglo_estados_porcentajes[$i]["porcentaje"] = 30.0;
                        }
                        break;
                    case 2:
                        echo "Estado de México";
                        if ($value["porcentaje"] > 30.00) {
                            $arreglo_estados_porcentajes[$i]["saldo"] = $suma_total_estados * 0.3;
                            $arreglo_estados_porcentajes[$i]["porcentaje"] = 20.0;
                        }
                        break;
                    case 3:
                        echo "Michoacán de Ocampo";
                        if ($value["porcentaje"] > 20.00) {
                            $arreglo_estados_porcentajes[$i]["saldo"] = $suma_total_estados * 0.2;
                            $arreglo_estados_porcentajes[$i]["porcentaje"] = 20.0;
                        }
                        break;
                    default:

                }

            }

            //***************************************************TERMINA CALCULOS POR ESTADOS ***************************************** */

            $aforo_calculado = 0.0;

            foreach ($arreglo_estados_porcentajes as $key => $value) {
                $aforo_calculado += $value["saldo"];
            }

            // VALOR DE AFORO CALCULADO $aforo_calculado;

            //  SIN REGLAS************************************************************************************************

            //VALORES JUCAVI
            $montoPromecapJucavi = 0;
            $montoPromecapMambu = 0;
            $query = "SELECT NombreFondeador as nombrefondeador, COUNT(*) as cantidadregistros, SUM( Sh_monto_seguro + sh_saldo_capital ) as monto from ( SELECT ( select fo_nombre from clientes_ods.c_fondeadores where sh_fondeador = fo_numfondeador ) as NombreFondeador, Sh_monto_seguro, sh_saldo_capital FROM clientes_ods.d_saldos_hist sh LEFT JOIN clientes_ods.d_saldos s ON ( Sh_numclientesicap = s.ib_numclientesicap AND Sh_numsolicitudsicap = s.ib_numsolicitudsicap AND sh.origsistema = s.origsistema ) LEFT JOIN clientes_ods.d_ciclos_grupales cg ON ( s.ib_numclientesicap = cg.ci_numgrupo AND s.ib_numsolicitudsicap = cg.ci_numciclo AND s.origsistema = cg.origsistema AND ci_origenmigracion = 0 ) LEFT JOIN clientes_ods.c_fondeadores ON ( fo_numfondeador = sh_fondeo_garantia ) LEFT JOIN clientes_ods.c_sucursales ON (sh_numsucursal = su_numsucursal) LEFT JOIN clientes_ods.c_estados ON (su_estado = es_numestado) LEFT JOIN clientes_ods.c_operaciones ON (op_numoperacion = Sh_producto) WHERE sh_fecha_historico = DATE_FORMAT( DATE_SUB( CURDATE(), INTERVAL 1 DAY ), '%Y-%m-%d' ) AND sh_fecha_desembolso <= DATE_FORMAT( DATE_SUB( CURDATE(), INTERVAL 1 DAY ), '%Y-%m-%d' ) AND Sh_fondeador IN (1, 10, 16, 17) AND sh_estatus IN (1, 2, 3, 4, 5, 6) GROUP BY Sh_credito, Sh_numclientesicap, Sh_numsolicitudsicap ) as subquery group by NombreFondeador";
            $statementODS = $pdoODS->prepare($query);
            $statementODS->execute();
            $results = $statementODS->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $elemento) {
                if ($elemento["nombrefondeador"] === "Promecap") {
                    $montoPromecapJucavi = $elemento["monto"];
                    break; // Terminar la búsqueda una vez que se encuentra una coincidencia
                }
            }
            // VALORES MAMBU

            $formattedFechaMov = date("Y-m-d", strtotime($fechaActual . "-1 day"));
            $query = "SELECT fondeador as nombrefondeador, COUNT(*) as cantidadregistros, SUM(saldocapital) as monto from ( SELECT fondeador, sucursal as Sucursal, SUM(diasatraso) as dias_moa, SUM(saldocapital) as saldocapital FROM ( SELECT fondeador, acuerdocreditoequipo, equipo, sucursal, diasatraso, capitalotorgado, fechadesembolso, fechavencimiento, cuotastranscurridas, saldocapital, saldointeres, CASE WHEN saldocapital > 900000 THEN 900000 WHEN diasatraso > 21 THEN 0 ELSE saldocapital END as monto FROM mambu_prod.soh_mambu WHERE fechacorte = ( SELECT MAX(fechadesembolso) FROM mambu_prod.soh_mambu ) ) subconsulta GROUP by fondeador, acuerdocreditoequipo, equipo, sucursal ORDER BY acuerdocreditoequipo ) as squery GROUP BY fondeador;";

            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $montoPromecapMambu = 0;
            foreach ($result as $elemento) {
                if ($elemento["nombrefondeador"] === "PROMECAP") {
                    $montoPromecapMambu = $elemento["monto"];
                    break; // Terminar la búsqueda una vez que se encuentra una coincidencia
                }
            }

            $saldo_buscado = $aforo_calculado - (floatval($montoPromecapJucavi) + floatval($montoPromecapMambu));

            $verdaderassucursales = '[{"sucursal_jucavi":"Acambaro","sucursal_real":"Acambaro Multiproducto"},{"sucursal_jucavi":"Acapulco","sucursal_real":"Acapulco Multiproducto"},{"sucursal_jucavi":"Acapulco Centro","sucursal_real":"Acapulco Centro Multiproducto"},{"sucursal_jucavi":"Acapulco Renacimieto","sucursal_real":"Acapulco Renacimiento Multiproducto"},{"sucursal_jucavi":"Aguascalientes","sucursal_real":"Aguascalientes Multiproducto"},{"sucursal_jucavi":"Apatzingan","sucursal_real":"Apatzingan Multiproducto"},{"sucursal_jucavi":"Apatzingan Madero","sucursal_real":"Apatzingan Madero Multiproducto"},{"sucursal_jucavi":"Apizaco","sucursal_real":"Apizaco Multiproducto"},{"sucursal_jucavi":"Atlacomulco","sucursal_real":"Atlacomulco Multiproducto"},{"sucursal_jucavi":"Autlan","sucursal_real":"Autlan Multiproducto"},{"sucursal_jucavi":"Boca del Rio","sucursal_real":"Boca del Rio Multiproducto"},{"sucursal_jucavi":"Bucerias","sucursal_real":"Bucerias Multiproducto"},{"sucursal_jucavi":"Celaya Centro","sucursal_real":"Celaya Centro Multiproducto"},{"sucursal_jucavi":"Chilapa","sucursal_real":"Chilapa Multiproducto"},{"sucursal_jucavi":"Chilpancingo Norte","sucursal_real":"Chilpancingo Norte Multiproducto"},{"sucursal_jucavi":"Coatzacoalcos","sucursal_real":"Coatzacoalcos Multiproducto"},{"sucursal_jucavi":"Colima","sucursal_real":"Colima Multiproducto"},{"sucursal_jucavi":"Comitan","sucursal_real":"Comitan Multiproducto"},{"sucursal_jucavi":"Cosamaloapan","sucursal_real":"Cosamaloapan Multiproducto"},{"sucursal_jucavi":"Culiacan","sucursal_real":"Culiacan Multiproducto"},{"sucursal_jucavi":"Dolores Hidalgo","sucursal_real":"Dolores Hidalgo Multiproducto"},{"sucursal_jucavi":"Durango","sucursal_real":"Durango Multiproducto"},{"sucursal_jucavi":"Guadalajara Centro","sucursal_real":"Guadalajara Centro Multiproducto"},{"sucursal_jucavi":"Guadalupe","sucursal_real":"Guadalupe Multiproducto"},{"sucursal_jucavi":"Guanajuato","sucursal_real":"Guanajuato Multiproducto"},{"sucursal_jucavi":"Hermosillo","sucursal_real":"Hermosillo Multiproducto"},{"sucursal_jucavi":"Heroica Cardenas","sucursal_real":"Heroica Cardenas Multiproducto"},{"sucursal_jucavi":"Huajuapan de Leon","sucursal_real":"Huajuapan de Leon Multiproducto"},{"sucursal_jucavi":"Iguala","sucursal_real":"Iguala Multiproducto"},{"sucursal_jucavi":"Ixtlahuaca","sucursal_real":"Ixtlahuaca Multiproducto"},{"sucursal_jucavi":"Lagos de Moreno","sucursal_real":"Lagos de Moreno Multiproducto"},{"sucursal_jucavi":"Lazaro Cardenas","sucursal_real":"Lazaro Cardenas Multiproducto"},{"sucursal_jucavi":"Leon Centro","sucursal_real":"Leon centro Multiproducto"},{"sucursal_jucavi":"Lerdo","sucursal_real":"Lerdo Multiproducto"},{"sucursal_jucavi":"Los Reyes","sucursal_real":"Los Reyes Multiproducto"},{"sucursal_jucavi":"Manzanillo","sucursal_real":"Manzanillo Multiproducto"},{"sucursal_jucavi":"Martinez de la Torre","sucursal_real":"Martinez de la Torre Multiproducto"},{"sucursal_jucavi":"Matehuala","sucursal_real":"Matehuala Multiproducto"},{"sucursal_jucavi":"Mazatlan","sucursal_real":"Mazatlan Multiproducto"},{"sucursal_jucavi":"Monterrey","sucursal_real":"Monterrey Multiproducto"},{"sucursal_jucavi":"Monterrey Centro","sucursal_real":"Monterrey Centro Multiproducto"},{"sucursal_jucavi":"Morelia Camelinas","sucursal_real":"Morelia Camelinas Multiproducto"},{"sucursal_jucavi":"Morelia Madero","sucursal_real":"Morelia Madero Multiproducto"},{"sucursal_jucavi":"Navojoa","sucursal_real":"Navojoa Multiproducto"},{"sucursal_jucavi":"Oaxaca","sucursal_real":"Oaxaca Multiproducto"},{"sucursal_jucavi":"Ocotlan de Morelos","sucursal_real":"Ocotlan de Morelos Multiproducto"},{"sucursal_jucavi":"Orizaba","sucursal_real":"Orizaba Multiproducto"},{"sucursal_jucavi":"Papantla","sucursal_real":"Papantla Multiproducto"},{"sucursal_jucavi":"Patzcuaro","sucursal_real":"Patzcuaro Multiproducto"},{"sucursal_jucavi":"Puebla","sucursal_real":"Puebla Multiproducto"},{"sucursal_jucavi":"Queretaro","sucursal_real":"Queretaro Multiproducto"},{"sucursal_jucavi":"Rio Verde","sucursal_real":"Rio Verde Multiproducto"},{"sucursal_jucavi":"Sahuayo","sucursal_real":"Sahuayo Multiproducto"},{"sucursal_jucavi":"Salamanca","sucursal_real":"Salamanca Multiproducto"},{"sucursal_jucavi":"Saltillo","sucursal_real":"Saltillo Multiproducto"},{"sucursal_jucavi":"San Andres","sucursal_real":"San Andres Multiproducto"},{"sucursal_jucavi":"San Juan del Rio","sucursal_real":"San Juan Del Rio Multiproducto"},{"sucursal_jucavi":"San Luis","sucursal_real":"San Luis Multiproducto"},{"sucursal_jucavi":"San Luis Centro","sucursal_real":"San Luis Centro Multiproducto"},{"sucursal_jucavi":"San Luis Zona Industrial","sucursal_real":"San Luis Zona Industrial Multiproducto"},{"sucursal_jucavi":"Santa Catarina","sucursal_real":"Santa Catarina Multiproducto"},{"sucursal_jucavi":"Tacambaro","sucursal_real":"Tacambaro Multiproducto"},{"sucursal_jucavi":"Tala","sucursal_real":"Tala Multiproducto"},{"sucursal_jucavi":"Tapachula","sucursal_real":"Tapachula Multiproducto"},{"sucursal_jucavi":"Tecpan de Galeana","sucursal_real":"Tecpan de Galeana Multiproducto"},{"sucursal_jucavi":"Tepic","sucursal_real":"Tepic Multiproducto"},{"sucursal_jucavi":"Tlajomulco","sucursal_real":"Tlajomulco Multiproducto"},{"sucursal_jucavi":"Tlaxiaco","sucursal_real":"Tlaxiaco Multiproducto"},{"sucursal_jucavi":"Tonala Centro","sucursal_real":"Tonala Centro Multiproducto"},{"sucursal_jucavi":"Tonala Chiapas","sucursal_real":"Tonala Chiapas Multiproducto"},{"sucursal_jucavi":"Torreon","sucursal_real":"Torreon Multiproducto"},{"sucursal_jucavi":"Tuxtepec","sucursal_real":"Tuxtepec Multiproducto"},{"sucursal_jucavi":"Tuxtla","sucursal_real":"Tuxtla Multiproducto"},{"sucursal_jucavi":"Uruapan Centro","sucursal_real":"Uruapan Centro Multiproducto"},{"sucursal_jucavi":"Veracruz","sucursal_real":"Veracruz Multiproducto"},{"sucursal_jucavi":"Villa Flores","sucursal_real":"Villaflores Multiproducto"},{"sucursal_jucavi":"Villa Victoria","sucursal_real":"Villa Victoria Multiproducto"},{"sucursal_jucavi":"Villahermosa Olmeca","sucursal_real":"Villahermosa Olmeca Multiproducto"},{"sucursal_jucavi":"Zacapu","sucursal_real":"Zacapu Multiproducto"},{"sucursal_jucavi":"Zamora","sucursal_real":"Zamora Multiproducto"},{"sucursal_jucavi":"Zihuatanejo","sucursal_real":"Zihuatanejo Multiproducto"},{"sucursal_jucavi":"Zitacuaro","sucursal_real":"Zitacuaro Multiproducto"}]';
            $truesucursales = json_decode($verdaderassucursales, true);

            // Reporte previo
            /*Fechas*/
            $fechaActual = $this->fechaActual;
            $fechaMenosUnDia = $this->fechaMenosUnDia;
            $lista = [];

            // SE CONSULTA A JUCAVI
            $lista = $this->GetLitaAltaPromecapJ($fechaMenosUnDia); //
            $listaCount = count($lista);
            $truesucursalesCount = count($truesucursales);

            for ($i = 0; $i < $listaCount; $i++) {
                for ($j = 0; $j < $truesucursalesCount; $j++) {
                    if ($lista[$i]["Sucursal"] === $truesucursales[$j]["sucursal_jucavi"]) {
                        $lista[$i]["Sucursal"] = $truesucursales[$j]["sucursal_real"];
                        break; // Terminamos la búsqueda una vez que se encuentra una coincidencia
                    }
                }
            }

            // ORDENAR DE MAYOR A MENOR POR MONTO

            // Define la función de comparación personalizada dentro de usort
            usort($lista, function ($a, $b) {
                $montoA = floatval($a["Monto"]);
                $montoB = floatval($b["Monto"]);

                if ($montoA == $montoB) {
                    return 0;
                }

                return ($montoA > $montoB) ? -1 : 1;
            });

            // Convierte el lista ordenado de nuevo a JSON
            $listaOrdenada = json_encode($lista, JSON_PRETTY_PRINT);

            $listaOrdenadaJucavi = json_decode($listaOrdenada, true);

            $preetiquetado = array();
            $monto_meta = 0;
            foreach ($sucursales as $sucursal) {
                foreach ($listaOrdenadaJucavi as $creditoCandidato) {
                    $percent = floatval($sucursal["porcentaje"]);
                    ///
                    if ($percent <= 5.0 && $creditoCandidato["Sucursal"] == $sucursal["sucursal"] && $monto_meta <= $saldo_buscado) {
                        $nuevosaldo = 0;
                        $nuevosaldo = floatval($sucursal["saldo_capital"]) + floatval($creditoCandidato["SaldoInsoluto"]);
                        $sucursal["saldo_capital"] = number_format($nuevosaldo);
                        $s = floatval(str_replace(',', '', $sucursal["saldo_capital"]));
                        $nuevo_porcentaje = ($s * 100) / $suma_total;
                        $sucursal["porcentaje"] = number_format($nuevo_porcentaje, 2);
                        $monto_meta += floatval($creditoCandidato["SaldoInsoluto"]);
                        array_push($preetiquetado, $creditoCandidato);
                    }
                }

            }
            $tmpmambu = [];
            $preetiquetado2 = [];
            if ($monto_meta <= $saldo_buscado) {
                // SE COMPLETA CON MAMBU
                $tmpmambu = $this->GetLitaAltaPromecapM();
                foreach ($sucursales as $sucursal) {
                    foreach ($tmpmambu as $creditoCandidato) {
                        $percent = floatval($sucursal["porcentaje"]);
                        if ($percent <= 5.0 && $creditoCandidato["Sucursal"] == $sucursal["sucursal"] && $monto_meta <= $saldo_buscado) {
                            $nuevosaldo = 0;
                            $nuevosaldo = floatval($sucursal["saldo_capital"]) + floatval($creditoCandidato["Monto"]);

                            $sucursal["saldo_capital"] = number_format($nuevosaldo);
                            $s = floatval(str_replace(',', '', $sucursal["saldo_capital"]));
                            $nuevo_porcentaje = ($s * 100) / $suma_total;
                            $sucursal["porcentaje"] = number_format($nuevo_porcentaje, 2);
                            $monto_meta += $nuevosaldo;
                            array_push($preetiquetado, $creditoCandidato);
                        }
                    }

                }

            }
            //return $preetiquetado;
            // return $listaOrdenadaJucavi;
            return ([
                "Sucursal:" => $sucursales,
                "CreditosCandidatos" => $listaOrdenadaJucavi,
                "Sucursales_equivalentes" => $truesucursales,
                "preetiquetado:" => $preetiquetado,
                "Montometa" => $monto_meta,
                "MontoBuscado" => $saldo_buscado,
                "AFORO_CALCULADO" => $aforo_calculado,
                "AforoFaltanteJucavi" => $results,
                "AforoFaltanteMambu" => $result,
                "montoPromecapJucavi" => $montoPromecapJucavi,
                "montoPromecapMambu" => $montoPromecapMambu,
                "ListaCandidatosMambu" => $tmpmambu,
                "jucavi" => $preetiquetado, "mambu" => $preetiquetado2,
            ]);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage()], 401);
        }
    }

    public function GetLitaAltaPromecapJ($fechaMenosUnDia)
    {

        // Conexion a ODS
        $host = 'fcods.trafficmanager.net';
        $dbName = 'clientes_ods';
        $user = 'hmonroy';
        $password = 'Monroy2011@';
        $port = 3306;

        try {
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            // Preparar la consulta SQL
            ini_set('max_execution_time', 300);
            $query = "call clientes_ods.SP_ListaPrevioPromecapNuevo('" . $fechaMenosUnDia . "');";
            $statement = $pdo->prepare($query);

            // Ejecutar la consulta
            $statement->execute();

            // Obtener los resultados
            $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $posts;

            foreach ($posts as $post) {
                $nestedData["NoCredito"] = $post["NoCredito"];
                $nestedData["NoGrupo"] = $post["NoGrupo"];
                $nestedData["NoCiclo"] = $post["NoCiclo"];
                $nestedData["Monto"] = $post["Monto"];
                $nestedData["SaldoInsoluto"] = $post["SaldoInsoluto"];
                $nestedData["DiasMora"] = $post["DiasMora"];
                $nestedData["NoPlazos"] = $post["NoPlazos"];
                $nestedData["IVA"] = $post["IVA"];
                $nestedData["Estado"] = $post["Estado"];
                $nestedData["NoIntegrantes"] = $post["NoIntegrantes"];
                $nestedData["AddIntegrantes"] = $post["AddIntegrantes"];
                $dataList = $nestedData;
            }

            return $dataList;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error: " . $e->getMessage();
        }

    }
    public function GetLitaAltaPromecapM()
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapM/PreetiquetadoPromecapMambuLista',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Acept: Accept: application/vnd.mambu.v2+json',
                    'Authorization: Basic Y29uRm9uZGVhZG9yZXM6TjYjS3V0SEkhcA==',
                    'Cookie: ARRAffinity=5ac5a5b6b0474f4a176adea3605fa714567efe822ec3033deff0c52abd6389e8; ARRAffinitySameSite=5ac5a5b6b0474f4a176adea3605fa714567efe822ec3033deff0c52abd6389e8',
                ),
            ));

            $response = curl_exec($curl);
            $data = json_decode($response, true);
            curl_close($curl);
            $listamambu = json_decode($data[0], true);
            $returnmambu = array();
            foreach ($listamambu as $item) {
                $nedestItem["NoCredito"] = $item["stridlc"];
                $nedestItem["NoGrupo"] = "-";
                $nedestItem["NoCiclo"] = $item["ciclogrupo"];
                $nedestItem["Monto"] = $item["monto"];
                $nedestItem["SaldoInsoluto"] = "-";
                $nedestItem["DiasMora"] = "-";
                $nedestItem["NoPlazos"] = "-";
                $nedestItem["IVA"] = "-";
                $nedestItem["Estado"] = "-";
                $nedestItem["Sucursal"] = $item["sucursal"];
                $nedestItem["NoIntegrantes"] = "-";
                $nedestItem["AddIntegrante"] = "-";
                $returnmambu[] = $nedestItem;
            }

            return $returnmambu;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error: " . $e->getMessage();
            return $e->getMessage();
        }

    }
    public function bajapromecapjucavi(Request $request)
    {
        $curl = curl_init();
        try {

            // Conexion a ODS
            $host = 'fcods.trafficmanager.net';
            $dbName = 'clientes_ods';
            $user = 'hmonroy';
            $password = 'Monroy2011@';
            $port = 3306;

            $lstcreditos = $request->jucavi;
            /*Fechas*/
            $fechaActual = $this->fechaActual;
            $fechaMenosUnDia = $this->fechaMenosUnDia;

            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            // Preparar la consulta SQL
            ini_set('max_execution_time', 300);

            $queryValidaDia = "SELECT pa_valor FROM clientes_ods.c_parametros WHERE pa_cve_param = 'FECHA_CIERRE';";
            $statementValidaDia = $pdo->query($queryValidaDia);
            $resultValidaDia = $statementValidaDia->fetchAll(PDO::FETCH_ASSOC);

            if ($resultValidaDia[0]["pa_valor"] == "") {
                return response()->json(['error' => "Hoy no es un día para realizar la baja."], 400);
            } else {
                $strFechaCierre = date("Y-m-d", strtotime("-1 day", strtotime($resultValidaDia[0]["pa_valor"])));

                $strValidaCierre = $this->validaBaja($strFechaCierre, 1, 10);

                if ($strValidaCierre[0]["strResult"] == "Continua.") {

                    $sqlStatementJucaviPromecap = "use cartera_ods; INSERT INTO d_etiquetado_previopromecap_baja (ep_num_credito, ep_fecha_etiquetado,ep_fechamov) VALUES  \n";

                    foreach ($lstcreditos as $id) {
                        $sqlStatementJucaviPromecap .= '("' . $id . '", "' . $fechaMenosUnDia . '", "' . $fechaActual . '"),';
                    }

                    $sqlStatementJucaviPromecap = rtrim($sqlStatementJucaviPromecap, ',');
                    $sqlStatementJucaviPromecap .= ';';

                    $statement = $pdo->query($sqlStatementJucaviPromecap);
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://fcetiquetado.azurewebsites.net/ProcesoBursa/api/EtiquetadoPromecapJ/BajaPromecapJV/69',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'Cookie: ARRAffinity=f338cc84dcd26ef0541e10991beb3f601c2d1a0e9ced27dcfbc2140d4a6a8e25',
                        ),
                    ));

                    $response = curl_exec($curl);

                    return response()->json(['success' => "Baja realizada correctamente correctamente"], 200);

                } else {
                    return response()->json(['error' => 'El archivo con los créditos autorizados por ACFIN ya fueron enviados y etiquetados.'], 400);
                }
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }

    }
    public function getListaAltaBlaoJ($fecha)
    {

        // Conexion a ODS
        $host = 'fcods.trafficmanager.net';
        $dbName = 'clientes_ods';
        $user = 'hmonroy';
        $password = 'Monroy2011@';
        $port = 3306;

        try {
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            // Preparar la consulta SQL
            $query = "call clientes_ods.SP_ListaPrevioBlao('" . $fecha . "');";
            $statement = $pdo->prepare($query);

            // Ejecutar la consulta
            $statement->execute();

            // Obtener los resultados
            $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as $post) {
                $nestedData["NoCredito"] = $post["NoCredito"];
                $nestedData["NoGrupo"] = $post["NoGrupo"];
                $nestedData["NoCiclo"] = $post["NoCiclo"];
                $nestedData["Monto"] = $post["Monto"];
                $nestedData["SaldoInsoluto"] = $post["SaldoInsoluto"];
                $nestedData["DiasMora"] = $post["DiasMora"];
                $nestedData["NoPlazos"] = $post["NoPlazos"];
                $nestedData["IVA"] = $post["IVA"];
                $nestedData["Estado"] = $post["Estado"];
                $nestedData["NoIntegrantes"] = $post["NoIntegrantes"];
                $nestedData["AddIntegrantes"] = $post["AddIntegrantes"];
                $dataList = $nestedData;
            }

            return $dataList;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error: " . $e->getMessage();
        }

    }
    public function verificaEtiquetado($fecha, $fondeador)
    {

        // Conexion a ODS
        $host = 'fcods.trafficmanager.net';
        $dbName = 'clientes_ods';
        $user = 'hmonroy';
        $password = 'Monroy2011@';
        $port = 3306;

        try {
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            // Preparar la consulta SQL
            $query = "call clientes_ods.SP_ValidaEtiquetado('" . $fecha . "',.$fondeador.);";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error: " . $e->getMessage();
        }

    }
    public function validaBaja($fecha, $intFondeador, $intfondeadoranterior)
    {
        // Conexion a ODS
        $host = 'fcods.trafficmanager.net';
        $dbName = 'clientes_ods';
        $user = 'hmonroy';
        $password = 'Monroy2011@';
        $port = 3306;

        try {
            // Conexión a la base de datos
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $password);

            // Preparar la consulta SQL
            $query = "call clientes_ods.SP_ValidaBaja('" . $fecha . "'," . $intFondeador . "," . $intfondeadoranterior . ");";

            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error: " . $e->getMessage();
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

    public function pruebaetiquetadoblaomambu()
    {

        $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
        $port = '5439';
        $database = 'mambu_prod';
        $user = 'marcadodev';
        $password = 'marcadoDev00';

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $user, $password);
        // Configurar opciones adicionales si es necesario
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo 'Conexión exitosa a PostgreSQL';


        $query = "DROP TABLE IF EXISTS conteopagospaso; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query = "DROP TABLE IF EXISTS camposPersonalizados; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query = "DROP TABLE IF EXISTS DiasAtraso; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query = "DROP TABLE IF EXISTS Seguro; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query = "DROP TABLE IF EXISTS GeneroEdad; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query = "DROP TABLE IF EXISTS MontoCliente; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);



        $conteopagospaso = " create temporary table conteopagospaso as " .
            " SELECT DISTINCT LINEOFCREDITKEY " .
            " ,R.duedate " .
            " FROM mambu_prod.loanaccount LA " .
            " INNER JOIN mambu_prod.repayment R " .
            " ON LA.ENCODEDKEY = R.PARENTACCOUNTKEY " .
            " inner join  mambu_prod.loanproduct LP " .
            " on LP.encodedkey = LA.producttypekey " .
            " WHERE state = 'PAID' " .
            " AND ACCOUNTSTATE = 'ACTIVE' " .
            " and productname  in ('Crédito Grupal Tradicional (Saldos Insolutos)') " .
            " GROUP BY R.duedate,LINEOFCREDITKEY; ";

        $statement = $pdo->query($conteopagospaso);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $camposPersonalizados = " create temporary table camposPersonalizados as " .
            " SELECT cfv.PARENTKEY " .
            " ,MAX(CASE WHEN Name = 'Centro de Costo' THEN VALUE ELSE NULL END) AS CentroDeCosto " .
            " ,'EntidadFederativaSucursal' " .
            " ,MAX(CASE WHEN Name = 'Región' THEN VALUE ELSE NULL END) AS Region " .
            " ,MAX(CASE WHEN Name = 'Fondeador' THEN VALUE ELSE NULL END) AS Fondeador " .
            " ,MAX(CASE WHEN Name = 'Fecha Etiquetado' THEN VALUE ELSE NULL END) AS FechaEtiquetado " .
            " ,MAX(CASE WHEN Name = 'Fecha baja etiquetado' THEN VALUE ELSE NULL END) AS FechaBaja " .
            " ,MAX(CASE WHEN Name = 'Producto' THEN VALUE ELSE NULL END) AS Producto " .
            " ,MAX(CASE WHEN Name = 'Ciclo Grupo' THEN VALUE ELSE NULL END) AS CicloGrupo " .
            " FROM mambu_prod.customfieldvalue cfv " .
            " INNER JOIN mambu_prod.customfield cf " .
            " ON cf.ENCODEDKEY = cfv.CUSTOMFIELDKEY and cf.type IN ('BRANCH_INFO','LINE_OF_CREDIT') " .
            " WHERE ID IN ('Centro_de_Costo_Sucursales','EF_Suc','Region','_IdFondeador','_Fecha_baja_etiquetado','_Producto','_Fecha_Etiquetado','Ciclo_Grupo_Credit_Arrangements') " .
            " GROUP BY cfv.PARENTKEY; ";
        $statement = $pdo->query($camposPersonalizados);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $DiasAtraso = " create temporary table DiasAtraso as  " .
            " SELECT lastsettoarrearsdate " .
            " ,isnull((CURRENT_DATE - lastsettoarrearsdate::DATE),0) AS DAY " .
            " ,CASE WHEN UPPER(LOANNAME) LIKE '%SALDOS INSOLUTOS%' THEN interestrate*12 ELSE (1.6904*interestrate+0.00465)*12 END  interestrate " .
            " ,L.LINEOFCREDITKEY " .
            " ,ACCOUNTSTATE " .
            " ,LOANNAME " .
            " FROM mambu_prod.loanaccount L " .
            " GROUP BY L.lineofcreditkey,L.interestrate,lastsettoarrearsdate,l.loanname,l.accountstate; ";

        $statement = $pdo->query($DiasAtraso);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $Seguro = " create temporary table Seguro as " .
            " SELECT  LA.LINEOFCREDITKEY " .
            " ,COUNT(CASE WHEN VALUE = 'FALSE' THEN VALUE ELSE NULL END) AS SEGURO_FALSE " .
            " ,COUNT(CASE WHEN VALUE = 'TRUE' THEN VALUE ELSE NULL END) AS SEGURO_TRUE " .
            " FROM mambu_prod.customfieldvalue CF " .
            " INNER JOIN mambu_prod.loanaccount LA " .
            " ON LA.ENCODEDKEY = CF.PARENTKEY " .
            " WHERE CUSTOMFIELDKEY = (SELECT ENCODEDKEY FROM mambu_prod.customfield " .
            " WHERE ID = 'Tiene_Seguro') " .
            " GROUP BY LINEOFCREDITKEY; ";
        $statement = $pdo->query($Seguro);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $MontoCliente = " create temporary table MontoCliente as " .
            " SELECT  LA.LINEOFCREDITKEY " .
            " ,count(CASE WHEN loanamount >= 83000 THEN 1 END) AS mayor80 " .
            " ,COUNT(CASE WHEN loanamount < 83000 THEN 1 END) AS menor80 " .
            " ,count(*) TotClientes " .
            " FROM  mambu_prod.loanaccount LA " .
            " GROUP BY LINEOFCREDITKEY; ";
        $statement = $pdo->query($MontoCliente);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);



        $generoedad1 = " create temporary table GeneroEdad as " .
        " select cv.parentkey " .
        " ,COUNT(CASE WHEN coalesce(idgenero,2) = 2 THEN 1 END) AS esMujer " .
        " ,COUNT(CASE WHEN idgenero = 1 THEN 1 END) AS esHombre " .
        " ,count(case when (date_part ('year', CURRENT_DATE) * 12 + date_part ('month', CURRENT_DATE)) - (date_part ('year', fechanacimiento) * 12 + date_part ('month', fechanacimiento)) > 959 then 1 end) AS mas80 " .
        " ,count(case when (date_part ('year', CURRENT_DATE) * 12 + date_part ('month', CURRENT_DATE)) - (date_part ('year', fechanacimiento) * 12 + date_part ('month', fechanacimiento)) < 216 then 1 end) AS menos18 " .
        " from mambu_prod.mambu_prod.customfieldvalue cv " .
        " inner join (select CG.idgrupo,c.id,c.fechanacimiento,c.idgenero from clienteunicofinanciera.clienteunicofinanciera.cliente c " .
        " inner join clienteunicofinanciera.clienteunicofinanciera.clientegrupo CG " .
        " on CG.idcliente = c.id and CG.activo = 1) SX " .
        " on  cv.value = SX.idgrupo " .
        " where customfieldkey = (SELECT  encodedkey " .
        " FROM  mambu_prod.mambu_prod.customfield c " .
        " where id = 'IdGrupo_Clients') " .
        " group by cv.parentkey; " ;
        $statement = $pdo->query($generoedad1);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);


        $query = " DROP TABLE IF EXISTS Principal; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $principal = " create temporary table Principal as " .
            " select DISTINCT * FROM( " .
            " SELECT C.FIRSTNAME AS strNombre " .
            " ,C.LASTNAME AS strApellido " .
            " ,B.NAME AS strSucursal " .
            " ,SUC.CentroDeCosto AS strCentroDeCostos " .
            " ,LC.AMOUNT AS decMontoLC " .
            " ,LC.ID AS strIdLC " .
            " ,FOND.Fondeador AS intFondeador " .
            " ,FOND.FechaEtiquetado AS strFechaEtiquetado " .
            " ,FOND.FechaBaja AS strFechaBaja " .
            " ,DAYS.interestrate AS decTasaIns " .
            " ,DAYS.LOANNAME AS strProducto " .
            " ,DAYS.DAY " .
            " ,PAG.Pago AS intNumPago " .
            " ,LC.ENCODEDKEY " .
            " ,SEGURO_FALSE " .
            " ,SEGURO_TRUE " .
            " ,mayor80 " .
            " ,menor80 " .
            " ,TotClientes " .
            " ,esMujer " .
            " ,esHombre " .
            " ,mas80 " .
            " ,menos18 " .
            ", FOND.ciclogrupo " .
            " FROM mambu_prod.client C " .
            " INNER JOIN mambu_prod.branch B " .
            " ON B.ENCODEDKEY = C.ASSIGNEDBRANCHKEY " .
            " inner join camposPersonalizados SUC " .
            " ON SUC.PARENTKEY = B.ENCODEDKEY " .
            " INNER JOIN mambu_prod.lineofcredit LC " .
            " ON LC.CLIENTKEY = C.ENCODEDKEY " .
            " inner join camposPersonalizados FOND " .
            " ON FOND.PARENTKEY = LC.ENCODEDKEY " .
            " INNER JOIN DiasAtraso DAYS " .
            " ON DAYS.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            " INNER JOIN (SELECT COUNT(*) AS Pago,LINEOFCREDITKEY from conteopagospaso " .
            " GROUP BY LINEOFCREDITKEY) PAG " .
            " ON PAG.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            " INNER JOIN Seguro SEG " .
            " ON SEG.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            " inner join MontoCliente MC " .
            " on MC.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            " inner join GeneroEdad GE " .
            " on GE.parentkey = LC.clientkey " .
            " ORDER BY 3 " .
            " ) TBL " .
            " WHERE " .
            " day <=21 " .
            " and decmontolc <= 2900000 " .
            " and Mayor80 = 0 " .
            " and COALESCE(intNumPago,0) BETWEEN 1 AND 12 " .
            " AND decTasaIns::double precision >=  20 " .
            " and TotClientes >= 5 " .
            " and mas80 = 0 " .
            " and menos18 = 0 " .
            " AND COALESCE(intFondeador,'CREDITO REAL') = 'CREDITO REAL' " .
            " and (esMujer = (case when (esMujer >= 5 and esMujer <= 9 and esHombre = 0) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 10 and esMujer <= 11 and esHombre <= 2) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 12 and esMujer <= 15 and esHombre <= 3) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 16 and esMujer <= 19 and esHombre <= 4) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 20 and esMujer <= 23 and esHombre <= 5) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 24 and esMujer <= 27 and esHombre <= 6) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 28 and esMujer <= 31 and esHombre <= 7) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 32 and esMujer <= 35 and esHombre <= 8) then esMujer end) " .
            " or esMujer = (case when (esMujer >= 36 and esHombre <= 9) then esMujer end)) and ciclogrupo > 1 " .
            "union " .
            " select DISTINCT *FROM( " .
            "SELECT C.FIRSTNAME AS strNombre " .
            ", C.LASTNAME AS strApellido " .
            ", B.NAME AS strSucursal " .
            ", SUC.CentroDeCosto AS strCentroDeCostos " .
            ", LC.AMOUNT AS decMontoLC " .
            ", LC.ID AS strIdLC " .
            ", FOND.Fondeador AS intFondeador " .
            ", FOND.FechaEtiquetado AS strFechaEtiquetado " .
            ", FOND.FechaBaja AS strFechaBaja " .
            ", DAYS.interestrate AS decTasaIns " .
            ", DAYS.LOANNAME AS strProducto " .
            ", DAYS.DAY " .
            ", PAG.Pago AS intNumPago " .
            ", LC.ENCODEDKEY " .
            ", SEGURO_FALSE " .
            ", SEGURO_TRUE " .
            ", mayor80 " .
            ", menor80 " .
            ", TotClientes " .
            ", esMujer " .
            ", esHombre " .
            ", mas80 " .
            ", menos18 " .
            ", FOND.ciclogrupo " .
            "FROM mambu_prod.client C  " .
            "INNER JOIN mambu_prod.branch B  ON B.ENCODEDKEY = C.ASSIGNEDBRANCHKEY " .
            "inner join camposPersonalizados SUC  ON SUC.PARENTKEY = B.ENCODEDKEY " .
            "INNER JOIN mambu_prod.lineofcredit LC  ON LC.CLIENTKEY = C.ENCODEDKEY " .
            "inner join camposPersonalizados FOND  ON FOND.PARENTKEY = LC.ENCODEDKEY " .
            "INNER JOIN DiasAtraso DAYS  ON DAYS.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            "INNER JOIN(SELECT COUNT(*) AS Pago, LINEOFCREDITKEY from conteopagospaso GROUP BY LINEOFCREDITKEY) PAG  ON PAG.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            "INNER JOIN Seguro SEG  ON SEG.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            "inner join MontoCliente MC on MC.LINEOFCREDITKEY = LC.ENCODEDKEY " .
            "inner join GeneroEdad GE on GE.parentkey = LC.clientkey " .
            "ORDER BY 3 " .
            ") TBL " .
            "WHERE " .
            "day <= 21 " .
            "and decmontolc <= 2900000 " .
            "and Mayor80 = 0 " .
            "and COALESCE(intNumPago,0) BETWEEN 1 AND 12 " .
            "AND decTasaIns::double precision >= 20 " .
            "and TotClientes >= 5  " .
            "and mas80 = 0 " .
            "and menos18 = 0 " .
            "AND COALESCE(intFondeador,'CREDITO REAL') = 'CREDITO REAL'  " .
            "and(esMujer = (case when(esMujer >= 5 and esMujer <= 9 and esHombre = 0) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 10 and esMujer <= 11 and esHombre <= 2) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 12 and esMujer <= 15 and esHombre <= 3) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 16 and esMujer <= 19 and esHombre <= 4) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 20 and esMujer <= 23 and esHombre <= 5) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 24 and esMujer <= 27 and esHombre <= 6) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 28 and esMujer <= 31 and esHombre <= 7) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 32 and esMujer <= 35 and esHombre <= 8) then esMujer end) " .
            "or esMujer = (case when(esMujer >= 36 and esHombre <= 9) then esMujer end)) " .
            "and ciclogrupo = 1  " .
            "and intNumPago > 1  " .
            "; " ;
            $statement = $pdo->query($principal);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);


            $query = "drop table conteopagospaso; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $query = "drop table camposPersonalizados; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $query = "drop table DiasAtraso; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $query = "drop table Seguro; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $query = "drop table GeneroEdad; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $query = "drop table MontoCliente; ";
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);



        $query = "select * from Principal; ";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);


        return $result
        ;
    }
}
