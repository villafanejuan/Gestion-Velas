<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión");

// Obtener productos
$productos = $conn->query("SELECT * FROM productos ORDER BY id DESC");

// Obtener precios de materiales y esencias
$precios = [];
$result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales");
while ($row = $result->fetch_assoc()) {
    $precios[$row['nombre']] = $row['precio_kg'];
}

// Obtener tipos de frascos
$frascos = $conn->query("SELECT * FROM tipos_frascos WHERE activo = 1 ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestión de Velas y Difusores</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DataTables CSS CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <!-- DataTables Buttons and dependencies (JSZip, pdfmake) -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-pink-100 to-yellow-100 min-h-screen flex flex-col items-center py-8">

    <!-- Bienvenida -->
    <div class="w-full max-w-4xl mx-auto mb-8">
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
            <h1 class="text-4xl font-extrabold text-pink-600 mb-2">¡Bienvenido!</h1>
            <p class="text-lg text-gray-700 mb-6">al sistema de gestión de velas y difusores</p>
            <!-- Botones para gestionar precios -->
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="precios_materiales.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-pink-600 hover:to-pink-700 shadow-md font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    Materiales de Velas
                </a>
                <a href="precios_difusores.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-md font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Materiales de Difusores
                </a>
            </div>
        </div>
    </div>

    <!-- Contenedor principal con dos columnas: formulario (izq) y listado (der) -->
    <div class="w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Columna izquierda: Formulario -->
        <div class="lg:col-span-4">
            <form id="form-agregar" action="guardar_producto.php" method="POST" class="bg-white p-6 rounded-xl shadow-lg w-full">
                <h2 class="text-2xl font-bold text-pink-600 mb-4">Agregar producto</h2>

                <!-- Tipo -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold" for="tipo">Tipo:</label>
            <select id="tipo" name="tipo" class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:outline-none focus:ring-2 focus:ring-pink-200" required>
                <option value="vela">Vela</option>
                <option value="difusor">Difusor</option>
            </select>
        </div>

        <!-- Nombre -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold" for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="w-full border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-pink-200" required />
        </div>

        <!-- Campos para Vela -->
        <div id="campos-vela" class="mb-4 space-y-4">
            
            <!-- Materiales en gramos -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-3">Materiales (en gramos)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium" for="material_apf_g">Material APF (g):</label>
                        <input type="number" step="0.01" id="material_apf_g" name="material_apf_g" class="w-full border border-blue-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['APF'] ?? 0, 2) ?>/kg</p>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium" for="material_vpf_g">Material VPF (g):</label>
                        <input type="number" step="0.01" id="material_vpf_g" name="material_vpf_g" class="w-full border border-blue-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['VPF'] ?? 0, 2) ?>/kg</p>
                    </div>
                </div>
            </div>

            <!-- Esencias -->
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-800 mb-3">Esencias</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium" for="esc_g">Esencias (g):</label>
                        <input type="number" step="0.01" id="esc_g" name="esc_g" class="w-full border border-purple-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-purple-400" required />
                        <p class="text-xs text-purple-600 mt-1">Precio actual: $<?= number_format($precios['Esencias'] ?? 0, 2) ?>/kg</p>
                    </div>
                </div>
            </div>

            <!-- Otros costos -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium" for="pack_extras">Pack/hs/extras ($):</label>
                    <input type="number" step="0.01" id="pack_extras" name="pack_extras" class="w-full border border-gray-200 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-pink-100" required />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="costo_insumos">Costo insumos ($):</label>
                    <input type="number" step="0.01" id="costo_insumos" name="costo_insumos" class="w-full border border-gray-200 rounded-lg p-2 vela-campo bg-gray-50" readonly required />
                </div>
            </div>

        </div>

        <!-- Campos para Difusor -->
        <div id="campos-difusor" class="mb-4 space-y-4 hidden">
            
            <!-- Alcohol -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-3">Alcohol</h3>
                <div>
                    <label class="block mb-1 text-sm font-medium" for="alcohol_ml">Alcohol (ml):</label>
                    <input type="number" step="0.01" id="alcohol_ml" name="alcohol_ml" class="w-full border border-blue-300 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-blue-400" required />
                    <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['Alcohol'] ?? 0, 2) ?>/L</p>
                </div>
            </div>

            <!-- Esencia para difusores -->
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h3 class="font-semibold text-purple-800 mb-3">Esencia</h3>
                <div>
                    <label class="block mb-1 text-sm font-medium" for="esencia_g">Esencia (g):</label>
                    <input type="number" step="0.01" id="esencia_g" name="esencia_g" class="w-full border border-purple-300 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-purple-400" required />
                    <p class="text-xs text-purple-600 mt-1">Precio actual: $<?= number_format($precios['Esencia_Difusor'] ?? 0, 2) ?>/kg</p>
                </div>
            </div>

            <!-- Selección de frasco -->
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h3 class="font-semibold text-green-800 mb-3">Frasco</h3>
                <div>
                    <label class="block mb-1 text-sm font-medium" for="tipo_frasco_id">Tipo de Frasco:</label>
                    <select id="tipo_frasco_id" name="tipo_frasco_id" class="w-full border border-green-300 rounded-lg p-2 difusor-campo bg-white focus:outline-none focus:ring-2 focus:ring-green-400" required>
                        <option value="">Selecciona un frasco...</option>
                        <?php 
                        $frascos->data_seek(0); // Reset pointer
                        while($frasco = $frascos->fetch_assoc()): 
                        ?>
                            <option value="<?= $frasco['id'] ?>" data-precio="<?= $frasco['precio_unidad'] ?>">
                                <?= htmlspecialchars($frasco['nombre']) ?> - $<?= number_format($frasco['precio_unidad'], 2) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <p class="text-xs text-green-600 mt-1">El precio se aplica automáticamente según el frasco seleccionado</p>
                </div>
            </div>

            <!-- Otros costos -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-medium" for="pack_extras_difusor">Pack/hs/extras ($):</label>
                    <input type="number" step="0.01" id="pack_extras_difusor" name="pack_extras_difusor" class="w-full border border-gray-200 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-pink-100" required />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="costo_insumos_difusor">Costo insumos ($):</label>
                    <input type="number" step="0.01" id="costo_insumos_difusor" name="costo_insumos_difusor" class="w-full border border-gray-200 rounded-lg p-2 bg-gray-50" readonly required />
                </div>
            </div>

        </div>

        <!-- Ganancia -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold" for="ganancia_porcentaje">Porcentaje de ganancia (%):</label>
            <input type="number" id="ganancia_porcentaje" name="ganancia_porcentaje" value="100" min="0" class="w-full border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-pink-100" required />
        </div>

        <!-- Botón Guardar -->
        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-2 rounded-lg hover:from-pink-600 hover:to-pink-700 shadow-md text-lg font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Guardar producto
        </button>

    </form>
        </div>

        <!-- Columna derecha: Listado -->
        <div class="lg:col-span-8">
            <!-- Mensaje de éxito -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="mb-4">
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabla de productos -->
            <div id="tabla-productos" class="bg-white p-4 rounded-xl shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-gray-700">Productos registrados</h2>
                    <!-- Contenedor para los botones de export -->
                    <div id="exportButtons" class="flex gap-2"></div>
                </div>
                <div class="overflow-x-auto">
                    <table id="productosTable" class="min-w-full bg-white rounded table-auto">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-50 to-blue-100">
                        <th class="p-3 text-left text-sm font-medium text-gray-600">Tipo</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-600">Nombre</th>
                        <th class="p-3 text-right text-sm font-medium text-gray-600">Costo total</th>
                        <th class="p-3 text-right text-sm font-medium text-gray-600">Ganancia (%)</th>
                        <th class="p-3 text-right text-sm font-medium text-gray-600">Precio venta</th>
                        <th class="p-3 text-center text-sm font-medium text-gray-600">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $productos->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 align-middle"><?= htmlspecialchars($row['tipo']) ?></td>
                        <td class="p-3 align-middle"><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="p-3 text-right align-middle">$<?= number_format($row['costo_total'], 2, ',', '.') ?></td>
                        <td class="p-3 text-right align-middle"><?= $row['ganancia_porcentaje'] ?>%</td>
                        <td class="p-3 font-bold text-green-700 text-right align-middle">$<?= number_format($row['precio_venta'], 2, ',', '.') ?></td>
                        <td class="p-3 text-center align-middle">
                            <div class="inline-flex gap-2">
                                <a href="editar_producto.php?id=<?= $row['id'] ?>" class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md shadow text-sm font-medium">Editar</a>
                                <a href="borrar_producto.php?id=<?= $row['id'] ?>" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md shadow text-sm font-medium" onclick="return confirm('¿Seguro que deseas borrar este producto?')">Borrar</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->

    <script>
        // Precios obtenidos desde PHP
        const precios = {
            APF: <?= $precios['APF'] ?? 0 ?>,
            VPF: <?= $precios['VPF'] ?? 0 ?>,
            Esencias: <?= $precios['Esencias'] ?? 0 ?>,
            Alcohol: <?= $precios['Alcohol'] ?? 0 ?>,
            Esencia_Difusor: <?= $precios['Esencia_Difusor'] ?? 0 ?>
        };

        // Función para calcular costo insumos de velas
        function calcularCostoInsumos() {
            const matApfG = parseFloat(document.querySelector('input[name="material_apf_g"]').value) || 0;
            const matVpfG = parseFloat(document.querySelector('input[name="material_vpf_g"]').value) || 0;
            const escG = parseFloat(document.querySelector('input[name="esc_g"]').value) || 0;
            const pack = parseFloat(document.querySelector('input[name="pack_extras"]').value) || 0;

            // Calcular costos usando precios centralizados
            const costoApf = (precios.APF / 1000) * matApfG;
            const costoVpf = (precios.VPF / 1000) * matVpfG;
            const costoEscencias = (precios.Esencias / 1000) * escG;

            // Total incluye pack/hs/extras
            const costoTotal = costoApf + costoVpf + costoEscencias + pack;
            
            document.querySelector('input[name="costo_insumos"]').value = costoTotal.toFixed(2);
        }

        // Función para calcular costo insumos de difusores con precios centralizados
        function calcularCostoInsumosDifusor() {
            const alcoholMl = parseFloat(document.querySelector('input[name="alcohol_ml"]').value) || 0;
            const esenciaG = parseFloat(document.querySelector('input[name="esencia_g"]').value) || 0;
            const pack = parseFloat(document.querySelector('input[name="pack_extras_difusor"]').value) || 0;
            
            // Obtener precio del frasco seleccionado
            const frascoSelect = document.querySelector('select[name="tipo_frasco_id"]');
            const frascoOption = frascoSelect.options[frascoSelect.selectedIndex];
            const precioFrasco = parseFloat(frascoOption?.dataset?.precio) || 0;

            // Calcular costos usando precios centralizados
            const precioPorMl = precios.Alcohol / 1000;
            const costoAlcohol = alcoholMl * precioPorMl;
            const costoEsencia = (precios.Esencia_Difusor / 1000) * esenciaG;

            const costoTotal = costoAlcohol + costoEsencia + precioFrasco + pack;
            
            document.querySelector('input[name="costo_insumos_difusor"]').value = costoTotal.toFixed(2);
        }

        // Mostrar campos según tipo seleccionado
        function toggleCampos() {
            const tipoSelect = document.querySelector('select[name="tipo"]');
            const camposVela = document.getElementById('campos-vela');
            const camposDifusor = document.getElementById('campos-difusor');

            if (tipoSelect.value === 'vela') {
                camposVela.classList.remove('hidden');
                camposDifusor.classList.add('hidden');
                // campos vela requeridos
                document.querySelectorAll('.vela-campo').forEach(e => e.required = true);
                document.querySelectorAll('.difusor-campo').forEach(e => e.required = false);
            } else {
                camposVela.classList.add('hidden');
                camposDifusor.classList.remove('hidden');
                // campos difusor requeridos
                document.querySelectorAll('.vela-campo').forEach(e => e.required = false);
                document.querySelectorAll('.difusor-campo').forEach(e => e.required = true);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar cálculo y campos
            toggleCampos();

            // Eventos para calcular costos vela
            document.querySelectorAll('.vela-campo').forEach(input => {
                if (["material_apf_g","material_vpf_g","esc_g","pack_extras"].includes(input.name)) {
                    input.addEventListener('input', calcularCostoInsumos);
                }
            });
            calcularCostoInsumos();

            // Eventos para calcular costos difusor
            document.querySelectorAll('.difusor-campo').forEach(input => {
                if (["alcohol_ml","esencia_g","pack_extras_difusor","tipo_frasco_id"].includes(input.name)) {
                    input.addEventListener('input', calcularCostoInsumosDifusor);
                    input.addEventListener('change', calcularCostoInsumosDifusor);
                }
            });
            calcularCostoInsumosDifusor();

            // Evento para cambio de tipo
            document.querySelector('select[name="tipo"]').addEventListener('change', () => {
                toggleCampos();
                calcularCostoInsumos();
                calcularCostoInsumosDifusor();
            });

            // Inicializar DataTables en español y agregar botones de export (Excel, PDF)
            $('#productosTable').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "searchPlaceholder": "Filtrar productos...",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activar para ordenar la columna ascendente",
                        "sortDescending": ": activar para ordenar la columna descendente"
                    }
                },
                paging: true,
                pageLength: 10,
                ordering: true,
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 5 } // Desactivar orden en columna acción
                ],
                // Dom para crear los botones (se moverán luego al contenedor personalizado)
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Exportar Excel',
                        titleAttr: 'Exportar a Excel',
                        className: 'dt-export-excel',
                        exportOptions: {
                            // Excluir la última columna (Acción)
                            columns: [0,1,2,3,4]
                        },
                        action: function(e, dt, node, config) {
                            // forzar orden alfabético por columna 'Tipo' (índice 0)
                            var oldOrder = dt.order();
                            dt.order([[0, 'asc']]).draw(false);
                            // llamar a la acción por defecto de excelHtml5
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                            // restaurar orden
                            dt.order(oldOrder).draw(false);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Exportar PDF',
                        titleAttr: 'Exportar a PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        className: 'dt-export-pdf',
                        exportOptions: {
                            columns: [0,1,2,3,4]
                        },
                        action: function(e, dt, node, config) {
                            var oldOrder = dt.order();
                            dt.order([[0, 'asc']]).draw(false);
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, node, config);
                            dt.order(oldOrder).draw(false);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Exportar PDF Clientes',
                        titleAttr: 'Exportar a PDF (versión clientes)',
                        orientation: 'portrait',
                        pageSize: 'A4',
                        className: 'dt-export-pdf-clientes',
                        title: 'Listado de Precios de Velas y Difusores',
                        exportOptions: {
                            columns: [0,1,4] // Solo Tipo, Nombre y Precio venta
                        },
                        customize: function(doc) {
                            // Centrar el contenido de todas las columnas
                            doc.content[1].table.body.forEach(function(row) {
                                row.forEach(function(cell) {
                                    cell.alignment = 'center';
                                });
                            });
                            // Centrar los encabezados
                            doc.content[1].table.headerRows = 1;
                            doc.content[1].table.body[0].forEach(function(header) {
                                header.alignment = 'center';
                            });
                        },
                        action: function(e, dt, node, config) {
                            var oldOrder = dt.order();
                            dt.order([[0, 'asc']]).draw(false);
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, node, config);
                            dt.order(oldOrder).draw(false);
                        }
                    }
                ]
            });

            // Mover los botones de exportación al contenedor derecho del título
            var tabla = $('#productosTable').DataTable();
            if (tabla.buttons) {
                // agregar los botones al contenedor y aplicar clases Tailwind para que coincidan con el diseño
                var btnContainer = tabla.buttons().container();
                btnContainer.appendTo('#exportButtons');
                // Aplicar estilos Tailwind a los botones creados
                $('#exportButtons .dt-button').each(function() {
                    var $b = $(this);
                    // Common Tailwind classes
                    $b.addClass('inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium shadow');
                    // Diferenciar color según tipo
                    if ($b.hasClass('dt-export-excel')) {
                        $b.addClass('bg-green-500 hover:bg-green-600 text-white');
                    } else if ($b.hasClass('dt-export-pdf')) {
                        $b.addClass('bg-red-500 hover:bg-red-600 text-white');
                    } else {
                        $b.addClass('bg-gray-200 hover:bg-gray-300');
                    }
                });
            }
        });
    </script>

</body>
</html>

