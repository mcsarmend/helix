@extends('adminlte::page')

@section('title', 'Etiqueado MAMBU BLAO')

@section('content_header')

@stop

@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Etiquetado MAMBU BLAO</h1>
            <h1 class="card-title">Proceso que da de alta o baja créditos que serán asignados a BLAO.</h1>
        </div>
        <div class="card-body">
            <div class="container py-4">
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3">
                        <button class="btn btn-outline-primary w-100" id="pruebaetiquetado">Preetiquetado Candidatos</button>
                    </div>
                </div>
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3">
                        <button class="btn btn-outline-primary w-100" id="preetiquetadoBlaomambu">Preetiquetado
                            BLAO</button>
                    </div>
                </div>
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="bajablao">
                            <div class="input-group mb-3">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Baja BLAO</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="etiquetadoBlao">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelEtiquetado"
                                        accept=".xlsx, .xls">
                                    <label class="custom-file-label-etiquetado" for="excelEtiquetado">Seleccionar
                                        archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Etiquetado
                                        BLAO</button>
                                </div>
                            </div>
                        </form>
                        {{-- <div>
                            <p>En el archivo, la primer columna es para mambu y la segunda columna es para jucavi</p>
                        </div> --}}
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('fondo')
@stop

@section('css')
    <style>
        .custom-file input {
            width: 20%;
        }

        .custom-file label {
            color: #034383;
            text-decoration: underline;
        }

        .section {
            border-bottom: 1px solid #034383;
            padding: 20px;
            align-content: center;
        }

        .center-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }


    </style>

@stop

@section('js')
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/7.0.20220408/Blob.min.js"
        integrity="sha512-uPm9nh4/QF6a7Mz4Srk0lXfN7T+PhKls/NhWUKpXUbu3xeG4bXhtbw2NCye0BRXopnD0x+SBDMOWXOlHAwqgLw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <!-- Agrega esto en el encabezado o antes de cerrar el cuerpo -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function() {
            drawTriangles();
            showUsersSections();

        });
        /* preetiquetadoBlaomambu*/
        $('#preetiquetadoBlaomambu').click(function() {
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
                url: "blao_preetiequetado_mambu",
                method: "GET",
                dataType: "JSON",
                data: {},
                success: function(data) {
                    $.unblockUI();
                    if ('success' in data) {
                        Swal.fire(
                            '¡Gracias por esperar!',
                            data["success"],
                            'success'
                        )
                    }
                },
                error: function(data) {
                    $.unblockUI();
                    Swal.fire({
                        icon: 'error',
                        title: 'Encontramos un error...',
                        text: data["responseJSON"]["error"],
                    });
                }
            });
            // Desbloquea la pantalla después de que se complete la petición
            $.unblockUI();
        });

        $('#pruebaetiquetado').click(function() {
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

            // PRUEBA PREETIQUETADO BLAO MAMBU
            $.ajax({
                url: "pruebaetiquetadoblaomambu",
                method: "get",
                dataType: "JSON",
                success: function(data) {

                    if (data != '[]') {
                        // Crear un nuevo libro de Excel
                        var workbook = XLSX.utils.book_new();

                        // Convertir JSON1 a una hoja de cálculo
                        var worksheet1 = XLSX.utils.json_to_sheet(data);
                        XLSX.utils.book_append_sheet(workbook, worksheet1,
                            'Prueba Preetiquetado Blao Mambu');

                        // Generar el archivo Excel
                        var excelBuffer = XLSX.write(workbook, {
                            bookType: 'xlsx',
                            type: 'array'
                        });
                        var blob = new Blob([excelBuffer], {
                            type: 'application/octet-stream'
                        });
                        saveAs(blob, 'Prueba Preetiquetado Blao Mambu.xlsx');
                        Swal.fire(
                            '¡Gracias por esperar!',
                            data["success"],
                            'success'
                        )
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


                },
                error: function(data) {
                    $.unblockUI();
                    Swal.fire({
                        icon: 'error',
                        title: 'Encontramos un error...',
                        text: data["responseJSON"]["error"],
                    });
                }
            });
        });

        /* BAJA*/

        const form_baja = document.getElementById('bajablao');

        form_baja.addEventListener('submit', (e) => {
            e.preventDefault();
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
                url: "bajablaomambu",
                method: "POST",
                dataType: "JSON",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {},
                success: function(data) {
                    $.unblockUI();
                    if ('success' in data) {
                        Swal.fire(
                            '¡Gracias por esperar!',
                            data["success"],
                            'success'
                        )
                    }
                },
                error: function(data) {
                    $.unblockUI();
                    Swal.fire({
                        icon: 'error',
                        title: 'Encontramos un error...',
                        text: data["responseJSON"]["error"],
                    });
                }
            });
        });



        /* Etiquetado*/

        const form_etiquetado = document.getElementById('etiquetadoBlao');
        const fileInput_etiquetado = document.getElementById('excelEtiquetado');
        const fileInputLabel_etiquetado = document.querySelector('.custom-file-label-etiquetado');
        // Actualizar la etiqueta del archivo seleccionado
        fileInput_etiquetado.addEventListener('change', () => {
            name = fileInput_etiquetado.files[0]?.name;
            if (name.substring(name.length - 3, name.length) == 'xls' || name.substring(name.length - 4, name
                    .length) == 'xlsx') {
                fileInputLabel_etiquetado.textContent = fileInput_etiquetado.files[0]?.name ||
                    'Seleccionar archivo';
            } else {
                fileInput_etiquetado.value = "";
                Swal.fire({
                    icon: 'error',
                    title: 'El archivo no es un excel',
                });

            }
        });

        form_etiquetado.addEventListener('submit', (e) => {
            e.preventDefault();


            const file = fileInput_etiquetado.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {
                        type: 'array'
                    });
                    const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                        header: 1,
                        defval: ''
                    });

                    const mambuColumn = jsonData.map(function(row) {
                        return row[0];
                    });


                    Swal.fire({
                        title: '¡Se etiquetará la siguiente cantidad de creditos!',
                        html: 'Mambu: <b>' + mambuColumn.length + '</b>, ',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Etiquetar',
                        denyButtonText: `No etiquetar`,

                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
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
                                url: "etiquetadoBlaomambu",
                                method: "POST",
                                dataType: "JSON",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    "mambu": mambuColumn
                                },
                                success: function(data) {
                                    $.unblockUI();
                                    if ('success' in data) {
                                        Swal.fire(
                                            '¡Gracias por esperar!',
                                            data["success"],
                                            'success'
                                        )
                                    }
                                },
                                error: function(data) {
                                    $.unblockUI();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Encontramos un error...',
                                        text: data["responseJSON"]["error"],
                                    });
                                }
                            });


                        } else if (result.isDenied) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se ha seleccionado ningun archivo',

                            });
                        }
                    })





                };

                reader.readAsArrayBuffer(file);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se ha seleccionado ningun archivo',

                });
            }
        });
    </script>
@stop
<a href=""></a>
