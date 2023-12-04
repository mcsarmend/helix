@extends('adminlte::page')

@section('title', 'Alta Fideicomisos')

@section('content_header')


@stop
@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Alta Fideicomisos</h1>
            <h1 class="card-title">Proceso que genera nombres de fideicomisos.</h1>
        </div>
        <div class="card-body">
            <form id="crear">
                @csrf
                <div class="row">
                    <div class="col">
                        <label for="name">Nombre Fideicomiso:</label>
                    </div>
                    <div class="col">
                        <input type="text" id="name" required name="name" class="form-control"> <br><br>
                    </div>
                </div>


                <div class="row">
                    <div class="col">
                        <input type="submit" value="Crear" class="btn btn-success">
                    </div>
                </div>


            </form>

            <br>
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
            $('#crear').submit(function(e) {
                e.preventDefault(); // Evitar la recarga de la página

                // Obtener los datos del crear
                var datoscrear = $(this).serialize();

                // Realizar la solicitud AJAX con jQuery
                $.ajax({
                    url: 'accionalta', // Ruta al controlador de Laravel
                    type: 'POST',
                    data: datoscrear, // Enviar los datos del crear
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            '¡Gracias por esperar!',
                            response.message,
                            'success'
                        );
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '¡Gracias por esperar!',
                            "Existe un error: " + xhr,
                            'error'
                        )
                    }
                });
            });
        });
    </script>
@stop
