@extends('adminlte::page')

@section('title', 'Baja Creditos')

@section('content_header')


@stop
@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Baja Creditos</h1>
            <h1 class="card-title">Proceso que da de baja creditos.</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <label for="fideicomiso">Fideicomiso:</label>
                </div>
                <div class="col">
                    <select name="id" id="id_editar" class="form-control">
                        @foreach ($fideicomisos as $fideicomiso)
                            <option value="{{ encrypt($fideicomiso->id) }}">{{ $fideicomiso->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col">
                    <form id="etiquetado">
                        <div class="input-group mb-3">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="excelEtiquetado" accept=".xlsx, .xls">
                                <label class="custom-file-label-etiquetado" for="excelEtiquetado">Seleccionar
                                    archivo...</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col">
                    <br>
                    <div>
                        <p>El archivo debera contener una sola columna sin encabezados</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <input value="Baja" id = "bajaclic"class="btn btn-danger">
                </div>
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

            $('#bajaclic').click(function() {
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

                $.unblockUI();

                Swal.fire({
                    icon: 'success',
                    title: '¡Gracias por esperar!',
                    text: 'SIMULACION DE BAJA EXITOSA'
                });

                // Realiza la petición AJAX
                // $.ajax({
                //     url: "promecap_preetiequetado_jucavi",
                //     method: "GET",
                //     dataType: "JSON",
                //     data: {},
                //     success: function(data) {
                //         console.log(data);
                //         //if ('success' in data) {

                //         var workbook = XLSX.utils.book_new();

                //         var fechaActual =
                //             getdateformatted(); // Crea un objeto Date con la fecha y hora actuales

                //         nameWorkbook = 'Jucavi ' +
                //             fechaActual;

                //         // Convertir JSON1 a una hoja de cálculo
                //         var worksheet1 = XLSX.utils.json_to_sheet(
                //             data);
                //         XLSX.utils.book_append_sheet(workbook, worksheet1,
                //             nameWorkbook
                //         );
                //         // Generar el archivo Excel
                //         var excelBuffer = XLSX.write(workbook, {
                //             bookType: 'xlsx',
                //             type: 'array'
                //         });
                //         var blob = new Blob([excelBuffer], {
                //             type: 'application/octet-stream'
                //         });

                //         saveAs(blob, nameWorkbook + '.xlsx');

                //         // Desbloquea la pantalla después de que se complete la petición
                //         $.unblockUI();
                //         Swal.fire(
                //             '¡Gracias por esperar!',
                //             data["success"],
                //             'success'
                //         )
                //         //}
                //     },
                //     error: function(data) {
                //         // Desbloquea la pantalla después de que se complete la petición
                //         $.unblockUI();
                //         Swal.fire({
                //             icon: 'error',
                //             title: 'Encontramos un error...',
                //             text: data["responseJSON"]["error"],
                //         });
                //     }
                // });
            });
        });
    </script>
@stop
