@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')

@stop

@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Reporte de Sesion de cartera</h1>
            <h2 class="card-title">Proceso que genera Reporte de Sesión de Cartera.</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <label for="start-date">Fecha de consulta:</label>
                </div>
                <div class="col">
                    <input type="date" id="start-date" name="start-date">
                </div>
                <div class="col">
                    <label>Fecha máxima con información:</label>
                </div>
                <div class="col">
                    <p id="max-date">Fecha máxima con información:</p>
                </div>
                <div class="col">
                    <label>Fecha mínima con información:</label>
                </div>
                <div class="col">
                    <p id="min-date">Fecha mínima con información:</p>
                </div>

            </div>


            <br>
            <button class="btn btn-primary" id="reportesesioncartera"> Generar Reporte</button>
            <br>
            <br>
            <table id="tablasesioncartera" class="display" style="display:none;">
                <thead>
                    <tr>
                        <th>Fecha_Alta</th>
                        <th>Acuerdo</th>
                        <th>NoTitularCuenta</th>
                        <th>NombreTitulaCuenta</th>
                        <th>Ciclo</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th>Fondeador</th>
                        <th>Saldo_Capital</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
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
    <script>
        $(document).ready(function() {

            fechamin = @json($fechamin);
            fechamax = @json($fechamax);

            $('#min-date').text(fechamin[0].min.substr(0, 10))
            $('#max-date').text(fechamax[0].max.substr(0, 10))

            drawTriangles();
            showUsersSections();

        });

        $('#reportesesioncartera').click(function() {
            // Bloquea la pantalla
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
            dateVal = $('#start-date').val().toString();
            if (dateVal == '') {
                Swal.fire({
                    title: '¡Fecha no indicada!',
                    text: "No se seleccionó una fecha para realizar la consulta",
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $.unblockUI();
            } else {
                var validate = Date.parse(dateVal)
                var today = new Date();
                var newDate = new Date();
                newDate.setMonth(today.getMonth() - 13);

                var formattedDate = newDate.toLocaleDateString();

                if (validate < newDate) {
                    Swal.fire({
                        title: '¡Fecha no válida!',
                        text: "Ingresa una fecha mayor a trece meses hacia atrás",
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $.unblockUI();
                }

                $("#result").text("Fecha de hoy menos 13 meses: " + formattedDate);


                $.ajax({
                    url: "reportesesioncartera",
                    method: "GET",
                    dataType: "JSON",
                    data: {
                        date: dateVal
                    },

                    success: function(data) {
                        console.log(data);
                        if (data.length > 0) {
                            // Procesa los datos de la respuesta...
                            $('#tablasesioncartera').show();
                            // Inicializa la tabla DataTables con los datos
                            $('#tablasesioncartera').DataTable({
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
                                dom: 'Blfrtip',
                                buttons: [
                                    'excel'
                                ],
                                destroy: true,
                                processing: true,
                                sort: true,
                                paging: true,
                                lengthMenu: [
                                    [10, 25, 50, -1],
                                    [10, 25, 50, 'All']
                                ], // Personalizar el menú de longitud de visualización

                                // Configurar las opciones de exportación
                                // Para PDF
                                pdf: {
                                    orientation: 'landscape', // Orientación del PDF (landscape o portrait)
                                    pageSize: 'A4', // Tamaño del papel del PDF
                                    exportOptions: {
                                        columns: ':visible' // Exportar solo las columnas visibles
                                    }
                                },
                                // Para Excel
                                excel: {
                                    exportOptions: {
                                        columns: ':visible' // Exportar solo las columnas visibles
                                    }
                                },
                                data: data,
                                columns: [{
                                    title: "Fecha_Alta"
                                }, {
                                    title: "Acuerdo",
                                }, {
                                    title: "NoTitularCuenta",
                                }, {
                                    title: "NombreTitulaCuenta",
                                }, {
                                    title: "Ciclo",
                                }, {
                                    title: "Sucursal",
                                }, {
                                    title: "Estado",
                                }, {
                                    title: "Fondeador",
                                }, {
                                    title: "Saldo_Capital",
                                }]
                            });


                            // Desbloquea la pantalla después de que se complete la petición
                            $.unblockUI();

                            // Muestra un mensaje de éxito
                            Swal.fire({
                                title: 'Todo bien!',
                                text: '¡El reporte se generó correctamente!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });

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
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Maneja los errores de la petición AJAX...

                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();

                        // Muestra un mensaje de error
                        Swal.fire({
                            title: 'Error',
                            text: 'Algo salió mal. Vuelve a intentarlo.' + errorThrown,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                });
            }

        });

        function showUsersSections() {
            var type = @json($type);
            switch (type) {
                case '3':
                    $('a:contains("Cuentas")').hide();
                    $('small:contains("Administrador")').text('Ejecutivo');
                    $('a:contains("Etiquetado")').hide();
                    $('a:contains("Administración de Cartera")').hide();
                    $('a:contains("Administración de Fideicomisos")').hide();
                    $('a:contains("Anexos")').hide();
                    break;
                case '2':
                    $('a:contains("Cuentas")').hide();

                    break;

                default:
                    break;
            }
        }
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


        }
    </script>
@stop
