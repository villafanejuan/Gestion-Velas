<?php
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión");

if (!isset($_GET['id'])) die('ID no especificado');
$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM productos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php?msg=" . urlencode("Producto eliminado correctamente"));
exit;