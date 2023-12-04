@extends('adminlte::page')

@section('title', 'Etiquetado PROMECAP')

@section('content_header')

@stop

@section('content')
    <br>
    <div class="card">
        <div class="card-header">
            <h1>Etiquetado PROMECAP</h1>
            <h1 class="card-title">Proceso que da de alta o baja créditos que serán asignados a Promecap.</h1>
        </div>
        <div class="card-body">
            <div class="container py-4">
                <div class="row section" style="display: flex; flex-direction: column-reverse;">
                    <div class="col-md-8 col-sm-6 mb-3">
                        <button class="btn btn-outline-primary w-100" id="preetiquetadopromecapmambu">Preetiquetado
                            mambu</button>
                    </div>
                    <h2>PREETIQUETADO</h2>
                </div>

                <div class="row section" style="display: flex; flex-direction: column-reverse;">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="bajapromecap">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelBaja" accept=".xlsx, .xls">
                                    <label class="custom-file-label-baja" for="excelBaja">Seleccionar archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Baja Promecap</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <h2>BAJA</h2>
                </div>

                <div class="row section" style="display: flex; flex-direction: column-reverse;">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="etiquetadoPromecap">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelEtiquetado"
                                        accept=".xlsx, .xls">
                                    <label class="custom-file-label-etiquetado" for="excelEtiquetado">Seleccionar
                                        archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Etiquetado
                                        Promecap</button>
                                </div>
                            </div>
                        </form>
                        <div>
                            <p>El archivo deberá contener una sola columna sin encabezados</p>
                        </div>
                    </div>
                    <h2>ETIQUETADO</h2>
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
        /* preetiquetadopromecapmambu*/
        $('#preetiquetadopromecapmambu').click(function() {
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
                url: "promecap_preetiequetado_mambu",
                method: "GET",
                dataType: "JSON",
                data: {},
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

        /* BAJA*/
        const form_baja = document.getElementById('bajapromecap');
        const fileInput_baja = document.getElementById('excelBaja');
        const fileInputLabel_baja = document.querySelector('.custom-file-label-baja');
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

                    const firstColumn = jsonData.map(function(row) {
                        return row[0];
                    });

                    $.ajax({
                        url: "bajapromecapmambu",
                        method: "POST",
                        dataType: "JSON",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            "baja": firstColumn
                        },
                        success: function(data) {
                            console.log(data);
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
                };

                reader.readAsArrayBuffer(file);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se ha seleccionado ningun archivo',

                });
            }
        });

        // Actualizar la etiqueta del archivo seleccionado


        /* Etiquetado*/

        const form_etiquetado = document.getElementById('etiquetadoPromecap');
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
                        confirmButtonText: 'Etiquetar',
                        denyButtonText: `No etiquetar`,

                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {

                            $.ajax({
                                url: "etiquetadopromecapmambu",
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
                            Swal.fire('No se realiza etiquetado', '', 'info')
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
