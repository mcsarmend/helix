<?php

namespace App\Http\Controllers;

use DateTimeZone;
use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\logenviosinterfaces;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use mysqli;
use Nette\Utils\DateTime;
use PDO;
use PDOException;
use Linq;
use TheSeer\Tokenizer\Exception;


class interfacescxcController extends Controller
{

    public function generaInterfaces()
    {
        $date = new DateTime('-1 day');
        $duedate = $date->format('Y-m-d');

        $resSincro = $this->generarSincronizacion($duedate);
        $envioSincro = $this->enviarinformacion($resSincro, $api = 'datos-generales');
        //$resClientes = $this->generarClientes($request->duedate);
        //$envioClientes = $this->enviarinformacion($resClientes, $api = 'clientes');
        //$resTransacciones = $this->GeneraTransaccionesAsync($duedate);
        //$envioTransacciones = $this->enviarinformacion($resTransacciones, $api = 'transacciones');
        //$resTransaccionesCuentas = $this->GeneraTransaccionesCuentas($request->duedate);
        //$envioTransaccionesCuentas = $this->enviarinformacion($resTransaccionesCuentas, $api = 'transacciones-cuentas');
        //$resAmortizacion = $this->amortizacion($request->duedate);
        //$envioAmortizacion = $this->enviarinformacion($resAmortizacion, $api = 'amortizacion');

        //$resClaves = $this->generarClientes($request->duedate);
        //$envioClaves = $this->enviarinformacion($resClaves, $api = 'clientes');

        return $envioSincro;
    }
    //OBTIENE INFORMACION PARA ENVIAR LAS APIS
    public function generarSincronizacion($duedate)
    {
        $error = "";
        $tableData = [];
        $datos = "";
        $dataFinal = [];

        $strConnString = "pgsql:host=fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com;port=5439;dbname=mambu_prod;user=marcadodev;password=marcadoDev00";
        $DDate = new DateTime ($duedate,new DateTimeZone('America/Mexico_City'));
        $DDate->setTime(0, 0, 0);
        $format = Carbon::createFromFormat('Y-m-d',$duedate)->format('dmY');

        try
        {
            $pdo = new PDO($strConnString);

            $query = "SELECT DISTINCT LC.ID AS ACUERDO,
                            coalesce(COALESCE(replace(CLI.FIRSTNAME, '\"', ''), '') || ' ' || COALESCE(replace(CLI.LASTNAME, '\"', ''), ''), '') AS NombreTitularCuenta,
                            (SELECT DISTINCT ROUND(sum(L.loanamount + L.feespaid + l.feesdue), 2) FROM mambu_marcado_bursa.loanaccount L WHERE L.lineofcreditkey = min(LC.encodedkey)) AS Monto,
                            (SELECT sum(LA.principalbalance) FROM mambu_marcado_bursa.loanaccount AS LA WHERE LA.lineofcreditkey = min(LC.encodedkey)) AS SaldoCapital,
                            (SELECT DISTINCT coalesce(round(sum(RE_S.INTERESTDUE - RE_S.interestpaid), 2), 0) FROM mambu_marcado_bursa.loanaccount L
                             JOIN mambu_marcado_bursa.repayment RE_S ON RE_S.PARENTACCOUNTKEY = L.ENCODEDKEY AND RE_S.STATE != 'PAID'
                             WHERE L.lineofcreditkey = min(LC.encodedkey) AND L.ACCOUNTSTATE != 'CLOSED') AS SaldoInteres,
                            current_date::date AS CORTE,
                            CASE WHEN LC.STATE = 'ACTIVE' THEN 'Activo'
                                 WHEN LC.STATE = 'LATE' THEN 'Atrasado'
                                 WHEN LC.STATE = 'CLOSED' THEN 'Liquidado'
                            END AS Estatus,
                            CASE WHEN CVF.VALUE IS NULL THEN 'CONTIGO'
                                 WHEN CVF.VALUE = 'CREDITO REAL' THEN 'CONTIGO'
                                 ELSE CVF.VALUE
                            END AS Fondeador,
                            LA.INTERESTRATE AS Tasa,
                            to_char(LC.STARTDATE::date, 'yyyy-mm-dd') AS Fecha_Emision,
                            to_char(LC.EXPIREDATE::date, 'yyyy-mm-dd') AS Fecha_Vencimiento_Credito,
                            LA.REPAYMENTINSTALLMENTS AS Plazo_Credito,
                            CASE WHEN LA.REPAYMENTPERIODUNIT = 'WEEKS' THEN 'Semanal'
                                 WHEN LA.REPAYMENTPERIODUNIT = 'DAYS' THEN 'Diario'
                            END AS Periodicidad,
                            CFV_P.VALUE AS Origen_Tipo_Credito,
                            'S' AS Indicador_Seguro,
                            (SELECT count(DISTINCT(RE_S.DUEDATE)) FROM mambu_marcado_bursa.lineofcredit LC_C
                             JOIN mambu_marcado_bursa.loanaccount la ON la.ACCOUNTHOLDERKEY = LC_C.CLIENTKEY AND la.ACCOUNTSTATE != 'CLOSED'
                             JOIN mambu_marcado_bursa.repayment RE_S ON RE_S.PARENTACCOUNTKEY = la.ENCODEDKEY AND RE_S.DUEDATE <= current_date
                             WHERE LC_C.ENCODEDKEY = min(LC.ENCODEDKEY)) AS Numero_Cuota_Encurso,
                            (SELECT DISTINCT CASE WHEN RE_S.STATE = 'PENDING' THEN 'Pendiente'
                                                    WHEN RE_S.state = 'PAID' THEN 'Pagado'
                                                    WHEN RE_S.state = 'LATE' THEN 'Atrasado'
                                                    WHEN RE_S.state = 'ACTIVE_IN_ARREARS' THEN 'Atrasado'
                                                    WHEN RE_S.state = 'PARTIALLY_PAID' THEN 'Atrasado'
                                                    WHEN (RE_S.state IS NULL OR RE_S.state = NULL) THEN 'Cerrado'
                                                    ELSE 'Cerrado'
                                            END FROM mambu_marcado_bursa.lineofcredit LC_C
                             LEFT JOIN mambu_marcado_bursa.loanaccount la ON la.lineofcreditkey = LC_C.encodedkey AND la.ACCOUNTSTATE != 'CLOSED'
                             LEFT JOIN mambu_marcado_bursa.repayment RE_S ON RE_S.PARENTACCOUNTKEY = la.ENCODEDKEY AND RE_S.DUEDATE >= (current_date - interval '7 days')
                             WHERE LC_C.ENCODEDKEY = min(LC.encodedkey)
                             ORDER BY DUEDATE ASC LIMIT 1) AS Estado_Pago,
                            (SELECT DISTINCT COALESCE(round(sum(RE_S.PRINCIPALDUE), 2), '0') FROM mambu_marcado_bursa.loanaccount L
                             JOIN mambu_marcado_bursa.repayment RE_S ON RE_S.PARENTACCOUNTKEY = L.ENCODEDKEY AND RE_S.STATE != 'PAID'
                             WHERE L.lineofcreditkey = min(LC.encodedkey) AND L.ACCOUNTSTATE != 'CLOSED'
                             AND RE_S.duedate <= (current_date + interval '7 days')
                             LIMIT 1) AS CAPITAL_AMORTIZACION,
                            (SELECT DISTINCT COALESCE(round(sum(RE_S.interestdue), 2), '0') FROM mambu_marcado_bursa.loanaccount L
                             JOIN mambu_marcado_bursa.repayment RE_S ON RE_S.PARENTACCOUNTKEY = L.ENCODEDKEY AND RE_S.STATE != 'PAID'
                             WHERE L.lineofcreditkey = min(LC.encodedkey) AND L.ACCOUNTSTATE != 'CLOSED'
                             AND RE_S.duedate <= (current_date + interval '7 days')
                             LIMIT 1) AS INTERES_AMORTIZACION,
                            'RAP' AS IDENTIFICADOR_DEUDOR_ENCURSO,
                            B.NAME AS SUCURSAL,
                            CV3.VALUE AS CICLO,
                            LP.productname AS PRODUCTO,
                            CASE WHEN C_PR.value = 'PROMECAP' THEN 'SI'
                                 ELSE 'NO'
                            END AS PREETIQUETADO,
                            coalesce(to_char(C_PR2.value::date, 'yyyy-MM-dd'), 'S/F') AS FECHAPREETIQUETADO,
                            coalesce(to_char(CVF_E.value::date, 'yyyy-MM-dd'), 'S/F') AS FECHAETIQUETADO,
                            coalesce(C_PR3.value, 'S/F') AS GARANTIA,
                            coalesce(C_PR4.value, 'S/F') AS IdGrupo,
                            (isnull((current_date - 1) - to_date((SELECT DISTINCT (L.lastsettoarrearsdate) FROM mambu_marcado_bursa.loanaccount L WHERE L.lineofcreditkey = min(LC.encodedkey) LIMIT 1), 'YYYY-MM-DD'), 0)) AS DiasMora
                      FROM mambu_marcado_bursa.lineofcredit AS LC
                      JOIN mambu_marcado_bursa.client AS CLI ON CLI.ENCODEDKEY = LC.CLIENTKEY
                      JOIN mambu_marcado_bursa.customfieldvalue CV ON CV.PARENTKEY = CLI.ENCODEDKEY
                      JOIN mambu_marcado_bursa.loanaccount LA ON LA.lineofcreditkey = LC.encodedkey
                      JOIN mambu_marcado_bursa.branch B ON B.ENCODEDKEY = LA.ASSIGNEDBRANCHKEY
                      LEFT JOIN mambu_marcado_bursa.customfield AS CF ON CF.ID = '_IdFondeador'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue AS CVF ON CVF.CUSTOMFIELDKEY = CF.ENCODEDKEY AND CVF.PARENTKEY = LC.ENCODEDKEY
                      LEFT JOIN mambu_marcado_bursa.customfield AS CF_E ON CF_E.ID = '_Fecha_Etiquetado'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue AS CVF_E ON CVF_E.CUSTOMFIELDKEY = CF_E.ENCODEDKEY AND CVF_E.PARENTKEY = LC.ENCODEDKEY
                      JOIN mambu_marcado_bursa.loanproduct LP ON LP.encodedkey = LA.producttypekey
                      JOIN mambu_marcado_bursa.customfield AS CF_P ON CF_P.ID = '_Producto'
                      JOIN mambu_marcado_bursa.customfieldvalue AS CFV_P ON CFV_P.CUSTOMFIELDKEY = CF_P.ENCODEDKEY AND CFV_P.parentkey = LC.encodedkey
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue CV3 ON CV3.PARENTKEY = LC.ENCODEDKEY AND CV3.CUSTOMFIELDKEY = '8a4422367be572b8017be640f98d5075'
                      LEFT JOIN mambu_marcado_bursa.customfield cf2 ON cf.ENCODEDKEY = CV3.CUSTOMFIELDKEY AND cf2.type IN ('Ciclo Grupo')
                      LEFT JOIN mambu_marcado_bursa.customfield CPR ON CPR.id = '_preetiquetado'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue C_PR ON C_PR.customfieldkey = CPR.encodedkey AND C_PR.parentkey = LC.encodedkey
                      LEFT JOIN mambu_marcado_bursa.customfield CPR2 ON CPR2.id = '_fecha_preetiquetado'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue C_PR2 ON C_PR2.customfieldkey = CPR2.encodedkey AND C_PR2.parentkey = LC.encodedkey
                      LEFT JOIN mambu_marcado_bursa.customfield CPR3 ON CPR3.id = '_GarantiaLiquida'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue C_PR3 ON C_PR3.customfieldkey = CPR3.encodedkey AND C_PR3.parentkey = LC.encodedkey
                      LEFT JOIN mambu_marcado_bursa.customfield CPR4 ON CPR4.id = 'IdGrupo_Clients'
                      LEFT JOIN mambu_marcado_bursa.customfieldvalue C_PR4 ON C_PR4.customfieldkey = CPR4.encodedkey AND C_PR4.parentkey = LC.clientkey
                      WHERE LA.producttypekey IN ('8a445c7c7e352b74017e35fa9f696648', '8a445c7c7e352b74017e35fddcad6856', '8a445c7c7e352b74017e35fef4c7692a')
                      AND (LA.accountstate NOT IN ('CLOSED', 'APPROVED') OR LA.closeddate::timestamp AT TIME ZONE 'Etc/GMT-0' >= (SELECT to_timestamp('". $duedate ." 06:00:00', 'YYYY-MM-DD HH:MI:SS')))
                      AND (LC.state NOT IN ('CLOSED', 'APPROVED') OR LC.closeddate::timestamp AT TIME ZONE 'Etc/GMT-0' >= (SELECT to_timestamp('".$duedate." 06:00:00', 'YYYY-MM-DD HH:MI:SS')))
                      GROUP BY LC.ID, CLI.firstname, CLI.lastname, LC.state, LC.substate, cvf.value, la.interestrate, lc.startdate, lc.expiredate, la.repaymentinstallments, la.repaymentperiodunit, cfv_p.value, B.name, cv3.value, LP.productname, C_PR.value, C_PR2.value, CVF_E.value, C_PR3.value, C_PR4.value
                      ORDER BY LC.ID ASC ";

                      ini_set('max_execution_time',300);
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data as $lect) {
                        $dataFinal[] = [
                            'IDENTIFICADOR_CREDITO' => $lect["acuerdo"],
                            'NOMBRE_EQUIPO' => $lect["nombretitularcuenta"],
                            'MONTO_OTORGADO' => $lect["monto"],
                            'SALDO_CAPITAL' => $lect["saldocapital"],
                            'SALDO_INTERES' => $lect["saldointeres"],
                            'FECHA_CORTE' => $lect["corte"],
                            'ESTADO_CREDITO' => $lect["estatus"],
                            'ID_FONDEADOR' => $lect["fondeador"],
                            'TASA_EMISION' => $lect["tasa"],
                            'FECHA_EMISION' => $lect["fecha_emision"],
                            'FECHA_VENCIMIENTO_CREDITO' => $lect["fecha_vencimiento_credito"],
                            'PLAZO_CREDITO' => $lect["plazo_credito"],
                            'PERIODICIDAD' => $lect["periodicidad"],
                            'ORIGEN_TIPO_CREDITO' => $lect["origen_tipo_credito"],
                            'INDICADOR_SEGURO' => $lect["indicador_seguro"],
                            'NUMERO_CUOTA_ENCURSO' => $lect["numero_cuota_encurso"],
                            'ESTADO_PAGO' => $lect["estado_pago"],
                            'AMORTIZACION_ENCURSO' => $lect["capital_amortizacion"],
                            'MONTO_INTERES_ENCURSO' => $lect["interes_amortizacion"],
                            'IDENTIFICADOR_DEUDOR_ENCURSO' => $lect["identificador_deudor_encurso"],
                            'SUCURSAL' => $lect["sucursal"],
                            'CICLO' => $lect["ciclo"],
                            'PRODUCTO' => $lect["producto"],
                            'PRE_ETIQUETADO' => $lect["preetiquetado"],
                            'FECHA_PRE_ETIQUETADO' => $lect["fechapreetiquetado"],
                            'FECHA_ETIQUETADO' => $lect["fechaetiquetado"],
                            'ESTADO_ETIQUETADO' => 'NO ENVIADO',
                            'PROCENTAJE_GARANTIA' => $lect["garantia"],
                            'ID_GRUPO' => $lect["idgrupo"],
                            'DIAS_MORA' => $lect["diasmora"],
                            'FECHA_PROCESO' => $format
                        ];
                    }
            return $dataFinal;
        }
        catch (\Throwable $th)
        {
            return 'Error en el query1: ' . $th;
        }
    }
    public function generarClientes($duedate)
    {
        try
        {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';

            $host2 = 'fcontigodbuprod01.cluster-cwdlibnyktew.us-east-1.rds.amazonaws.com';
            $port2 = '3306';
            $user2 = 'jtolentino';
            $password2 = 'A123456';
            $database2 = 'clienteunicofinanciera';

            $format = Carbon::createFromFormat('Y-m-d',$duedate)->format('dmY');

            //Busca Clientes Desembolsados Anteriores
            $Anteriores = $this->DesembolsosAnteriores();
            $recorre = json_decode($Anteriores);
            //Busca Clientes Cancelados
            $Cancelados = $this->DesembolsosCancelados();
            $recorreCan = json_decode($Cancelados);
            //Busca Desembolsos Diarios
            $Diario = $this->DesembolsosDiario();
            $recorreDiario = json_decode($Diario);

            $PrimerMerge = array_merge($recorre,$recorreCan);
            $SegundoMerge = array_merge($PrimerMerge,$recorreDiario);

            $Acuerdos = [];
            $Clientes = [];

            for ($i=0;$i< count($SegundoMerge);$i++)
            {
                //echo json_encode($Anteriores[$i]->id) . "\n";
                array_push($Acuerdos,$SegundoMerge[$i]->id);
                array_push($Clientes,$SegundoMerge[$i]->clave_deudor);
            }
            //Obtiene información final
            $tmp = [];
            try
            {

                //OBTENER DATA CLIENTE UNICO
                for ($i=0; $i < count($Clientes) ; $i++)
                {
                    $jen = json_encode($Clientes[$i]);
                    $jde = json_decode($jen);

                    $DeudorCursor = $jde;

                    $dsn2 = "mysql:host=$host2;dbname=$database2;charset=UTF8";
                    $pdo2 = new PDO($dsn2, $user2, $password2);
                    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $query = "SELECT DISTINCT  '". $Acuerdos[$i] . "' AS ACUERDO, ".
                    "COALESCE(c.RFC, '') AS RFC, ".
                    "coalesce(convert(c.id,char), '') AS ID, ".
                    "COALESCE(c.APELLIDOPATERNO,'X') AS APELLIDOPATERNO, ".
                    "COALESCE(c.APELLIDOMATERNO, 'X') AS APELLIDOMATERNO, ".
                    "coalesce (COALESCE(c.NOMBRE, '') || ' ' || COALESCE (c.SEGUNDONOMBRE,''), 'S/N')  AS NOMBRE, ".
                    "coalesce(convert(c.FECHANACIMIENTO,char),'') AS FECHANACIMIENTO, ".
                    "coalesce(COALESCE(d.CALLE, '')|| ' ' || COALESCE (d.NUMEROEXTERIOR, '')|| ' '|| COALESCE (d.NUMEROINTERIOR, ''),'S/D') AS DIRECCION, ".
                    "COALESCE(replace(cp.COLONIA, '\"', ''),' - ') AS COLONIA, ".
                    "COALESCE(e.Descripcion,'') AS ESTADO, ".
                    "COALESCE(cp.CODIGO,'') AS CODIGO, ".
                    "COALESCE(c.TELEFONOPARTICULAR, 'N/A') AS TELEFONOPARTICULAR, ".
                    "COALESCE(c.TELEFONOMOVIL,'N/A') AS TELEFONOMOVIL, ".
                    "COALESCE(g.Descripcion,'') AS SEXO, ".
                    "COALESCE(ec.Descripcion,'') AS ESTADOCIVIL, ".
                    "COALESCE(p.Descripcion,'') AS NACIONALIDAD, ".
                    "'N/A' AS ACTIVIDAD_ECONOMICA ".
                    "FROM clienteunicofinanciera.cliente AS c ".
                    "JOIN clienteunicofinanciera.clientegrupo AS GR ON GR.idCliente = c.id ".
                    "LEFT JOIN clienteunicofinanciera.clientedireccion AS dc ON dc.idcliente = c.id ".
                    "LEFT JOIN clienteunicofinanciera.direccion AS d ON d.id = dc.IdDireccion ".
                    "LEFT JOIN clienteunicofinanciera.codigopostal AS cp ON cp.id = d.IdCodigoPostal ".
                    "LEFT JOIN clienteunicofinanciera.estadomunicipiociudad emc ON emc.Id = cp.IdEstadoMunicipioCiudad ".
                    "LEFT JOIN clienteunicofinanciera.estado AS e ON e.Id = emc.IdEstado ".
                    "LEFT JOIN clienteunicofinanciera.municipio AS m ON m.Id = IdMunicipio ".
                    "LEFT JOIN clienteunicofinanciera.ciudad AS ci ON ci.Id = IdCiudad ".
                    "LEFT JOIN clienteunicofinanciera.genero AS g ON g.Id = c.IdGenero ".
                    "LEFT JOIN clienteunicofinanciera.estadocivil ec ON ec.id = c.IdEstadoCivil ".
                    "LEFT JOIN clienteunicofinanciera.pais AS p ON p.id = c.IdNacionalidad ".
                    "WHERE GR.IdGrupo in ('". $DeudorCursor ."') ".
                    "and GR.activo = 1; "
                    ;

                    $statement  = $pdo2->query($query);
                    $join = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $pdo2 = null;


                    for ($j=0; $j < count($join); $j++)
                    {
                        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                        $pdo = new PDO($dsn, $user, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $jjson = json_encode($join[$j]);
                        $jjson2 = json_decode($jjson);
                        $cliente = $jjson2->ID;


                        $query2 = "SELECT DISTINCT ". "LP.id AS CODIGO_PRODUCTO ".
                        "from mambu_marcado_bursa.lineofcredit LC ".
                            "join mambu_marcado_bursa.loanaccount L ".
                                "on L.lineofcreditkey = LC.encodedkey  ".
                            "join mambu_marcado_bursa.loanproduct LP ".
                                "on LP.encodedkey = L.producttypekey  ".
                            "join mambu_marcado_bursa.customfield c  ".
                                "on C.id = 'Cliente_Grupal_Loan_Accounts' ".
                            "join mambu_marcado_bursa.customfieldvalue c2  ".
                                "on c2.customfieldkey = c.encodedkey and c2.parentkey = l.encodedkey  ".
                            "join mambu_marcado_bursa.customfield cf  ".
                                "on Cf.id = 'idCustomer_Clientes' ".
                            "join mambu_marcado_bursa.customfieldvalue c3  ".
                                "on c3.customfieldkey = cf.encodedkey and c3.parentkey = c2.linkedentitykeyvalue   ".
                        "where c3.value = '".$cliente."' ".
                        "and LC.id = '".$Acuerdos[$i]."'"
                        ;

                        ini_set('max_execution_time', 300);
                        $statement2  = $pdo->query($query2);
                        $join2 = $statement2->fetchAll(PDO::FETCH_ASSOC);

                        $jjsonj = json_encode($join2[0]);
                        $jjson2j = json_decode($jjsonj);

                        $prod = $jjson2j->codigo_producto;


                        $data = [];
                        $data['IDENTIFICADOR_CREDITO'] = $jjson2->ACUERDO;
                        $data['RFC'] = $jjson2->RFC;
                        $data['IDENTIFICADOR_DEUDOR'] = $jjson2->ID;
                        $data['APELLIDO_PATERNO'] = $jjson2->APELLIDOPATERNO;
                        $data['APELLIDO_MATERNO'] = $jjson2->APELLIDOMATERNO;
                        $data['NOMBRES'] = $jjson2->NOMBRE;
                        $data['FECHA_NACIMIENTO'] = $jjson2->FECHANACIMIENTO;
                        $data['DIRECCION'] = $jjson2->DIRECCION;
                        $data['COLONIA'] = $jjson2->COLONIA;
                        $data['ESTADO'] = $jjson2->ESTADO;
                        $data['CP'] = $jjson2->CODIGO;
                        $data['TELEFONO'] = $jjson2->TELEFONOPARTICULAR;
                        $data['CELULAR'] = $jjson2->TELEFONOMOVIL;
                        $data['SEXO'] = $jjson2->SEXO;
                        $data['ESTADO_CIVIL'] = $jjson2->ESTADOCIVIL;
                        $data['NACIONALIDAD'] = $jjson2->NACIONALIDAD;
                        $data['ACTIVIDAD_ECONOMICA'] = $jjson2->ACTIVIDAD_ECONOMICA;
                        $data['CODIGO_PRODUCTO'] = $prod;
                        $data['FECHA_PROCESO'] = $format;


                        array_push($tmp, $data);
                        $pdo = null;

                    }


                }
                $Final = $tmp;

                if($Final == '')  return []; else return $Final;

            } catch (PDOException $e) {
                echo 'Error al conectar a PostgreSQL: ' . $e->getMessage();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
    public function GeneraTransaccionesAsync($duedate)
    {
    $DDate = new DateTime($duedate);

    // Set the time to the start of the day
    $DDate->setTime(0, 0, 0);

    $datos2 = [];
    $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
    $port = '5439';
    $database = 'mambu_prod';
    $user = 'marcadodev';
    $password = 'marcadoDev00';
    $format = Carbon::createFromFormat('Y-m-d',$duedate)->format('dmY');

    try
    {

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $user, $password);
        // Configurar opciones adicionales si es necesario
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT DISTINCT " .
        "LC.ID AS ACUERDO, " .
        "ROUND(LT.PRINCIPALAMOUNT,2) AS MONTO_CAPITAL, " .
        "ROUND((LT.interestamount - LT.deferredinterestamount)- (LT.taxoninterestamount - LT.deferredtaxoninterestamount),2) as MONTO_INTERES, " .
        "ROUND(LT.taxoninterestamount - LT.deferredtaxoninterestamount, 2) as IVA_INTERES, " .
        "ROUND(LT.deferredinterestamount - LT.deferredtaxoninterestamount , 2) as INTERES_DIFERIDO, " .
        "ROUND(LT.deferredtaxoninterestamount, 2) as IVA_INTERES_DIFERIDO, " .
        "LT.type AS TIPOTRX, " .
        "CASE " .
        "WHEN LT.type = 'DISBURSMENT' THEN 'DES' " .
        "WHEN LT.type = 'DISBURSMENT_ADJUSTMENT' THEN 'AJUSTE_DESEMBOLSO' " .
        "WHEN LT.type = 'INTEREST_APPLIED' THEN 'INTERES_APLICADO' " .
        "WHEN LT.type = 'INTEREST_APPLIED_ADJUSTMENT' THEN 'AJUSTE_INTERES_APLICADO' " .
        "WHEN LT.type = 'REPAYMENT' THEN 'PAG' " .
        "WHEN LT.type = 'REPAYMENT_ADJUSTMENT' THEN 'REPAYMENT_ADJUSTMENT' " .
        "WHEN LT.type = 'WRITE_OFF' THEN 'PASAR A PERDIDA' " .
        "WHEN LT.type = 'WRITE_OFF_ADJUSTMENT' THEN 'AJUSTE PASAR A PERDIDA' " .
        "WHEN LT.type = 'DEFERRED_INTEREST_PAID' THEN 'INTERES_DIFERIDO_PAG' " .
        "WHEN LT.type = 'DEFERRED_INTEREST_PAID_ADJUSTMENT' THEN 'AJUSTE_INTERES_DIFERIDO_PAG' " .
        "WHEN LT.type = 'BRANCH_CHANGED' THEN 'CAMBIO_SUCURSAL' " .
        "END AS ORIGEN, " .
        "to_char(LT.CREATIONDATE::timestamp at time zone 'Etc/GMT-6', 'yyyymmdd')  AS FECHA, " .
        "GE.TYPE AS CA , " .
        "LT.TRANSACTIONID AS IDTRANSACCION, " .
        "coalesce(CVTP.value,coalesce(STII.transactionid::text,'N/A')) as TRANSACCION_PADRE,  " .
        "coalesce(TCP.name, coalesce(TCPI.name,'N/A')) as NOMBRE_CANAL_PAGO " .
        "from mambu_marcado_bursa.gljournalentry GE " .
        "JOIN mambu_marcado_bursa.glaccount GA ON GA.encodedkey = GE.GLACCOUNT_ENCODEDKEY_OID " .
        "join mambu_marcado_bursa.loanaccount L on GE.ACCOUNTKEY = L.ENCODEDKEY " .
        "join mambu_marcado_bursa.loantransaction LT ON LT.TRANSACTIONID = GE.TRANSACTIONID " .
        "join mambu_marcado_bursa.lineofcredit LC on L.LINEOFCREDITKEY = LC.encodedkey " .
        "left join mambu_prod.mambu_marcado_bursa.savingstransaction ST on ST.linkedloantransactionkey = LT.encodedkey " .
        "left join mambu_prod.mambu_marcado_bursa.savingsaccount as SA on SA.encodedkey = ST.parentaccountkey " .
        "left join mambu_marcado_bursa.customfieldvalue CVTP on CVTP.parentkey = ST.encodedkey " .
        "left join mambu_marcado_bursa.customfield CFTP on CFTP.encodedkey = CVTP.customfieldkey and CFTP.type ='TRANSACTION_TYPE_INFO' " .
        "left join mambu_marcado_bursa.savingstransaction STP ON STP.TRANSACTIONID = CVTP.value " .
        "left join mambu_marcado_bursa.transactiondetails as TDP on TDP.encodedkey = STP.details_encodedkey_oid " .
        "left join mambu_marcado_bursa.transactionchannel as TCP on TCP.encodedkey = TDP.transactionchannelkey " .
        "left join mambu_marcado_bursa.loantransaction LTI on LTI.reversaltransactionkey = LT.encodedkey " .
        "left join mambu_marcado_bursa.savingstransaction STI on STI.linkedloantransactionkey = LTI.encodedkey " .
        "left join mambu_marcado_bursa.customfieldvalue CVTPI on CVTPI.parentkey = STI.encodedkey " .
        "left join mambu_marcado_bursa.customfield CFTPI on CFTPI.encodedkey = CVTPI.customfieldkey and CFTPI.type ='TRANSACTION_TYPE_INFO' " .
        "left join mambu_marcado_bursa.savingstransaction STII on STII.transactionid = CVTPI.value " .
        "left join mambu_marcado_bursa.transactiondetails as TDPI on TDPI.encodedkey = STII.details_encodedkey_oid " .
        "left join mambu_marcado_bursa.transactionchannel as TCPI on TCPI.encodedkey = TDPI.transactionchannelkey " .
        "where PRODUCTKEY in ('8a445c7c7e352b74017e35fa9f696648','8a445c7c7e352b74017e35fddcad6856','8a445c7c7e352b74017e35fef4c7692a','8a445c7c7e352b74017e35f9209365b3') " .
        "and L.accountstate in ('ACTIVE','ACTIVE_IN_ARREARS','CLOSED','CLOSED_WRITTEN_OFF') " .
        //"and LT.transactionid in ('') " .
        //"and LC.ID in ('')" .
        "and (LT.CREATIONDATE::timestamp at time zone 'Etc/GMT-0' >= (SELECT to_timestamp('" . $duedate . " 06:00:00','yyyy-MM-dd HH:MI:SS'))) " .
        "order by LT.transactionid asc ; ";

        ini_set('max_execution_time',300);

        $stmt = $pdo->prepare($query);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $datos2[] = [
                    'IDENTIFICADOR_CREDITO' => $row['acuerdo'],
                    'NUMERO_CUOTA' => '1',
                    'MONTO_CAPITAL' => $row['monto_capital'],
                    'MONTO_INTERES' => $row['monto_interes'],
                    'MONTO_IVA_INTERES' => $row['iva_interes'],
                    'MONTO_INTERES_DIFERIDO' => $row['interes_diferido'],
                    'MONTO_IVA_INTERES_DIFERIDO' => $row['iva_interes_diferido'],
                    'TIPO_TRANSACCION' => $row['tipotrx'],
                    'ORIGEN_PAGO' => $row['origen'],
                    'FECHA_PAGO' => $row['fecha'],
                    'CARGO_ABONO' => $row['ca'],
                    'ID_TRANSACCION' => $row['idtransaccion'],
                    'ID_PAGO_TRANSACCION_PADRE' => $row['transaccion_padre'],
                    'NOMBRE_CANAL_PAGO' => $row['nombre_canal_pago'],
                    'FECHA_PROCESO' => $format

                ];
            }
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
    }

    return $datos2;
    }
    public function GeneraTransaccionesCuentas($duedate)
    {
    $DDate = new DateTime($duedate);

    // Set the time to the start of the day
    $DDate->setTime(0, 0, 0);

    $datos2 = [];
    $strConnString = "pgsql:host=fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com;port=5439;dbname=mambu_prod";
    $user = "marcadodev";
    $password = "marcadoDev00";
    $format = Carbon::createFromFormat('Y-m-d',$duedate)->format('dmY');

    try {
        $dbh = new PDO($strConnString, $user, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $dbh->prepare("SELECT DISTINCT " .
                    "LC.ID AS ACUERDO, " .
                    //"CV3.VALUE AS CICLO, " .
                    "round(GE.amount, 2)  AS MONTO, " .
                    "ST.type as TIPO, " .
                    "CASE " .
                    "WHEN ST.type = 'TRANSFER' THEN 'Transferencia' " .
                    "WHEN ST.type = 'TRANSFER_ADJUSTMENT' THEN 'Ajuste_Transferencia' " .
                    "WHEN ST.type = 'DEPOSIT' THEN 'Deposito' " .
                    "WHEN ST.type = 'ADJUSTMENT' THEN 'Ajuste' " .
                    "WHEN ST.type = 'WITHDRAWAL' THEN 'Retiro' " .
                    "WHEN ST.type = 'WITHDRAWAL_ADJUSTMENT' THEN 'Ajuste_retiro' " .
                    "END AS TIPOTRX, " .
                    "SA.NAME AS ORIGENPAGO, " .
                    "to_char(ST.ENTRYDATE::timestamp at time zone 'Etc/GMT-6', 'yyyymmdd') AS FECHA, " .
                    "ST.TRANSACTIONID AS IDTRANSACCION, " .
                    "GA.GLCODE AS CUENTACONTABLE, " .
                    "GA.NAME AS CANAL, " .
                    "coalesce(CVTP.value,coalesce((CVTPI.value),coalesce(STII.transactionid::text,'N/A'))) as TRANSACCION_PADRE, " .
                    "coalesce(TCP.name, coalesce(TCPI.name,GA.name)) as NOMBRE_CANAL_PAGO " .
                    "from mambu_marcado_bursa.gljournalentry AS GE " .
                    "JOIN mambu_marcado_bursa.glaccount GA ON GA.encodedkey = GE.GLACCOUNT_ENCODEDKEY_OID " .
                    "join mambu_marcado_bursa.savingsaccount SA on GE.ACCOUNTKEY = SA.ENCODEDKEY " .
                    "join mambu_marcado_bursa.savingstransaction ST on ST.transactionid = GE.TRANSACTIONID " .
                    "join mambu_marcado_bursa.lineofcredit LC on LC.CLIENTKEY = SA.ACCOUNTHOLDERKEY " .
                    "join mambu_marcado_bursa.savingsproduct SP ON SP.ENCODEDKEY = SA.PRODUCTTYPEKEY " .
                    "join mambu_marcado_bursa.client CLI ON CLI.ENCODEDKEY = LC.CLIENTKEY " .
                    "join mambu_marcado_bursa.customfieldvalue CV ON CV.PARENTKEY = CLI.ENCODEDKEY " .
                    "LEFT join mambu_marcado_bursa.customfieldvalue CV2 ON CV2.PARENTKEY = LC.ENCODEDKEY AND CV2.CUSTOMFIELDKEY = '8a442e697ddb93e7017dddbcc99720bf' " .
                    "LEFT JOIN mambu_marcado_bursa.customfield cf ON cf.ENCODEDKEY = CV2.CUSTOMFIELDKEY and cf.type IN ('LINE_OF_CREDIT') " .
                    "LEFT join mambu_marcado_bursa.customfieldvalue CV3 ON CV3.PARENTKEY = LC.ENCODEDKEY AND CV3.CUSTOMFIELDKEY = '8a4422367be572b8017be640f98d5075' " .
                    "LEFT JOIN mambu_marcado_bursa.customfield cf2 ON cf.ENCODEDKEY = CV3.CUSTOMFIELDKEY and cf2.type IN ('Ciclo Grupo')  " .
                    "left join mambu_marcado_bursa.customfieldvalue CVTP on CVTP.parentkey = ST.encodedkey " .
                    "left join mambu_marcado_bursa.customfield CFTP on CFTP.encodedkey = CVTP.customfieldkey and CFTP.type ='TRANSACTION_TYPE_INFO' " .
                    "left join mambu_marcado_bursa.savingstransaction STP ON STP.TRANSACTIONID = CVTP.value " .
                    "left join mambu_marcado_bursa.transactiondetails as TDP on TDP.encodedkey = STP.details_encodedkey_oid " .
                    "left join mambu_marcado_bursa.transactionchannel as TCP on TCP.encodedkey = TDP.transactionchannelkey " .
                    "left join mambu_marcado_bursa.savingstransaction STI on STI.transactionid = GE.TRANSACTIONID " .
                    "left join mambu_marcado_bursa.savingstransaction STII on STII.reversaltransactionkey = ST.encodedkey " .
                    "left join mambu_marcado_bursa.transactiondetails as TDPI on TDPI.encodedkey = STI.details_encodedkey_oid " .
                    "left join mambu_marcado_bursa.transactionchannel as TCPI on TCPI.encodedkey = TDPI.transactionchannelkey " .
                    "left join mambu_marcado_bursa.customfieldvalue CVTPI on CVTPI.parentkey = STI.encodedkey " .
                    "left join mambu_marcado_bursa.customfield CFTPI on CFTPI.encodedkey = CVTPI.customfieldkey and CFTPI.type ='TRANSACTION_TYPE_INFO' " .
                    "where SP.encodedkey in ('8a4422367be572b8017be63d16fb4ea9','8a4422367be572b8017be63de4ee4f63') " .
                    "AND (ST.CREATIONDATE::timestamp at time zone 'Etc/GMT-0' >= (SELECT to_timestamp('" . $duedate . " 06:00:00','yyyy-MM-dd HH:MI:SS'))) " .
                    "and SA.migrationeventkey is NULL " .
                    "AND SA.accountstate in ('ACTIVE','ACTIVE_IN_ARREARS','CLOSED','CLOSED_WRITTEN_OFF') " .
                    "and LC.state != 'CLOSED' " .
                    //"AND LC.id IN ('')" .
                    //"AND ST.transactionid in ('') " .
                    //"and ST.type not in ('BRANCH_CHANGED') " .
                    "ORDER BY FECHA asc ");
                    ini_set('max_execution_time',300);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $datos2[] = [
                    'IDENTIFICADOR_CREDITO' => $row['acuerdo'],
                    'MONTO' => $row['monto'],
                    'TIPO_TRANSACCION' => $row['tipotrx'],
                    'ORIGEN_PAGO' => $row['origenpago'],
                    'FECHA_PAGO' => $row['fecha'],
                    'ID_TRANSACCION' => $row['idtransaccion'],
                    'CUENTA_CONTABLE' => $row['cuentacontable'],
                    'CANAL_PAGO' => $row['canal'],
                    'ID_PAGO_TRANSACCION_PADRE' => $row['transaccion_padre'],
                    'NOMBRE_CANAL_PAGO' => $row['nombre_canal_pago'],
                    'FECHA_PROCESO' => $format
                ];
            }
        }
    } catch (PDOException $ex) {
        $error = $ex->getMessage();
    }

    // Call your function to create the file
    // await CrearArchivo("TransaccionesCuentas_" + $duedate.Replace("-", ""), $Datos);

    return $datos2;
    }
    public function amortizacion($duedate)
    {

        try
        {
            $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
            $port = '5439';
            $database = 'mambu_prod';
            $user = 'marcadodev';
            $password = 'marcadoDev00';
            $format = Carbon::createFromFormat('Y-m-d',$duedate)->format('dmY');

            //Busca Clientes Desembolsados Anteriores
            $Anteriores = $this->DesembolsosAnteriores();
            $recorre = json_decode($Anteriores);
            //Busca Clientes Cancelados
            $Cancelados = $this->DesembolsosCancelados();
            $recorreCan = json_decode($Cancelados);
            //Busca Desembolsos Diarios
            $Diario = $this->DesembolsosDiario();
            $recorreDiario = json_decode($Diario);

            $PrimerMerge = array_merge($recorre,$recorreCan);
            $SegundoMerge = array_merge($PrimerMerge,$recorreDiario);

            $Acuerdos = [];
            $Clientes = [];

            for ($i=0;$i<count($SegundoMerge);$i++)
            {
                //echo json_encode($Anteriores[$i]->id) . "\n";
                array_push($Acuerdos,$SegundoMerge[$i]->id);
                array_push($Clientes,$SegundoMerge[$i]->clave_deudor);
            }

            for ($i=0; $i < count($Acuerdos); $i++)
            {

                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                $pdo = new PDO($dsn, $user, $password);
                // Configurar opciones adicionales si es necesario
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                $query = "select distinct ".
                "	LC.id as ACUERDO, ".
                "	RP.duedate::date as EXIGIBILIDAD, ".
                "	(select distinct ".
                "		round(sum(r.principaldue),2) ".
                "		from mambu_marcado_bursa.repayment r ".
                "			join mambu_marcado_bursa.loanaccount l  ".
                "				on l.encodedkey = r.parentaccountkey  ".
                "		where L.lineofcreditkey = min(LC.encodedkey) ".
                "		and r.duedate = RP.duedate ".
                "		limit 1 ".
                "	) as CAPITAL, ".
                "	(select distinct ".
                "		round(sum(r.interestdue),2) ".
                "		from mambu_marcado_bursa.repayment r ".
                "			join mambu_marcado_bursa.loanaccount l  ".
                "				on l.encodedkey = r.parentaccountkey  ".
                "		where L.lineofcreditkey = min(LC.encodedkey) ".
                "		and r.duedate = RP.duedate ".
                "		limit 1 ".
                "	) as INTERES, ".
                "	(select distinct ".
                "		round(sum(r.principaldue + r.interestdue),2) ".
                "		from mambu_marcado_bursa.repayment r ".
                "			join mambu_marcado_bursa.loanaccount l  ".
                "				on l.encodedkey = r.parentaccountkey  ".
                "		where L.lineofcreditkey = min(LC.encodedkey) ".
                "		and r.duedate = RP.duedate ".
                "		limit 1 ".
                "	) as MONTO, ".
                "	RP.duedate::date as FECHA_PAGO, ".
                "	'NO PAGADO' as ESTATUS ".
                "from mambu_marcado_bursa.lineofcredit LC ".
                "	join mambu_marcado_bursa.loanaccount LA ".
                "		on LA.lineofcreditkey = LC.encodedkey  ".
                "	join mambu_marcado_bursa.repayment RP ".
                "		on RP.parentaccountkey = LA.encodedkey ".
                "where ".
                "LC.id in ('".$Acuerdos[$i]."') ".
                "group by LC.id, rp.duedate ".
                "order by LC.ID,RP.duedate asc ".
                ";";
                ini_set('max_execution_time',300);

                $stmt = $pdo->prepare($query);

                $pdo = null;
                $exi = 1 ;

                if ($stmt->execute()) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                        $datos2[] = [
                            'IDENTIFICADOR_CREDITO' => $row['acuerdo'],
                            'EXIGIBILIDAD' => $exi,
                            'FECHA_EXIGIBILIDAD' => $row['exigibilidad'],
                            'ABONO_CAPITAL' => $row['capital'],
                            'INTERES_IVA_INTERES' => $row['interes'],
                            'ABONO_CAP_INT_IVA_INTERES' => $row['monto'],
                            'FECHA_PAGO' => $row['fecha_pago'],
                            'ESTATUS_EXIGIBILIDAD' => $row['estatus'],
                            'FECHA_PROCESO' => $format

                        ];
                        $exi ++;
                    }
                }

            }
            return $datos2;


        }
        catch (\Throwable $th)
        {

        }
    }
    public function generaRAP($duedate)
{
    $r = null;
    $DataFinal = [];
    $DatosGenerales = [];
    $Transacciones = [];
    $Amortizacion = [];
    $Grupos = [];
    $Acuerdos = null;
    $ResLoan = [];
    $data = null;
    $data2 = [];

    $Datos = "";
    $Error = "";

    $DateIn = Carbon::parse($duedate);
    $DateFn = Carbon::parse($duedate);

    // FECHA DIARIO
    $DateIn->setTime(0, 0, 0);
    $DateFn->setTime(23, 59, 59);

    $FechaInicio = $DateIn->format('Y-m-d') . "T00:00:00-06:00";
    $FechaFin = $DateFn->addDay()->format('Y-m-d') . "T05:59:59-06:00";

    try {
        //Busca Clientes Desembolsados Anteriores
        $Anteriores = $this->DesembolsosAnteriores();
        $recorre = json_decode($Anteriores);
        //Busca Clientes Cancelados
        $Cancelados = $this->DesembolsosCancelados();
        $recorreCan = json_decode($Cancelados);
        //Busca Desembolsos Diarios
        $Diario = $this->DesembolsosDiario();
        $recorreDiario = json_decode($Diario);

        $PrimerMerge = array_merge($recorre,$recorreCan);
        $SegundoMerge = array_merge($PrimerMerge,$recorreDiario);

        // ...

        foreach ($SegundoMerge as $countGrupo) {
            do {
                $Acuerdos = $this->consultaCreditArr($countGrupo['creditArrangementKey']);
            } while ($Acuerdos->id == "" || $Acuerdos->id == " " || $Acuerdos->id == null);

            do {
                $Grupos = $this->consultarGrupo($countGrupo['accountHolderKey'], "");
            } while ($Grupos[0]->firstName == "" || $Grupos[0]->firstName == " " || $Grupos[0]->firstName == null);

            $Numgrupo = $Grupos->_IdGrupo_Clients;
            $Ciclo = $Acuerdos->_Datos_Extra_Credit_Arrangements->Ciclo_Grupo_Credit_Arrangements;

            $connection = new mysqli(config('database.connections.mysql.host'), config('database.connections.mysql.username'), config('database.connections.mysql.password'), config('database.connections.mysql.database'));

            if ($connection->connect_error) {
                die("Connection failed: " . $connection->connect_error);
            }

            $sql = "select distinct id_grupo,id_credito_acuerdo, rap, date(fecha_registro) as fecha_registro " .
                   "from ciclo_rap cr where id_grupo = '$Numgrupo' and ciclo = '$Ciclo';";

            $result = $connection->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $Data = [
                        '_grupo' => $row["id_grupo"],
                        '_acuerdo_nuevo' => $Acuerdos->id,
                        '_acuerdo_viejo' => $row["id_credito_acuerdo"],
                        '_ClaveRAP' => $row["rap"],
                        '_fecharegistro' => $row["fecha_registro"]
                    ];

                    $DataFinal[] = $Data;
                    $Datos .= $row["id_grupo"] . "|" . $Acuerdos->id . "|" . $row["id_credito_acuerdo"] . "|" . $row["rap"] . "|" . $row["fecha_registro"] . "\n";
                }
            }

            $connection->close();
        }

        // Método para Crear Archivo .txt
        $this->crearArchivo("Claves_" . str_replace("-", "", $duedate), $Datos);
    } catch (Exception $ex) {
        $Error = $ex->getMessage();
    }

    return $DataFinal;
}

    public function CrearArchivo($filename, $content)
    {
            $file = fopen($filename, "w") or die("Unable to open file!");
            fwrite($file, $content);
            fclose($file);
    }
