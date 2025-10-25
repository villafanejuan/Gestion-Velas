<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

$mensaje = '';

// Manejar actualizaciones de precios de materiales
if (isset($_POST['actualizar_precios'])) {
    $updates = [];
    $stmt = $conn->prepare("UPDATE precios_materiales SET precio_kg = ? WHERE nombre = ? AND tipo = ?");
    
    // Actualizar alcohol
    if (isset($_POST['precio_alcohol'])) {
        $precio_alcohol = floatval($_POST['precio_alcohol']);
        $stmt->bind_param("dss", $precio_alcohol, $nombre_alcohol, $tipo_material);
        $nombre_alcohol = 'Alcohol';
        $tipo_material = 'material';
        $stmt->execute();
        $updates[] = "Alcohol: $" . number_format($precio_alcohol, 2) . "/L";
    }
    
    // Actualizar esencia de difusores
    if (isset($_POST['precio_esencia_difusor'])) {
        $precio_esencia = floatval($_POST['precio_esencia_difusor']);
        $stmt->bind_param("dss", $precio_esencia, $nombre_esencia, $tipo_esencia);
        $nombre_esencia = 'Esencia_Difusor';
        $tipo_esencia = 'esencia';
        $stmt->execute();
        $updates[] = "Esencia para difusores: $" . number_format($precio_esencia, 2) . "/kg";
    }
    
    // Recalcular precios de difusores
    if (!empty($updates)) {
        recalcularPreciosDifusores($conn);
        $mensaje = "Precios actualizados: " . implode(", ", $updates) . ". Se han recalculado los precios de todos los difusores.";
    }
}

// Manejar operaciones con frascos
if (isset($_POST['frasco_action'])) {
    if ($_POST['frasco_action'] === 'crear') {
        $nombre = trim($_POST['nombre_frasco']);
        $precio = floatval($_POST['precio_frasco']);
        
        $stmt = $conn->prepare("INSERT INTO tipos_frascos (nombre, precio_unidad) VALUES (?, ?)");
        $stmt->bind_param("sd", $nombre, $precio);
        
        if ($stmt->execute()) {
            $mensaje = "Frasco '$nombre' creado exitosamente.";
        } else {
            $mensaje = "Error: El frasco ya existe o hubo un problema.";
        }
    } elseif ($_POST['frasco_action'] === 'editar') {
        $id = intval($_POST['frasco_id']);
        $nombre = trim($_POST['nombre_frasco']);
        $precio = floatval($_POST['precio_frasco']);
        
        $stmt = $conn->prepare("UPDATE tipos_frascos SET nombre = ?, precio_unidad = ? WHERE id = ?");
        $stmt->bind_param("sdi", $nombre, $precio, $id);
        
        if ($stmt->execute()) {
            recalcularPreciosDifusores($conn);
            $mensaje = "Frasco actualizado y precios de difusores recalculados.";
        }
    } elseif ($_POST['frasco_action'] === 'eliminar') {
        $id = intval($_POST['frasco_id']);
        
        // Verificar si hay difusores usando este frasco
        $check = $conn->query("SELECT COUNT(*) as count FROM productos WHERE tipo_frasco_id = $id");
        $result = $check->fetch_assoc();
        
        if ($result['count'] > 0) {
            $mensaje = "No se puede eliminar: hay " . $result['count'] . " difusor(es) usando este frasco.";
        } else {
            $conn->query("DELETE FROM tipos_frascos WHERE id = $id");
            $mensaje = "Frasco eliminado exitosamente.";
        }
    }
}

// Obtener precios actuales
$precios = [];
$result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales WHERE nombre IN ('Alcohol', 'Esencia_Difusor')");
while ($row = $result->fetch_assoc()) {
    $precios[$row['nombre']] = $row['precio_kg'];
}

// Obtener frascos
$frascos = $conn->query("SELECT * FROM tipos_frascos WHERE activo = 1 ORDER BY nombre");

