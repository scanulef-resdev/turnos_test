<?php
// Configuración de cabeceras SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Configuración de la base de datos
$servername = "db";
$username = "turnos_user";
$password = "turnos_pass";
$dbname = "turnos_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
    ob_flush();
    flush();
    exit;
}

// Guardamos un hash del último estado conocido
$ultimo_hash = '';

while (true) {
    // Consultar los turnos activos y últimos
    $stmt = $conn->query("
        SELECT tipo, codigo, numero, estado, fecha
        FROM turnos
        WHERE estado IN ('atendiendo', 'atendido')
        ORDER BY fecha DESC
        LIMIT 10
    ");
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear un hash para detectar cambios
    $hash_actual = md5(json_encode($turnos));

    // Si algo cambió, enviamos los datos al cliente
    if ($hash_actual !== $ultimo_hash) {
        $ultimo_hash = $hash_actual;
        echo "event: update\n";
        echo "data: " . json_encode($turnos) . "\n\n";
        ob_flush();
        flush();
    }

    // Esperamos un segundo antes de volver a revisar
    sleep(1);
}
?>
