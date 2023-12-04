@extends('adminlte::page')

@section('title', 'Reportes')

@section('content_header')

@stop


@section('content')
    <br>
    <div class="card">
        <div class="card-body">
            <h2>Reporte Cartera</h2>
            <div class="card-header">
                <h1 class="card-title">Proceso que genera documentos.</h1>
            </div>
            <br><br>
            <div class="row">
                <div class="col">
                    <label for="start-date" class="">Fecha inicial:</label>
                </div>
                <div class="col">
                    <input type="date" id="start-date" name="start-date" class="form-control">
                </div>
            </div>
            <br><br>
            <div class="row justify-content-center">
                <div class="col-4">
                    <button class="btn btn-primary btn-block" id="descargarcartera"> Descargar Reporte</button>
                </div>
            </div>
            <br><br>
        </div>
    </div>
    @include('fondo')
@stop

@section('css')
    <style>

    </style>
@stop

@section('js')
    <script>

        $(document).ready(function() {
            drawTriangles();
            showUsersSections();
        });
        $('#descargarcartera').click(function() {
            startDate = $('#start-date').val();

            if (startDate == "") {
                Swal.fire({
                    title: '¡Ingresa fecha de inicio!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
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
                url: "reportecartera",
                method: "GET",
                dataType: "JSON",
                data: {
                    startDate: startDate,
                },

                success: function(data) {

                    if (data != '[]') {
                        try {
                            // Crear un objeto de libro de Excel
                            var workbook = XLSX.utils.book_new();

                            // Crear una hoja de trabajo y agregar los datos JSON
                            var worksheet = XLSX.utils.json_to_sheet(data);
                            XLSX.utils.book_append_sheet(workbook, worksheet, "Cartera");

                            // Guardar el archivo Excel
                            XLSX.writeFile(workbook, "Reporte Cartera " + startDate + ".xlsx");

                            Swal.fire({
                                title: '¡Gracias por esperar!',
                                text: "Reporte generado correctamente",
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            $.unblockUI();
                        } catch (error) {
                            console.log(error);
                        }


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


    </script>
@stop
