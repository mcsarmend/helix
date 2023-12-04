@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')
    <h1>Etiquetado MAMBU</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Proceso que da de alta o baja créditos que serán asignados a Mintos.</h1>
        </div>
        <div class="card-body">
            <div class="container py-4">
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3">
                        <button class="btn btn-outline-primary w-100" id="preetiquetadoMintos">Preetiquetado
                            MINTOS</button>
                    </div>
                </div>
                <div class="row section">
                    <div class="col-md-8 col-sm-6 mb-3 center-form">
                        <!-- Agregado: "center-form" -->
                        <form id="etiquetadoMintos">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excelEtiquetado"
                                        accept=".xlsx, .xls">
                                    <label class="custom-file-label-etiquetado" for="excelEtiquetado">Seleccionar
                                        archivo...</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary w-100" type="submit">Etiquetado
                                        MINTOS</button>
                                </div>
                            </div>
                        </form>
                        <div>
                            <p>En el archivo, la primer columna es para el acuerdo de credito y la segunda columna es para
                                la taza</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
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
            var type = @json($type);
            if (type == '3') {
                $('a:contains("Cuentas")').hide();
                $('small:contains("Administrador")').text('Ejecutivo');
            }


            async function patchClients() {
                console.log("Enviando créditos");

                try {
                    let rengarr = [];
                    let keys = [];
                    await wb.xlsx.readFile(filePath);

                    var sh = wb.getWorksheet("Hoja1");

                    for (n = 1; n <= sh.rowCount; n++) {
                        rengarr.push([
                            sh.getRow(n).getCell(1).value.toString(),
                            sh.getRow(n).getCell(2).value.toString()
                        ]);
                        keys.push([sh.getRow(n).getCell(1).value]);
                    };

                    let indx = 0;
                    for (m = 1; m < keys.length; m++) {
                        await sleep(100);
                        indx = m;
                        let Clientpached = await patchClient(rengarr[indx]);

                        /*if(Clientpached)
                        {
                                console.log(`Cuenta ${rengarr[indx][1]} enviada`);
                        }*/
                        if (!Clientpached) {
                            console.error(
                                `ErrorA01 enviando la cuenta: ${rengarr[indx][1]}`
                            );
                        }
                        if (m % 10 == 0) {
                            console.log(`Cuenta ${m} de ${keys.length}`);
                        }

                    };


                    console.log("Pagos enviados");
                } catch (e) {
                    console.log(e);
                }
            }


        });
        // PREETIQUETADO
        $('#preetiquetadoMintos').click(function() {
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
                url: "mintos_preetiquetado",
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

        });


        /* Etiquetado*/

        const form_etiquetado = document.getElementById('etiquetadoMintos');
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

                    var array = [];
                    const column1 = jsonData.map(function(row) {
                        return row[0];
                    });
                    const column2 = jsonData.map(function(row) {
                        return row[1];
                    });

                    Swal.fire({
                        title: '¡Se etiquetará la siguiente cantidad de creditos!',
                        html: 'Mambu: <b>' + column1.length + '</b>, ',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Etiquetar',
                        denyButtonText: `No etiquetar`,
                    }).then((result) => {
                        // Inicia proceso etiquetado
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
                            const baseUrl = "http://52.168.179.158/api/RecepcionPago/" //Prod

                            for (m = 1; m < column1.length; m++) {
                                sleep(100);
                                try {
                                    url = baseUrl + 'RecepcionPago/enviar-cuentas-grupal-mintos/' +
                                        column1[m] + '/' + column2[m];
                                    var settings = {
                                        "url": url,
                                        "method": "GET",
                                        "timeout": 0,
                                    };

                                    $.ajax(settings).done(function(response) {
                                        $.unblockUI();
                                        Swal.fire(
                                            '¡Gracias por esperar!',
                                            'Etiquetado realizado correctamente',
                                            'success'
                                        )
                                    });

                                } catch (error) {
                                    $.unblockUI();
                                    Swal.fire({
                                        icon: 'error',
                                        title: error.toString(),
                                    });
                                }
                            }
                        } else if (result.isDenied) {
                            Swal.fire('No se realiza etiquetado', '', 'info');
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
    </script>
@stop