function recalcularPreciosDifusores($conn) {
    // Obtener precios actuales
    $precios = [];
    $result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales WHERE nombre IN ('Alcohol', 'Esencia_Difusor')");
    while ($row = $result->fetch_assoc()) {
        $precios[$row['nombre']] = $row['precio_kg'];
    }
    
    // Recalcular todos los difusores
    $difusores = $conn->query("SELECT p.*, tf.precio_unidad as frasco_precio FROM productos p LEFT JOIN tipos_frascos tf ON p.tipo_frasco_id = tf.id WHERE p.tipo = 'difusor'");
    $update_stmt = $conn->prepare("UPDATE productos SET costo_insumos = ?, costo_total = ?, precio_venta = ? WHERE id = ?");
    
    while ($difusor = $difusores->fetch_assoc()) {
        $alcohol_ml = floatval($difusor['alcohol_ml']);
        $esencia_g = floatval($difusor['esencia_g']);
        $frasco_precio = floatval($difusor['frasco_precio'] ?? $difusor['frascos_precio_unidad']);
        $pack_extras = floatval($difusor['pack_extras']);
        
        // Calcular costos con precios actualizados
        $precio_por_ml = $precios['Alcohol'] / 1000;
        $costo_alcohol = $alcohol_ml * $precio_por_ml;
        $costo_esencia = ($precios['Esencia_Difusor'] / 1000) * $esencia_g;
        
        $costo_insumos = $costo_alcohol + $costo_esencia + $frasco_precio + $pack_extras;
        $costo_total = $costo_insumos;
        $precio_venta = $costo_total * (1 + $difusor['ganancia_porcentaje'] / 100);
        
        $update_stmt->bind_param("dddi", $costo_insumos, $costo_total, $precio_venta, $difusor['id']);
        $update_stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios - Difusores</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-100 via-blue-100 to-purple-100 min-h-screen py-8">

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-extrabold text-blue-600">Gestión de Precios - Difusores</h1>
                <div class="space-x-2">
                    <a href="precios_materiales.php" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg">Materiales de Velas</a>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Volver al inicio</a>
                </div>
            </div>
            <p class="text-gray-600 mt-2">Gestiona precios de alcohol, esencias para difusores y tipos de frascos.</p>
        </div>

        <!-- Mensaje de éxito -->
        <?php if ($mensaje): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Precios de Materiales -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Precios de Materiales</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="actualizar_precios" value="1">
                    
                    <!-- Alcohol -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <label for="precio_alcohol" class="block text-sm font-medium text-blue-800 mb-2">
                            Alcohol ($/L)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="precio_alcohol" 
                                name="precio_alcohol" 
                                value="<?= number_format($precios['Alcohol'] ?? 0, 2, '.', '') ?>"
                                class="w-full pl-8 pr-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                required
                            >
                        </div>
                    </div>

                    <!-- Esencia para Difusores -->
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <label for="precio_esencia_difusor" class="block text-sm font-medium text-purple-800 mb-2">
                            Esencia para Difusores ($/kg)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="precio_esencia_difusor" 
                                name="precio_esencia_difusor" 
                                value="<?= number_format($precios['Esencia_Difusor'] ?? 0, 2, '.', '') ?>"
                                class="w-full pl-8 pr-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-400"
                                required
                            >
                        </div>
                    </div>

                    <!-- Botón actualizar precios -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-md font-semibold"
                    >
                        Actualizar Precios
                    </button>
                </form>
            </div>

            <!-- Gestión de Frascos -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tipos de Frascos</h2>
                
                <!-- Formulario para crear/editar frasco -->
                <form method="POST" class="mb-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Frasco</label>
                            <input 
                                type="text" 
                                name="nombre_frasco" 
                                id="nombre_frasco"
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-400"
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Precio por Unidad ($)</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="precio_frasco" 
                                id="precio_frasco"
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-400"
                                required
                            >
                        </div>
                    </div>
                    
                    <input type="hidden" name="frasco_action" id="frasco_action" value="crear">
                    <input type="hidden" name="frasco_id" id="frasco_id">
                    
                    <div class="flex gap-2">
                        <button 
                            type="submit" 
                            id="btn_submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold"
                        >
                            Crear Frasco
                        </button>
                        <button 
                            type="button" 
                            id="btn_cancelar"
                            onclick="cancelarEdicion()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hidden"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>

                <!-- Lista de frascos -->
                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-700">Frascos Disponibles:</h3>
                    <?php while($frasco = $frascos->fetch_assoc()): ?>
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                            <div>
                                <span class="font-medium"><?= htmlspecialchars($frasco['nombre']) ?></span>
                                <span class="text-gray-600 ml-2">$<?= number_format($frasco['precio_unidad'], 2) ?></span>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    onclick="editarFrasco(<?= $frasco['id'] ?>, '<?= htmlspecialchars($frasco['nombre']) ?>', <?= $frasco['precio_unidad'] ?>)"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                                >
                                    Editar
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este frasco?')">
                                    <input type="hidden" name="frasco_action" value="eliminar">
                                    <input type="hidden" name="frasco_id" value="<?= $frasco['id'] ?>">
                                    <button 
                                        type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-yellow-800">
                    <h3 class="font-semibold mb-1">¿Cómo funciona?</h3>
                    <ul class="text-sm list-disc list-inside space-y-1">
                        <li>Al actualizar precios de alcohol o esencias, se recalculan automáticamente todos los difusores</li>
                        <li>Puedes crear diferentes tipos de frascos con precios específicos</li>
                        <li>Al editar el precio de un frasco, se recalculan los difusores que lo usan</li>
                        <li>No puedes eliminar un frasco que esté siendo usado por algún difusor</li>
                        <li>Los precios de esencias para difusores son independientes de las esencias para velas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editarFrasco(id, nombre, precio) {
            document.getElementById('nombre_frasco').value = nombre;
            document.getElementById('precio_frasco').value = precio.toFixed(2);
            document.getElementById('frasco_action').value = 'editar';
            document.getElementById('frasco_id').value = id;
            document.getElementById('btn_submit').textContent = 'Actualizar Frasco';
            document.getElementById('btn_submit').className = 'bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold';
            document.getElementById('btn_cancelar').classList.remove('hidden');
        }

        function cancelarEdicion() {
            document.getElementById('nombre_frasco').value = '';
            document.getElementById('precio_frasco').value = '';
            document.getElementById('frasco_action').value = 'crear';
            document.getElementById('frasco_id').value = '';
            document.getElementById('btn_submit').textContent = 'Crear Frasco';
            document.getElementById('btn_submit').className = 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold';
            document.getElementById('btn_cancelar').classList.add('hidden');
        }
    </script>

</body>
</html>
