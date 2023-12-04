@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')

@stop

@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Reporte de Recuperacion de cartera</h1>
            <h1 class="card-title">Proceso que genera Reporte de Recuperación de Cartera de Mambu.</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <label for="start-date">Fecha inicial:</label>
                    <input type="date" id="start-date" name="start-date">
                </div>
                <div class="col">
                    <label for="end-date">Fecha final:</label>
                    <input type="date" id="end-date" name="end-date" max="">
                </div>
            </div>


            <br>
            <br>
            <div class="row">
                <div class="col">
                    <button class="btn btn-primary" id="generarrecuperacioncartera"> Generar Reporte</button>
                </div>
                <div class="col">
                    <button class="btn btn-primary" id="descargarrecuperacioncartera"> Descargar Reporte</button>
                </div>
            </div>

            <br><br>


            <div class="table-responsive">
                <h2>Tabla Prestamo</h2>
                <table id="tablePrestamo" class="display table table-hover" style="">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>IdTransaccion</th>
                            <th>Acuerdo</th>
                            <th>NoTitular</th>
                            <th>NombreTitularCuenta</th>
                            <th>Ciclo</th>
                            <th>Sucursal</th>
                            <th>Monto_Capital</th>
                            <th>Monto_Interes</th>

                            <th>IvaInteres</th>
                            <th>InteresDiferido</th>
                            <th>IvaInteresDiferido</th>

                            <th>TipoTrx</th>
                            <th>Producto1</th>
                            <th>Producto2</th>
                            <th>CA</th>
                            <th>TipoMov</th>
                            <th>IdFondeador</th>
                            <th>Fondeador</th>
                            <th>Transaccion_vinculada</th>
                            <th>ID_CUENTA</th>
                            <th>strIdTransaccionPadre</th>
                            <th>strCanalPagoTransaccionPadre</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
                <h2>Tabla Depósito</h2>
                <table id="tableDeposito" class="display table table-hover" style="">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>IdTransaccion</th>
                            <th>Acuerdo</th>
                            <th>NoTitular</th>
                            <th>NombreTitularCuenta</th>
                            <th>Ciclo</th>
                            <th>Sucursal</th>
                            <th>importe</th>
                            <th>TipoTrx</th>
                            <th>Producto1</th>
                            <th>Producto2</th>
                            <th>CA</th>
                            <th>TipoMov</th>
                            <th>Canal</th>
                            <th>IdFondeador</th>
                            <th>Fondeador</th>
                            <th>Transaccion_vinculada</th>
                            <th>ID_CUENTA</th>
                            <th>transaccionpadre</th>
                            <th>canaltransaccionpadre</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @include('fondo')
@stop

