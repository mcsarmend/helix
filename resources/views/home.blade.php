@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop

@section('content')
    <br>
    {{-- asdsad --}}
    <div class="card">
        <div class="card-body" style="width: 1200px">


            <div class="section">
                <h3>Aforos</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Aforo</th>
                            <th>Actual Jucavi</th>
                            <th>Actual Mambu</th>
                            <th>Actual Suma</th>
                            <th>Diferencia</th>
                            <th>Cantidad Jucavi</th>
                            <th>Cantidad Mambu</th>
                            <th>Suma cantidad</th>
                            <th>Exportar Creditos</th>


                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Promecap</th>
                            <th id="aforocalpromecap">AFORO CALCULADO PROMECAP</th>
                            <td id="valoractualjucavipromecap">Valor Actual jucavi promecap</td>
                            <td id="valoractualmambupromecap">Valor Actual mambu promecap</td>
                            <td id="valoractualsumapromecap">Valor Actual suma promecap</td>
                            <td id="valordiferenciapromecap">Valor Diferencia Promecap</td>
                            <td id="cantidadactualjucavipromecap">Cantidad actual jucavi Promecap</td>
                            <td id="cantidadactualmambupromecap">Cantidad actual mambu Promecap</td>
                            <td id="sumacantidadactualpromecap">Suma cantidad Promecap</td>
                            <th><button type="button" class="btn btn-primary"
                                    id="exportarcreditospromecap">Exportar</button></th>
                        </tr>
                        <tr>
                            <th>Blao</th>
                            <td id="valoraforoblao">$153,173,700.00</td>
                            <td id="valoractualjucaviblao">Valor Actual jucavi blao</td>
                            <td id="valoractualmambublao">Valor Actual mambu blao</td>
                            <td id="valoractualsumablao">Valor Actual suma blao</td>
                            <td id="valordiferenciablao">Valor Diferencia blao</td>
                            <td id="cantidadactualjucaviblao">Cantidad actual jucavi blao</td>
                            <td id="cantidadactualmambublao">Cantidad actual mambu blao</td>
                            <td id="sumacantidadactualblao">Suma cantidad blao</td>
                            <th>EXPORTAR</th>
                        </tr>
                        <tr>
                            <th>Mintos</th>
                            <td id="valoraforomintos">-</td>
                            <td id="valoractualjucavimintos">-</td>
                            <td id="valoractualmambumintos">Valor Actual mambu mintos</td>
                            <td id="valoractualsumamintos">Valor Actual suma mintos</td>
                            <td id="valordiferenciamintos">-</td>
                            <td id="cantidadactualjucavimintos">-</td>
                            <td id="cantidadactualmambumintos">Cantidad actual mambu mintos</td>
                            <td id="sumacantidadactualmintos">Suma cantidad mintos</td>
                            <th>EXPORTAR</th>
                        </tr>
                    </tbody>
                </table>


            </div>
            <br>

            <div class="section">
                <h2> Gráficas</h2>
                <br>
                <figure class="highcharts-figure">
                    <h2>JUCAVI</h2>
                    <div id="container-jucavi"></div>
                    <p class="highcharts-description">
                        En esta gráfica se encuentra la cantidad de elementos de un fondeador jucavi
                    </p>
                </figure>
                <br>
                <figure class="highcharts-figure">
                    <h2>MAMBU</h2>
                    <div id="container-mambu"></div>
                    <p class="highcharts-description">
                        En esta gráfica se encuentra la cantidad de elementos de un fondeador mambu
                    </p>
                </figure>
            </div>
            <br>
            <br>


        </div>

    </div>



    @include('fondo')
@stop

@section('css')
    <style>

        .section {
            border-bottom: 1px solid #034383;
            padding: 20px;
            align-content: center;
        }

        .highcharts-figure,
        .highcharts-data-table table {
            min-width: 320px;
            max-width: 800px;
            margin: 1em auto;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .highcharts-data-table td,
        .highcharts-data-table th,
        .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr,
        .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }

        input[type="number"] {
            min-width: 50px;
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            /* Por defecto, ocultar el modal */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            /* Fondo semi-transparente */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
        }

        /* Estilos para el botón de cerrar el modal */
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .modalAnexo {
            font-family: Arial;
            font-size: 18.5px
        }

        .cent {
            text-align: center;
            z-index: 0;
            left: 0px;
            top: 0px
        }

        .derecha {
            text-align: right;
        }

        .justif {
            text-align-last: justify;
        }

        .container {
            max-width: 1200px !important;
        }
    </style>

