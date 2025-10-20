<?php
$servername = "db";
$username = "turnos_user";
$password = "turnos_pass";
$dbname = "turnos_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>✅ Conexión exitosa a la base de datos MySQL.</h2>";
} catch(PDOException $e) {
    echo "<h2>❌ Error de conexión: " . $e->getMessage() . "</h2>";
}
?>
