<?php
header('Content-Type: application/json');

$servername = "db";
$username = "turnos_user";
$password = "turnos_pass";
$dbname = "turnos_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Turnos actualmente en atención
$stmt = $conn->query("SELECT tipo, numero, estado FROM turnos WHERE estado = 'atendiendo' ORDER BY fecha DESC");
$en_atencion = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Últimos 5 turnos llamados (atendidos o atendiendo)
$stmt = $conn->query("SELECT tipo, numero, estado FROM turnos WHERE estado IN ('atendido', 'atendiendo') ORDER BY fecha DESC LIMIT 5");
$ultimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'en_atencion' => $en_atencion,
    'ultimos' => $ultimos
]);