@stop

@section('js')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    {{-- LIBRERIAS DATATABLE --}}
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.25/dataRender/datetime.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">

    </script>
    {{-- LIBRERIAS HIGHCHARTS --}}
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    {{-- LIBRERIAS PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.debug.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.min.js"
        integrity="sha512-w3u9q/DeneCSwUDjhiMNibTRh/1i/gScBVp2imNVAMCt6cUHIw6xzhzcPFIaL3Q1EbI2l+nu17q2aLJJLo4ZYg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
        integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- LIBRERIA EXCEL --}}

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.0/FileSaver.min.js"
        integrity="sha512-csNcFYJniKjJxRWRV1R7fvnXrycHP6qDR21mgz1ZP55xY5d+aHLfo9/FcGDQLfn2IfngbAHd8LdfsagcCqgTcQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        var jsonjucavi = [];
        var jsonmambu = [];
        $(document).ready(function() {

            const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre",
                "octubre", "noviembre", "diciembre"
            ];
            const fecha = new Date();
            const dia = fecha.getDate() + 1;
            const mes = meses[fecha.getMonth()];
            const año = fecha.getFullYear();
            const fechaFormateada = `${dia} ${mes} ${año}`;

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-dateh').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-dateh2').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-dateh3').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-datem').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-datem2').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-datem3').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-datem4').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-dateo').text(fechaFormateada);

            ////startDate = $('#start-date').val();
            //dayOfToday = new Date();
            //dayOfToday.setDate(dayOfToday.getDate()+1);
            //$('#start-dateo2').text(fechaFormateada);

            //startDate = $('#start-date').val();
            dayOfToday = new Date();
            dayOfToday.setDate(dayOfToday.getDate() + 1);
            $('#start-datehinst').text(fechaFormateada);

            ////startDate = $('#start-date').val();
            //dayOfToday = new Date();
            //dayOfToday.setDate(dayOfToday.getDate()+1);
            //$('#start-datehinst2').text(fechaFormateada);

            ////startDate = $('#start-date').val();
            //dayOfToday = new Date();
            //dayOfToday.setDate(dayOfToday.getDate()+1);
            //$('#start-datehinst3').text(fechaFormateada);

            // JavaScript para manejar el modal
            $('#anexoHPromecapAbrir').on('shown.bs.modal', function() {
                $('#anexoHPromecapModal').trigger('focus')
            })

            $.ajax({
                url: "obtenerFechaCorte",
                method: "GET",
                dataType: "JSON",
                data: {},
                success: function(data) {
                    const meses2 = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio",
                        "agosto", "septiembre", "octubre", "noviembre", "diciembre"
                    ];
                    const fecha2 = new Date(data.fecha + 'T06:00:00Z');
                    const dia2 = fecha2.getDate();
                    const mes2 = meses[fecha2.getMonth()];
                    const año2 = fecha2.getFullYear();
                    const fechaFormateada2 = `${dia2} ${mes2} ${año2}`;
                    $('#start-datem').text(fechaFormateada2);
                    $('#start-dateo2').text(fechaFormateada2);
                    $('#start-datehinst2').text(fechaFormateada2)
                    $('#start-datehinst3').text(fechaFormateada2)
                },
                error: function(data) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Encontramos un error...',
                        text: data["responseJSON"]["error"],
                    });
                }
            });

            var type = @json($type);
            if (type == '3') {
                $('a:contains("Cuentas")').hide();
                $('small:contains("Administrador")').text('Ejecutivo');
            }


            //    generarGraficasJucaviour();
            montoaseguradomambu = 0;

            montoaseguradojucavi = 0;

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

            calculofondeadorpromecap();

            calculosohmambu();

            calculosohjucavi();

            historicoaforopromecap();



            $('#exportarcreditospromecap').click(function() {
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
                    url: "get_creditos_promecap_aforo",
                    method: "GET",
                    dataType: "JSON",
                    data: {},
                    success: function(data) {

                        var workbook = XLSX.utils.book_new();

                        var fechaActual =
                            getdateformatted(); // Crea un objeto Date con la fecha y hora actuales

                        nameWorkbook = 'PROMECAP ' +
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
                        $.unblockUI();
                        Swal.fire(
                            '¡Gracias por esperar!',
                            data["success"],
                            'success'
                        )
                        //}
                    },
                    error: function(data) {
                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                        Swal.fire({
                            icon: 'error',
                            title: 'Encontramos un error...',
                            text: data["responseJSON"]["error"],
                        });
                    }
                });
            });

            $('#exportarcreditospromecapanexoa').click(function() {
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
                    url: "get_creditos_promecap_anexo_a",
                    method: "GET",
                    dataType: "JSON",
                    data: {},
                    success: function(data) {
                        $("#anexoamodal").modal("show");

                        $("#tablaanexoapromecap").dataTable().fnDestroy();

                        var table = $('#tablaanexoapromecap').DataTable({
                            destroy: true,
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
                            dom: 'Bfrtip',
                            buttons: [{
                                    extend: 'excelHtml5',
                                    text: 'Exportar a Excel',
                                    filename: 'Anexo A Promecap',
                                    className: 'btn-primary' // Cambia el color del botón a azul primario
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: 'Exportar a PDF',
                                    className: 'btn-primary', // Cambia el color del botón a azul primario
                                    // orientation: 'landscape',
                                    filename: 'Anexo A Promecap',
                                    title: '',
                                    customize: function(doc) {
                                        // Configura el ancho de la tabla
                                        // doc.content[1].table.widths = Array(table.columns()[0].length + 1).join('*').split('');

                                        // Configura el tamaño de la página
                                        doc.pageOrientation =
                                        'landscape'; // Puedes usar 'portrait' o 'landscape'
                                        doc.pageMargins = [5, 10, 10,
                                        10]; // Márgenes [izquierda, arriba, derecha, abajo]
                                    }
                                }
                                // Agrega más opciones de exportación si es necesario
                            ],
                            processing: true,
                            sort: true,
                            paging: true,
                            autoWidth: true,
                            data: data,
                            columns: [{
                                    data: 'credito'
                                },
                                {
                                    data: 'nombre grupo'
                                },
                                {
                                    data: 'monto otorgado'
                                },
                                {
                                    data: 'fecha desembolso'
                                },
                                {
                                    data: 'fecha vencimiento'
                                },
                                {
                                    data: 'saldo capital'
                                },
                                {
                                    data: 'saldo interes'
                                },
                                {
                                    data: 'saldo interes iva'
                                },
                                {
                                    data: 'cuotas restantes'
                                },
                                {
                                    data: 'periodicidad'
                                },
                                {
                                    data: 'sucursal'
                                },
                            ]
                        });


                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                        Swal.fire(
                            '¡Gracias por esperar!',
                            data["success"],
                            'success'
                        )

                    },
                    error: function(data) {
                        // Desbloquea la pantalla después de que se complete la petición
                        $.unblockUI();
                        Swal.fire({
                            icon: 'error',
                            title: 'Encontramos un error...',
                            text: data["responseJSON"]["error"],
                        });
                    }
                });
            });


            drawTriangles();
            showUsersSections();
        });


        function calculosohjucavi() {
            $.ajax({
                url: "testsohrep",
                method: "GET",
                dataType: "JSON",
                data: {
                    /* date: dateVal */
                },

                success: function(fondeadoresjucavi) {
                    jsonjucavi = fondeadoresjucavi;
                    tmpfondeadoresjucavi = [];
                    total = 0;
                    fondeadoresjucavi.forEach(element => {
                        var numero = parseFloat(element.monto);
                        total += numero;
                    });
                    totalFormateado = total.toLocaleString('en-US');
                    fondeadoresjucavi.forEach(element => {
                        porcentaje = (element.monto * 100) / total;
                        porcentajeFormateado = parseFloat(porcentaje.toFixed(2));
                        cantidadFormateado = parseFloat(element.cantidadregistros);
                        montoFormateado = parseFloat(element.monto);
                        montoFormateado = montoFormateado.toLocaleString('en-US');

                        tmpfondeadoresjucavi.push({
                            name: element.nombrefondeador,
                            y: porcentajeFormateado,
                            cantidad: cantidadFormateado,
                            monto: montoFormateado
                        });

                    });
                    series = [];
                    series.push({
                        name: 'Fondeador',
                        colorByPoint: true,
                        data: tmpfondeadoresjucavi
                    });
                    graficaPastel(series, 'container-jucavi');
                    datosgenerales();
                }
            });

        }

        function calculosohmambu() {
            $.ajax({
                url: "sohmambu",
                method: "GET",
                dataType: "JSON",
                data: {
                    /* date: dateVal */
                },
                success: function(fondeadoresmambu) {
                    jsonmambu = fondeadoresmambu;
                    tmpfondeadoresmambu = [];
                    total = 0;
                    fondeadoresmambu.forEach(element => {
                        var numero = parseFloat(element.monto);
                        total += numero;
                    });
                    totalFormateado = total.toLocaleString('en-US');
                    fondeadoresmambu.forEach(element => {
                        porcentaje = (element.monto * 100) / total;
                        porcentajeFormateado = parseFloat(porcentaje.toFixed(2));
                        cantidadFormateado = parseFloat(element.cantidadregistros);
                        montoFormateado = parseFloat(element.monto);
                        montoFormateado = montoFormateado.toLocaleString('en-US');

                        tmpfondeadoresmambu.push({
                            name: element.nombrefondeador,
                            y: porcentajeFormateado,
                            cantidad: cantidadFormateado,
                            monto: montoFormateado
                        });

                    });
                    series = [];
                    series.push({
                        name: 'Fondeador',
                        colorByPoint: true,
                        data: tmpfondeadoresmambu
                    });
                    graficaPastel(series, 'container-mambu');

                }
            });

        }


        function datosgenerales() {
            // Actuales
            actualjucavipromecap = 0;
            actualjucaviblao = 0;


            actualmambupromecap = 0;
            actualmambublao = 0;
            actualmambumintos = 0;

            // Cantidades
            cantidadjucavipromecap = 0;
            cantidadjucaviblao = 0;

            cantidadmambupromecap = 0;
            cantidadmambublao = 0;
            cantidadmambumintos = 0;

            jsonjucavi.forEach(element => {
                switch (element.nombrefondeador) {
                    case "Promecap":
                        $('#valoractualjucavipromecap').text("$" + parseFloat(element.monto).toLocaleString(
                            'en-US'));
                        actualjucavipromecap += parseFloat(element.monto);
                        $('#cantidadactualjucavipromecap').text(parseFloat(element.cantidadregistros)
                            .toLocaleString('en-US'));
                        cantidadjucavipromecap = element.cantidadregistros;
                        break;
                    case "BLAO":
                        $('#valoractualjucaviblao').text("$" + parseFloat(element.monto).toLocaleString('en-US'));
                        actualjucaviblao += parseFloat(element.monto);
                        $('#cantidadactualjucaviblao').text(parseFloat(element.cantidadregistros).toLocaleString(
                            'en-US'));
                        cantidadjucaviblao = element.cantidadregistros;
                        break;
                }
            });
            jsonmambu.forEach(element => {
                switch (element.nombrefondeador) {
                    case "PROMECAP":
                        $('#valoractualmambupromecap').text("$" + parseFloat(element.monto).toLocaleString(
                            'en-US'));
                        actualmambupromecap += parseFloat(element.monto);
                        $('#cantidadactualmambupromecap').text(parseFloat(element.cantidadregistros)
                            .toLocaleString('en-US'));
                        cantidadmambupromecap = element.cantidadregistros;
                        break;
                    case "BLAO":
                        $('#valoractualmambublao').text("$" + parseFloat(element.monto).toLocaleString('en-US'));
                        actualmambublao += parseFloat(element.monto);
                        $('#cantidadactualmambublao').text(parseFloat(element.cantidadregistros).toLocaleString(
                            'en-US'));
                        cantidadmambublao = element.cantidadregistros;
                        break;
                    case "MINTOS":
                        $('#valoractualmambumintos').text("$" + parseFloat(element.monto).toLocaleString('en-US'));
                        actualmambumintos += parseFloat(element.monto);
                        $('#cantidadactualmambumintos').text(parseFloat(element.cantidadregistros)
                            .toLocaleString('en-US'));
                        cantidadmambumintos = element.cantidadregistros;
                        $('#sumacantidadactualmintos').text(parseFloat(element.cantidadregistros).toLocaleString(
                            'en-US'));

                        break;
                }
            });

            sumavalorpromecap = actualjucavipromecap + actualmambupromecap;
            sumavalorblao = actualjucaviblao + actualmambublao;

            sumacantidadpromecap = cantidadjucavipromecap + cantidadmambupromecap;
            sumacantidadblao = cantidadjucaviblao + cantidadmambublao;



            $('#valoractualsumapromecap').text("$" + parseFloat(sumavalorpromecap).toLocaleString('en-US'));



            $('#valoractualsumablao').text("$" + parseFloat(sumavalorblao).toLocaleString('en-US'));
            $('#valoractualsumamintos').text("$" + parseFloat(actualmambumintos).toLocaleString('en-US'));

            $('#sumacantidadactualpromecap').text(parseFloat(sumacantidadpromecap).toLocaleString('en-US'));
            $('#sumacantidadactualblao').text(parseFloat(sumacantidadblao).toLocaleString('en-US'));


            vdb = 153173700.00 - sumavalorblao;

            var valorOriginal = $('#aforocalpromecap').text();

            // Eliminar el signo de dólar y las comas
            var valorLimpio = valorOriginal.replace(/\$|,/g, '');

            // Convertir la cadena en un número de punto flotante
            aforopromecap = parseFloat(valorLimpio);



            vdp = aforopromecap - sumavalorpromecap;
            $('#valordiferenciapromecap').text("$" + parseFloat(vdp).toLocaleString('en-US'));
            $('#valordiferenciablao').text("$" + parseFloat(vdb).toLocaleString('en-US'));


        }

        function graficaPastel(series, idcontainer) {

            // Data retrieved from https://netmarketshare.com
            Highcharts.chart(idcontainer, {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Creditos por fondeador del día anterior',
                    align: 'left'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    series: {
                        borderRadius: 5,
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y:.1f}%, Monto: {point.monto:.1f}'
                        }
                    }
                },

                tooltip: {
                    headerFormat: '<span style="font-size:11px">Porcentaje:  <b>{point.percentage:.1f}%</b></span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.cantidad}</b><br/>'
                },
                series: series
            });


        }

        function generarGraficasJucavi() {
            fondeadoresjucavi = [];
            // Bloquea la pantalla

            $("#soh").dataTable().fnDestroy();
            // Realiza la petición AJAX
            $.ajax({
                url: "testsohrep",
                method: "GET",
                dataType: "JSON",
                data: {
                    /* date: dateVal */
                },

                success: function(data) {
                    var encontrado = false;
                    var valu = "";
                    data.forEach(element => {
                        var longitud = Object.keys(element).length;
                        for (var key in element) {
                            if (element[key] == null)
                                element[key] = "-";
                            if (key == "NombreFondeador") {
                                encontrado = false;
                                valu = element[key];
                                // Recorrer cada objeto en el arreglo
                                for (var i = 0; i < fondeadoresjucavi.length; i++) {
                                    if (fondeadoresjucavi[i].NombreFondeador === valu) {
                                        encontrado = true;
                                        break;
                                    }
                                }
                                if (encontrado == false) {
                                    json = {
                                        NombreFondeador: valu,
                                        cantidad: 1
                                    }
                                    fondeadoresjucavi.push(json)
                                } else {
                                    fondeadoresjucavi.forEach(element => {
                                        if (element.NombreFondeador == valu)
                                            element.cantidad += 1;
                                    });
                                }
                            }
                        }
                    });

                    var total = 0;
                    fondeadoresjucavi.forEach(element => {
                        total += element.cantidad;
                    });
                    tmpfondeadoresjucavi = [];
                    fondeadoresjucavi.forEach(element => {
                        porcentaje = (element.cantidad * 100) / total;
                        tmpfondeadoresjucavi.push({
                            name: element.NombreFondeador,
                            y: porcentaje,
                            cantidad: element.cantidad,
                        });

                    });
                    series = [];
                    series.push({
                        name: 'Fondeador',
                        colorByPoint: true,
                        data: tmpfondeadoresjucavi

                    });
                    $('#totalcreditosJucavi').after('<div class="col">' + total + '</div>');
                    graficaPastel(series, 'container-jucavi');

                    if (data.length > 0) {

                    } else {
                        Swal.fire({
                            title: '¡Sin información!',
                            text: "No se encontraron registros en la fecha indicada",
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
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

        function generarGraficasMambu() {
            fondeadoresmambu = [];
            let dataJson;
            $.ajax({
                url: "sohmambu",
                method: "GET",
                dataType: "JSON",
                data: {},
                success: function(data) {


                    dataJson = reemplazarValoresVacios(data);

                    dataJson.forEach(element => {
                        element.montosegurofinanciado
                        for (var key in element) {
                            if (element[key] == null)
                                element[key] = "-";
                            if (key == "fondeador") {
                                encontrado = false;
                                valu = element[key];
                                // Recorrer cada objeto en el arreglo
                                for (var i = 0; i < fondeadoresmambu.length; i++) {
                                    if (fondeadoresmambu[i].NombreFondeador === valu) {
                                        encontrado = true;
                                        break;
                                    }
                                }
                                if (encontrado == false) {
                                    json = {
                                        NombreFondeador: valu,
                                        cantidad: 1
                                    }
                                    fondeadoresmambu.push(json)
                                } else {
                                    fondeadoresmambu.forEach(element => {
                                        if (element.NombreFondeador == valu)
                                            element.cantidad += 1;
                                    });
                                }
                            }
                        }
                    });


                    var total = 0;
                    fondeadoresmambu.forEach(element => {
                        total += element.cantidad;
                    });
                    tmpfondeadoresmambu = [];
                    fondeadoresmambu.forEach(element => {
                        porcentaje = (element.cantidad * 100) / total;
                        tmpfondeadoresmambu.push({
                            name: element.NombreFondeador,
                            y: porcentaje,
                            cantidad: element.cantidad,
                        });

                    });
                    series = [];
                    series.push({
                        name: 'Fondeador',
                        colorByPoint: true,
                        data: tmpfondeadoresmambu

                    });

                    $('#totalcreditosMambu').after('<div class="col">' + total + '</div>');


                    graficaPastel(series, 'container-mambu');

                    datosgenerales();

                    Swal.fire({
                        title: 'Gracias por esperar',
                        text: 'El reporte ha sido generado correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                },
                error: function(xhr, status, error) {
                    $.unblockUI();
                    console.log(
                        error); // Muestra cualquier error en la consola para depuración

                }
            });
        }


        function reemplazarValoresVacios(obj) {
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    if (typeof obj[key] === "object" && obj[key] !== null) {
                        // Si el valor es otro objeto, realizar la recursión y actualizar el valor en el objeto actual
                        obj[key] = reemplazarValoresVacios(obj[key]);
                    } else if (obj[key] === "" || obj[key] === null) {
                        // Si el valor es una cadena vacía, reemplazar por un guion y actualizar el valor en el objeto actual
                        obj[key] = "-";
                    }
                }
            }
            return obj; // Devolver el objeto actualizado
        }


        function exportarAnexoPromecapPdf(button) {

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
            const id = button.getAttribute('data-id');
            const tipoAnexo = button.getAttribute('data-tipo');

            // Obtener la fecha actual
            const today = new Date();
            const day = today.getDate();
            const year = today.getFullYear();
            const name = "ANEXO " + tipoAnexo + " " + day.toString() + " - " + year.toString() + ".pdf";
            const pdf = new jsPDF();

            if (tipoAnexo == "H" || tipoAnexo == "M" || tipoAnexo == "HI") {
                // HOJA 1
                var hoja1 = "#" + id + "Hoja1";
                var divContent1 = document.querySelector(hoja1);

                // Convertir el contenido del cuerpo a una imagen utilizando html2canvas
                html2canvas(divContent1).then(canvas => {
                    const imgData = canvas.toDataURL('image/jpeg', 1.0);

                    // Agregar la imagen al PDF
                    pdf.addImage(imgData, 'JPEG', 15, 15, 180, 0);

                    // HOJA 2
                    var hoja2 = "#" + id + "Hoja2";
                    var divContent2 = document.querySelector(hoja2);

                    // Convertir el contenido del cuerpo a una imagen utilizando html2canvas
                    html2canvas(divContent2).then(canvas => {
                        const imgData2 = canvas.toDataURL('image/jpeg', 1.0);

                        // Agregar la imagen de la segunda hoja al PDF
                        pdf.addPage(); // Agregar una nueva página
                        pdf.addImage(imgData2, 'JPEG', 15, 15, 180, 0);

                        // Descargar el PDF con el nombre especificado
                        pdf.save(name);
                        $.unblockUI();
                    });
                });
            } else {
                const divContent = document.querySelector("#" + id);

                // Convertir el contenido del cuerpo a una imagen utilizando html2canvas
                html2canvas(divContent).then(canvas => {
                    const imgData = canvas.toDataURL('image/jpeg', 1.0);

                    // Agregar la imagen al PDF
                    pdf.addImage(imgData, 'JPEG', 15, 15, 180, 0);

                    // Descargar el PDF con el nombre especificado
                    pdf.save(name);
                    $.unblockUI();
                });
            }
        }


        function exportarAnexoPromecapWord() {

            // Obtener el contenido del cuerpo HTML
            const divContent = document.querySelector('#anexoHdocumento');

            // Obtener el día y año actual
            const today = new Date();
            const day = today.getDate();
            const year = today.getFullYear();

            // Nombre del archivo
            const fileName = `ANEXO O ${day} - ${year}.docx`;

            // Obtener el contenido HTML como texto
            const htmlContent = divContent.innerHTML;

            // Utilizar Mammoth.js para convertir el HTML a formato de Word (docx)
            mammoth.convertToHtml(htmlContent)
                .then(result => {
                    // Crear un Blob con el contenido del documento de Word
                    const blob = new Blob([result.value], {
                        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    });

                    // Crear un enlace para descargar el archivo
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = fileName;

                    // Simular un clic en el enlace para iniciar la descarga
                    a.click();
                })
                .catch(error => {
                    console.error('Error al convertir a Word:', error);
                });
        }

        function ExportToDoc(element, filename = 'descarga') {
            var header =
                "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Export HTML to Word Document with JavaScript</title></head><body><center>";
            var footer = "</body></html></center>";
            // var html = header+document.getElementById(element).innerHTML+footer;

            var html = header + element.outerHTML.toString().replace("\n", "\n\n") + footer;

            var blob = new Blob(['\ufeff', html], {
                type: 'application/msword'
            });

            // Specify link url
            var url = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(html);

            // Specify file name
            filename = filename ? filename + '.doc' : 'document.doc';

            // Create download link element
            var downloadLink = document.createElement("a");

            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                // Create a link to the file
                downloadLink.href = url;
                // Setting the file name
                downloadLink.download = filename;
                //triggering the function
                downloadLink.click();
            }

            document.body.removeChild(downloadLink);
        }

        function calculofondeadorpromecap() {

            $.ajax({
                url: "calculofondeadorpromecap",
                method: "GET",
                dataType: "JSON",
                data: {
                    /* date: dateVal */
                },
                success: function(aforocalcpromecap) {

                    $('#aforocalpromecap').text("$" + parseFloat(aforocalcpromecap).toLocaleString('en-US'));
                    $('#anexohaforo').text("$" + parseFloat(aforocalcpromecap).toLocaleString());
                    $('#anexohaforoinst').text("$" + parseFloat(aforocalcpromecap).toLocaleString());
                    $('#anexomaforo').text("$" + parseFloat(aforocalcpromecap).toLocaleString());
                    $('#anexom2aforo').text("$" + parseFloat(aforocalcpromecap).toLocaleString());
                    aforopromecap = aforocalcpromecap;
                    $.unblockUI();
                }
            });
        }

        function historicoaforopromecap() {
            $.ajax({
                url: "historicoaforopromecap",
                method: "GET",
                dataType: "JSON",
                data: {
                    /* date: dateVal */
                },
                success: function(aforocalcpromecap) {

                    aforocalcpromecap.forEach(element => {
                        element.fecha = element.fecha.split(' ')[0];
                        element.aforo = parseFloat(element.aforo).toLocaleString('en-US');
                    });
                    $('#tablahistoricopromecap').DataTable({
                        destroy: true,
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
                        buttons: [],
                        destroy: true,
                        processing: true,
                        sort: true,
                        paging: true,
                        autoWidth: true,
                        data: aforocalcpromecap,
                        columns: [{
                                data: 'fecha'
                            },
                            {
                                data: 'aforo'
                            },
                        ]
                    });
                }
            });
        }

        function getdateformatted() {

            var fechaActual = new Date();
            var dia = fechaActual.getDate();
            var mes = fechaActual.getMonth() + 1; // Los meses van de 0 a 11, por lo que se suma 1
            var año = fechaActual.getFullYear();
            var horas = fechaActual.getHours();
            var minutos = fechaActual.getMinutes();

            // Asegurar que los valores tengan siempre dos dígitos
            dia = (dia < 10) ? '0' + dia : dia;
            mes = (mes < 10) ? '0' + mes : mes;
            horas = (horas < 10) ? '0' + horas : horas;
            minutos = (minutos < 10) ? '0' + minutos : minutos;

            var fechaFormateada = dia + '-' + mes + '-' + año + ' ' + horas + ':' + minutos;
            return fechaFormateada;

        }
    </script>
@stop
