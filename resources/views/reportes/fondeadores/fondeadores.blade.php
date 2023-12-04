@extends('adminlte::page')

@section('title', 'Reporte Fondeadores')

@section('content_header')


@stop
@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Reporte de Pre etiquetado Fondeadores</h1>
            <h1 class="card-title">Proceso que genera reporte de Pre etiquetado.</h1>
        </div>
        <div class="card-body">
            <button class="btn btn-success" id="reporteFondeadores">Generar Reporte</button>
            <br>
            <br>
            <div class="table-responsive">
                <table id="tablaFondeadores" class="display" style="display:none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Encoded Key</th>
                            <th>Cliente</th>
                            <th>Otro Campo</th>
                            <th>Preetiquetado</th>
                            <th>Fecha Preetiquetado</th>
                            <th>ID Fondeador</th>
                            <th>Fecha Etiquetado</th>
                            <th>Status Etiquetado</th>
                            <th>Motivo Rechazo</th>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <style>
        .card {
            width: 112%;
        }


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
            drawTriangles();
            showUsersSections();
        });



        $('#reporteFondeadores').click(function() {
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

            // Realiza la petición AJAX
            $.ajax({
                url: "fondeadoresreport",
                method: "GET",
                dataType: "JSON",
                data: {},
                success: function(data) {
                    // Procesa los datos de la respuesta...
                    $('#tablaFondeadores').show();
                    // Inicializa la tabla DataTables con los datos
                    $('#tablaFondeadores').DataTable({
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
                        data: data,
                        columns: [{
                                title: "ID"
                            },
                            {
                                title: "Encoded Key",
                                width: "35px"
                            },
                            {
                                title: "Cliente"
                            },
                            {
                                title: "Otro Campo"
                            },
                            {
                                title: "Preetiquetado"
                            },
                            {
                                title: "Fecha Preetiquetado"
                            },
                            {
                                title: "ID Fondeador"
                            },
                            {
                                title: "Fecha Etiquetado"
                            },
                            {
                                title: "Status Etiquetado"
                            },
                            {
                                title: "Motivo Rechazo"
                            }
                        ]
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
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Maneja los errores de la petición AJAX...

                    // Desbloquea la pantalla después de que se complete la petición
                    $.unblockUI();

                    // Muestra un mensaje de error
                    Swal.fire({
                        title: 'Error',
                        text: 'Algo salió mal. Vuelve a intentarlo.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
            });
        });
    </script>
@stop
