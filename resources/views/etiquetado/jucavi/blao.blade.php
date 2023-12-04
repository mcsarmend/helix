@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')

@stop

@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Etiquetado JUCAVI</h1>
            <h1 class="card-title">Proceso que da de alta o baja créditos que serán asignados a BLAO.</h1>
        </div>
        <div class="card-body">
            <div class="container py-4">
                {{-- <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3">
                        <button class="btn btn-outline-primary w-100" id="preeliminarjucaviblao">Etiquetado preliminar
                        </button>
                    </div>
                </div> --}}

                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="preetiquetadoblaojucavi">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelPreEtiquetado"
                                        accept=".xlsx, .xls">
                                    <label class="custom-file-label-etiquetado-pre" for="excelPreEtiquetado">Seleccionar
                                        archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Enviar pre
                                        etiquetado</button>
                                </div>
                            </div>
                        </form>
                        {{-- <div>
                        <p>En el archivo, la primer columna es para mambu y la segunda columna es para jucavi</p>
                    </div> --}}
                    </div>
                </div>
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="bajablaojucavi">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelBajaBlao" accept=".xlsx, .xls">
                                    <label class="custom-file-label-baja" for="excelBajaBlao">Seleccionar
                                        archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Baja BLAO</button>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
        <!-- Agrega esto en el encabezado o antes de cerrar el cuerpo -->
        <script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>
        <script>
            $(document).ready(function() {
                drawTriangles();
            showUsersSections();

            });

            // Preeliminar

            $('#preeliminarjucaviblao').click(function() {
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
                    url: "preeliminarjucaviblao",
                    method: "GET",
                    dataType: "JSON",
                    data: {},
                    success: function(data) {
                        console.log(data);
                        if ('success' in data) {

                            var workbook = XLSX.utils.book_new();

                            var fechaActual =
                                new Date(); // Crea un objeto Date con la fecha y hora actuales

                            nameWorkbook = 'Etiquetado preeliminar Blao ' +
                                fechaActual;

                            // Convertir JSON1 a una hoja de cálculo
                            var worksheet1 = XLSX.utils.json_to_sheet(
                                data);
                            XLSX.utils.book_append_sheet(workbook, worksheet1,
                                nameWorkbook
                            );
                            // Generar el archivo Excel
                            var excelBuffer = XLSX.write(workbook, {
                                bookType: 'xlsx',
                                type: 'array'
                            });
                            var blob = new Blob([excelBuffer], {
                                type: 'application/octet-stream'
                            });
                            saveAs(blob, nameWorkbook + '.xlsx');
                            // Desbloquea la pantalla después de que se complete la petición


                            Swal.fire(
                                '¡Gracias por esperar!',
                                data["success"],
                                'success'
                            )
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


            // ======================   EMPIEZA enviar preetiquetado  ==================================================

            const form_preetiquetado = document.getElementById('preetiquetadoblaojucavi');
            const fileInput_preetiquetado = document.getElementById('excelPreEtiquetado');
            const fileInputLabel_etiquetado = document.querySelector('.custom-file-label-etiquetado-pre');


            // Actualizar la etiqueta del archivo seleccionado
            fileInput_preetiquetado.addEventListener('change', () => {
                name = fileInput_preetiquetado.files[0]?.name
                if (name.substring(name.length - 3, name.length) == 'xls' || name.substring(name.length - 4, name
                        .length) == 'xlsx') {
                    fileInputLabel_etiquetado.textContent = fileInput_preetiquetado.files[0]?.name ||
                        'Seleccionar archivo';
                } else {

                    fileInput_preetiquetado.value = "";
                    Swal.fire({
                        icon: 'error',
                        title: 'El archivo no es un excel',
                    });
                }

            });

            form_preetiquetado.addEventListener('submit', (e) => {
                e.preventDefault();

                const file = fileInput_preetiquetado.files[0];
                if (file) {
                    fileInput_preetiquetado.addEventListener('change', () => {
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
                            const column1 = jsonData.map(function(row) {
                                return row[0];
                            });
                            Swal.fire({
                                title: '¡Se etiquetará la siguiente cantidad de creditos!',
                                html: 'Cantidad de créditos: <b>' + column1.length + '</b>, ',
                                showDenyButton: true,
                                confirmButtonText: 'Etiquetar',
                                denyButtonText: `No etiquetar`,
                            }).then((result) => {
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
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: "preetiquetadoblaojucavi",
                                        method: "POST",
                                        dataType: "JSON",
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                                .attr('content')
                                        },
                                        data: {
                                            "lstcreditos": column1,
                                        },
                                        success: function(data) {
                                            console.log(data);
                                            if ('success' in data) {

                                                $.unblockUI();


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
                                                text: data["responseJSON"][
                                                    "error"
                                                ],
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
                    });
                } else {
                    $.unblockUI();
                    Swal.fire({
                        icon: 'error',
                        title: 'No se ha seleccionado ningun archivo',

                    });
                }
            });

            // ======================   TERMINA enviar preetiquetado  ==================================================

            // ======================   EMPIEZA  baja  ==================================================

            const form_baja = document.getElementById('bajablaojucavi');
            const fileInput_baja = document.getElementById('excelBajaBlao');
            const fileInputLabel_baja = document.querySelector('.custom-file-label-baja');
            // Actualizar la etiqueta del archivo seleccionado
            fileInput_baja.addEventListener('change', () => {
                name = fileInput_baja.files[0]?.name;
                if (name.substring(name.length - 3, name.length) == 'xls' || name.substring(name.length - 4, name
                        .length) == 'xlsx') {
                    fileInputLabel_baja.textContent = fileInput_baja.files[0]?.name || 'Seleccionar archivo';
                } else {
                    fileInput_baja.value = "";
                    Swal.fire({
                        icon: 'error',
                        title: 'El archivo no es un excel',
                    });

                }
            });

            form_baja.addEventListener('submit', (e) => {
                e.preventDefault();

                const file = fileInput_baja.files[0];
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

                        const column1 = jsonData.map(function(row) {
                            return row[0];
                        });


                        Swal.fire({
                            title: '¡Se etiquetará la siguiente cantidad de creditos!',
                            html: 'Mambu: <b>' + column1.length + '</b>, ',
                            showDenyButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Etiquetar',
                            denyButtonText: `No etiquetar`,

                        }).then((result) => {
                            /* Read more about isConfirmed, isDenied below */
                            if (result.isConfirmed) {

                                $.ajax({
                                    url: "bajablaojucavi",
                                    method: "POST",
                                    dataType: "JSON",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        "mambu": column1
                                    },
                                    success: function(data) {
                                        console.log(data);
                                        if ('success' in data) {
                                            Swal.fire(
                                                '¡Gracias por esperar!',
                                                data["success"],
                                                'success'
                                            )
                                        }
                                    },
                                    error: function(data) {
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