// APIS MAMBU
    public function DesembolsosAnteriores()
    {

        //PARAMETROS DE BUSQUEDA MAMBU
        $Fecha = date('Y-m-d');
        $timestampFecha = strtotime ( $Fecha );
        $restardia = '-1 day';
        $sumardia = '+0 day';
        $formato = "Y-m-d";
        $Fecha_corte = date($formato,strtotime ( $restardia , $timestampFecha ));
        $FechaFin_corte = date($formato,strtotime ( $sumardia,$timestampFecha ));

        $FecIn = $Fecha_corte.'T00:00:00-06:00';
        $FecFn = $FechaFin_corte.'T05:59:59-06:00';
        $Arg = 'creationDate';
        $status = 'ACTIVE", "ACTIVE_IN_ARREARS';

        $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
        $port = '5439';
        $database = 'mambu_prod';
        $user = 'marcadodev';
        $password = 'marcadoDev00';
        $resultado = [];
        $Desembolsos = $this->ConsultaLoans($FecIn,$FecFn,$Arg, $status);
        $arregloDesembolsos = json_decode($Desembolsos);
        $Acuerdos = [];

        for ($i=0;$i<count($arregloDesembolsos);$i++)
        {

            if ( strtotime($arregloDesembolsos[$i]->disbursementDetails->disbursementDate) < strtotime($arregloDesembolsos[$i]->creationDate))
            {
                array_push($Acuerdos,$arregloDesembolsos[$i]->creditArrangementKey);
            }
        }

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $user, $password);
        // Configurar opciones adicionales si es necesario
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ejemplo de consulta
        $query1 = "SELECT distinct " .
            "LC.id,coalesce (C_PR4.value,'S/F') as clave_deudor ".
            "from mambu_marcado_bursa.lineofcredit LC " .
                "left join mambu_marcado_bursa.customfield CPR4 ".
                    "on CPR4.id = 'IdGrupo_Clients' ".
                "left join mambu_marcado_bursa.customfieldvalue C_PR4 ".
                    "on C_PR4.customfieldkey = CPR4.encodedkey and C_PR4.parentkey = LC.clientkey ".
            "where LC.encodedkey in ('". implode("','",$Acuerdos) ."') ;"
        ;

        $statement = $pdo->query($query1);
        $statement->execute();

        $data = array();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC))
        {
            $data[] = $row;
        }
        $resultado =  $data;

        return json_encode($resultado);
    }

    public function DesembolsosCancelados()
    {
        //PARAMETROS DE BUSQUEDA MAMBU
        $Fecha = date('Y-m-d');
        $timestampFecha = strtotime ( $Fecha );
        $restardia = '-1 day';
        $sumardia = '+0 day';
        $formato = "Y-m-d";
        $Fecha_corte = date($formato,strtotime ( $restardia , $timestampFecha ));
        $FechaFin_corte = date($formato,strtotime ( $sumardia,$timestampFecha ));

        $FecIn = $Fecha_corte.'T00:00:00-06:00';
        $FecFn = $FechaFin_corte.'T05:59:59-06:00';
        $Arg = 'closedDate';
        $status = 'CLOSED';

        $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
        $port = '5439';
        $database = 'mambu_prod';
        $user = 'marcadodev';
        $password = 'marcadoDev00';
        $resultado = [];
        $Desembolsos = $this->ConsultaLoans($FecIn,$FecFn,$Arg,$status);

        $arregloDesembolsos = json_decode($Desembolsos);

        $Acuerdos = [];

        for ($i=0;$i<count($arregloDesembolsos);$i++)
        {

            if ( $arregloDesembolsos[$i]->accountSubState != 'REPAID')
            {
                array_push($Acuerdos,$arregloDesembolsos[$i]->creditArrangementKey);
            }
        }

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $user, $password);
        // Configurar opciones adicionales si es necesario
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ejemplo de consulta
        $query1 = "SELECT distinct " .
            "LC.id,coalesce (C_PR4.value,'S/F') as clave_deudor ".
            "from mambu_marcado_bursa.lineofcredit LC " .
                "left join mambu_marcado_bursa.customfield CPR4 ".
                    "on CPR4.id = 'IdGrupo_Clients' ".
                "left join mambu_marcado_bursa.customfieldvalue C_PR4 ".
                    "on C_PR4.customfieldkey = CPR4.encodedkey and C_PR4.parentkey = LC.clientkey ".
            "where LC.encodedkey in ('". implode("','",$Acuerdos) ."') ;"
        ;

        $statement = $pdo->query($query1);
        $statement->execute();

        $data = array();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC))
        {
            $data[] = $row;
        }
        $resultado =  $data;

        return json_encode($resultado);
    }
    public function DesembolsosDiario()
    {
        //PARAMETROS DE BUSQUEDA MAMBU
        $Fecha = date('Y-m-d');
        $timestampFecha = strtotime ( $Fecha );
        $restardia = '-1 day';
        $sumardia = '+0 day';
        $formato = "Y-m-d";
        $Fecha_corte = date($formato,strtotime ( $restardia , $timestampFecha ));
        $FechaFin_corte = date($formato,strtotime ( $sumardia,$timestampFecha ));

        $FecIn = $Fecha_corte.'T00:00:00-06:00';
        $FecFn = $FechaFin_corte.'T05:59:59-06:00';
        $Arg = 'disbursementDetails.disbursementDate';
        $status = 'ACTIVE", "ACTIVE_IN_ARREARS';

        $host = 'fcontigo-rs-cluster-01.cdxtyqbdsp7d.us-east-1.redshift.amazonaws.com';
        $port = '5439';
        $database = 'mambu_prod';
        $user = 'marcadodev';
        $password = 'marcadoDev00';
        $resultado = [];
        $Desembolsos = $this->ConsultaLoans($FecIn,$FecFn,$Arg,$status);

        $arregloDesembolsos = json_decode($Desembolsos);

        $Acuerdos = [];

        for ($i=0;$i<count($arregloDesembolsos);$i++)
        {

            if ( $arregloDesembolsos[$i]->creditArrangementKey != '')
            {
                array_push($Acuerdos,$arregloDesembolsos[$i]->creditArrangementKey);
            }
        }

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $user, $password);
        // Configurar opciones adicionales si es necesario
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ejemplo de consulta
        $query1 = "SELECT distinct " .
            "LC.id,coalesce (C_PR4.value,'S/F') as clave_deudor ".
            "from mambu_marcado_bursa.lineofcredit LC " .
                "left join mambu_marcado_bursa.customfield CPR4 ".
                    "on CPR4.id = 'IdGrupo_Clients' ".
                "left join mambu_marcado_bursa.customfieldvalue C_PR4 ".
                    "on C_PR4.customfieldkey = CPR4.encodedkey and C_PR4.parentkey = LC.clientkey ".
            "where LC.encodedkey in ('". implode("','",$Acuerdos) ."') ;"
        ;

        $statement = $pdo->query($query1);
        $statement->execute();

        $data = array();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC))
        {
            $data[] = $row;
        }
        $resultado =  $data;

        return json_encode($resultado);
    }
    //APIS DE MAMBU
    public function ConsultaLoans($FechaIn, $FechaFn, $Filter,$Status)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fcontigo.mambu.com/api/loans:search/?detailsLevel=FULL&limit=1000&offset=0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "filterCriteria": [{
                    "field": "'.$Filter.'",
                    "operator": "BETWEEN",
                    "value": "'.$FechaIn.'",
                    "secondValue": "'.$FechaFn.'",
                    "values": null
                }, {
                    "field": "productTypeKey",
                    "operator": "IN",
                    "values": ["8a445c7c7e352b74017e35fa9f696648", "8a445c7c7e352b74017e35fddcad6856", "8a445c7c7e352b74017e35fef4c7692a", "8a445c7c7e352b74017e35f9209365b3"]
                }, {
                    "field": "accountState",
                    "operator": "IN",
                    "values": ["'.$Status.'"]
                }],
                "sortingCriteria": {
                    "field": "encodedKey",
                    "order": "ASC"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/vnd.mambu.v2+json',
                'Content-Type: application/json',
                'Authorization: Basic anRvbGVudGlubzo1NGp0Z2tuaFAk',
                'User-Agent: Client Cert Sample',
                'Cookie: AWSALB=3PGbdDFRdoylEVMQx/+2Kd39wfxm1tN6b+iAD6Sidved8W9uMMkOvh1Rt5XQDQyErc1DZXSdHs8tKLWN81kV10/Y0EDy0rX0q8hM0hWosez6udDzGyzZqhicb1O1; AWSALBCORS=3PGbdDFRdoylEVMQx/+2Kd39wfxm1tN6b+iAD6Sidved8W9uMMkOvh1Rt5XQDQyErc1DZXSdHs8tKLWN81kV10/Y0EDy0rX0q8hM0hWosez6udDzGyzZqhicb1O1'
            ),
        ));

        $response = curl_exec($curl);
        return $response;

    }