@section('css')
    <style>

    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.25/dataRender/datetime.js"></script>

    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/7.0.20220408/Blob.min.js"
        integrity="sha512-uPm9nh4/QF6a7Mz4Srk0lXfN7T+PhKls/NhWUKpXUbu3xeG4bXhtbw2NCye0BRXopnD0x+SBDMOWXOlHAwqgLw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


    <script>
        $(document).ready(function() {
            establecerFechaMaxima();
            drawTriangles();
            showUsersSections();

        });
        $('#descargarrecuperacioncartera').click(function() {
            startDate = $('#start-date').val();
            endDate = $('#end-date').val();

            if (startDate == "") {
                Swal.fire({
                    title: '¡Ingresa fecha de inicio!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (endDate == "") {
                Swal.fire({
                    title: '¡Ingresa fecha de termino!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            var startDateVal = new Date(startDate);
            var endDateVal = new Date(endDate);

            if (startDateVal > endDateVal) {
                Swal.fire({
                    title: '¡Ingresa parámetros correctos!',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    text: "La fecha inicial debe ser menor a la fecha final"
                });
                return;
            }


            $.blockUI({
                message: 'Cargando...',
                css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    color: '#fff',
                    'border-radius': '5px',
                    fontSize: '18px',
                    fontWeight: 'bold',
                }
            });
            $.ajax({
                url: "reporterecuperacioncartera",
                method: "GET",
                dataType: "JSON",
                data: {
                    startDate: startDate,
                    endDate: endDate
                },

                success: function(data) {
                    if (data != '[]') {
                        dataPrestamo = data.dataPrestamo;
                        dataDeposito = data.dataDeposito;
                        // Crear un nuevo libro de Excel
                        var workbook = XLSX.utils.book_new();

                        // Convertir JSON1 a una hoja de cálculo
                        var worksheet1 = XLSX.utils.json_to_sheet(dataPrestamo);
                        XLSX.utils.book_append_sheet(workbook, worksheet1, 'Prestamo');

                        // Convertir JSON2 a una hoja de cálculo
                        var worksheet2 = XLSX.utils.json_to_sheet(dataDeposito);
                        XLSX.utils.book_append_sheet(workbook, worksheet2, 'Deposito');

                        // Generar el archivo Excel
                        var excelBuffer = XLSX.write(workbook, {
                            bookType: 'xlsx',
                            type: 'array'
                        });
                        var blob = new Blob([excelBuffer], {
                            type: 'application/octet-stream'
                        });
                        saveAs(blob, 'Reporte Recuperacion Cartera' + $('#start-date').val() + '.xlsx');
                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();

                    } else {
                        Swal.fire({
                            title: '¡Sin información!',
                            text: "No se encontraron registros en la fecha indicada",
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                    }
                }
            });


        })

        $('#generarrecuperacioncartera').click(function() {
            startDate = $('#start-date').val();
            endDate = $('#end-date').val();



            if (startDate == "") {
                Swal.fire({
                    title: '¡Ingresa fecha de inicio!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (endDate == "") {
                Swal.fire({
                    title: '¡Ingresa fecha fin!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            var startDateVal = new Date(startDate);
            var endDateVal = new Date(endDate);

            if (startDateVal > endDateVal) {
                Swal.fire({
                    title: '¡Ingresa parámetros correctos!',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    text: "La fecha inicial debe ser menor a la fecha final"
                });
                return;
            }

            var startDateVal = new Date(startDate);
            var endDateVal = new Date(endDate);

            var timeDifference = endDateVal - startDateVal; // Diferencia en milisegundos

            var twoWeeksInMilliseconds = 14 * 24 * 60 * 60 * 1000; // 2 semanas en milisegundos

            if (timeDifference > twoWeeksInMilliseconds) {
                Swal.fire({
                    title: '¡Rango muy amplio!',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    text: 'Por favor elige un rango más pequeño para realizar la consulta'
                });
                return;
            }

            $.blockUI({
                message: 'Cargando...',
                css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    color: '#fff',
                    'border-radius': '5px',
                    fontSize: '18px',
                    fontWeight: 'bold',
                }
            });
            $("#tablePrestamo").dataTable().fnDestroy();
            $("#tableDeposito").dataTable().fnDestroy();
            // Realiza la petición AJAX
            $.ajax({
                url: "reporterecuperacioncartera",
                method: "GET",
                dataType: "JSON",
                data: {
                    startDate: startDate,
                    endDate: endDate
                },

                success: function(data) {
                    if (data != '[]') {
                        dataPrestamo = data.dataPrestamo;
                        dataDeposito = data.dataDeposito;

                        $('#tablePrestamo').DataTable({
                            destroy: true,
                            scrollX: true,
                            scrollCollapse: true,
                            language: {
                                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                                "lengthMenu": "Mostrar los _MENU_ registros",
                                "zeroRecords": "No existe ese registro",
                                "info": "Mostrar página _PAGE_ de _PAGES_",
                                "infoEmpty": "No encontrado",
                                "infoFiltered": "(filtrado de _MAX_ registros en total)",
                                "sSearch": "Buscar:",
                                "sEmptyTable": "No se encontraron registros",
                                "sLoadingRecords": "Cargando...",
                                "oPaginate": {
                                    "sFirst": "Primero",
                                    "sLast": "Último",
                                    "sNext": "Siguiente",
                                    "sPrevious": "Anterior"
                                }
                            },
                            destroy: true,
                            processing: true,
                            sort: true,
                            paging: true,
                            data: dataPrestamo,
                            columns: [{
                                    title: "Fecha",
                                    data: "strFecha"
                                },
                                {
                                    title: "IdTransaccion",
                                    data: "strIdTransaccion"
                                },
                                {
                                    title: "Acuerdo",
                                    data: "strAcuerdo"
                                },
                                {
                                    title: "NoTitular",
                                    data: "strNoTitular"
                                },
                                {
                                    title: "NombreTitularCuenta",
                                    data: "strNombreTitularCuenta"
                                },
                                {
                                    title: "Ciclo",
                                    data: "strCiclo"
                                },
                                {
                                    title: "Sucursal",
                                    data: "strSucursal"
                                },
                                {
                                    title: "Monto_Capital",
                                    data: "montoCapital"
                                },
                                {
                                    title: "Monto_Interes",
                                    data: "montoInteres"
                                },
                                {
                                    title: "IvaInteres",
                                    data: "ivaInteres"
                                },
                                {
                                    title: "InteresDiferido",
                                    data: "interesDiferido"
                                },
                                {
                                    title: "IvaInteresDiferido",
                                    data: "ivaInteresDiferido"
                                },

                                {
                                    title: "TipoTrx",
                                    data: "strTipoTrx"
                                },
                                {
                                    title: "Producto1",
                                    data: "strProducto1"
                                },
                                {
                                    title: "Producto2",
                                    data: "strProducto2"
                                },
                                {
                                    title: "CA",
                                    data: "strCA"
                                },
                                {
                                    title: "TipoMov",
                                    data: "strTipoMov"
                                },
                                {
                                    title: "IdFondeador",
                                    data: "strIdFondeador"
                                },
                                {
                                    title: "Fondeador",
                                    data: "strFondeador"
                                },
                                {
                                    title: "Transaccion_vinculada",
                                    data: "strTransaccion_vinculada"
                                },
                                {
                                    title: "ID_CUENTA",
                                    data: "strIdCuenta"
                                },
                                {
                                    title: "strIdTransaccionPadre",
                                    data: "strIdTransaccionPadre"
                                },
                                {
                                    title: "strCanalPagoTransaccionPadre",
                                    data: "strCanalPagoTransaccionPadre"
                                },
                            ]
                        });
                        $('#tableDeposito').DataTable({
                            destroy: true,
                            scrollX: true,
                            scrollCollapse: true,
                            language: {
                                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                                "lengthMenu": "Mostrar los _MENU_ registros",
                                "zeroRecords": "No existe ese registro",
                                "info": "Mostrar página _PAGE_ de _PAGES_",
                                "infoEmpty": "No encontrado",
                                "infoFiltered": "(filtrado de _MAX_ registros en total)",
                                "sSearch": "Buscar:",
                                "sEmptyTable": "No se encontraron registros",
                                "sLoadingRecords": "Cargando...",
                                "oPaginate": {
                                    "sFirst": "Primero",
                                    "sLast": "Último",
                                    "sNext": "Siguiente",
                                    "sPrevious": "Anterior"
                                }
                            },
                            destroy: true,
                            processing: true,
                            sort: true,
                            paging: true,
                            data: dataDeposito,
                            columns: [{
                                    title: "Fecha",
                                    data: "fecha"
                                },
                                {
                                    title: "IdTransaccion",
                                    data: "idtransaccion"
                                },
                                {
                                    title: "Acuerdo",
                                    data: "acuerdo"
                                },
                                {
                                    title: "NoTitular",
                                    data: "notitularcuenta"
                                },
                                {
                                    title: "NombreTitularCuenta",
                                    data: "nombretitularcuenta"
                                },
                                {
                                    title: "Ciclo",
                                    data: "ciclo"
                                },
                                {
                                    title: "Sucursal",
                                    data: "sucursal"
                                },
                                {
                                    title: "importe",
                                    data: "importe"
                                },
                                {
                                    title: "TipoTrx",
                                    data: "tipotrx"
                                },
                                {
                                    title: "Producto1",
                                    data: "producto1"
                                },
                                {
                                    title: "Producto2",
                                    data: "producto2"
                                },
                                {
                                    title: "CA",
                                    data: "ca"
                                },

                                {
                                    title: "TipoMov",
                                    data: "tipomov"
                                },
                                {
                                    title: "Canal",
                                    data: "canal"
                                },
                                {
                                    title: "IdFondeador",
                                    data: "idfondeador"
                                },
                                {
                                    title: "Fondeador",
                                    data: "fondeador"
                                },
                                {
                                    title: "Transaccion_vinculada",
                                    data: "transaccionvinculada"
                                },
                                {
                                    title: "ID_CUENTA",
                                    data: "idcuenta"
                                },
                                {
                                    title: "transaccionpadre",
                                    data: "transaccionpadre"
                                },
                                {
                                    title: "canaltransaccionpadre",
                                    data: "canaltransaccionpadre"
                                },
                            ]
                        });

                        Swal.fire({
                            title: 'Gracias por esperar',
                            text: 'El reporte ha sido generado correctamente',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });

                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                    } else {
                        Swal.fire({
                            title: '¡Sin información!',
                            text: "No se encontraron registros en la fecha indicada",
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                    }




                }
            });

        });





        function establecerFechaMaxima() {
            var fechaActual = new Date();
            var fechaMinima = new Date();
            fechaMinima.setMonth(fechaActual.getMonth() - 3);
            fechaminimastr = fechaMinima.toISOString().split('T')[0];
            var fechaMaxima = fechaActual.toISOString().split('T')[0];

            document.getElementById('start-date').setAttribute('min', fechaminimastr);
            document.getElementById('start-date').setAttribute('min', fechaminimastr);
            document.getElementById('start-date').setAttribute('max', fechaMaxima);
            document.getElementById('start-date').setAttribute('max', fechaMaxima);


            document.getElementById('end-date').setAttribute('min', fechaminimastr);
            document.getElementById('end-date').setAttribute('min', fechaminimastr);
            document.getElementById('end-date').setAttribute('max', fechaMaxima);
            document.getElementById('end-date').setAttribute('max', fechaMaxima);
        }
    </script>
@stop