// APIS CXC
    public function enviarinformacion($info, $api)
    {
        $token = $this->token();
        $getToken = $token->access_token;
        $headers = Array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$getToken
        );
        try
        {


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://devfntcxcapi.cxc.com.mx:8055/demo/data/'.$api,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($info),
                CURLOPT_HTTPHEADER => $headers
                ));

            $response = curl_exec($curl);
            //echo "despues exec \n";
            curl_close($curl);
            //echo "despues close : ". $response;
        }
        catch (\Throwable $th)
        {
            echo "catch" . $th;
            throw $th;
        }

        // ************************* GUARDAR EN TABLA ****************

        $cadena = json_encode($response);
        $array = "'" . $cadena . "'";
        $fechaActual = date("Y-m-d H:i:s");
        $registro = new logenviosinterfaces;
        $registro->fecha = $fechaActual;
        $registro->resenvio = $array;
        $registro->save();

        // ************************* GUARDAR EN TABLA ****************
        return json_decode($response);
    }
    public function token()
    {
        try
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://teccxc.us.auth0.com/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "client_id": "fBADhkxN2SME98wuF08CKbDfcCglnkik",
                "client_secret": "MiJvwbpxNfwAYtI6exsKj6K7_2SezIijwxhVld9URAqgnELC3y0DSRUjAqUcIX2B",
                "audience": "https://api-contigo.cxc.com.mx/",
                "grant_type": "client_credentials"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: did=s%3Av0%3A3a2222a0-eab1-11ed-b4ed-7f65523b86ab.sD4QJx2XGq20cD3bSakpuiME9ekXHOJgMmIfUK2iZS8; did_compat=s%3Av0%3A3a2222a0-eab1-11ed-b4ed-7f65523b86ab.sD4QJx2XGq20cD3bSakpuiME9ekXHOJgMmIfUK2iZS8'
            ),
            ));

            $response = curl_exec($curl);
            $token = $response;
            curl_close($curl);


        return json_decode($token);

        }
        catch (\Throwable $th) {
            throw $th;
        }
    }
}

