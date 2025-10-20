<?php
$servername = "db";
$username = "turnos_user";
$password = "turnos_pass";
$dbname = "turnos_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = $_POST['tipo'] ?? '';

    if (!in_array($tipo, ['compras', 'despacho'])) {
        die("Tipo de turno inválido");
    }

    // Buscar el último número del día para ese tipo
    $stmt = $conn->prepare("SELECT MAX(numero) AS ultimo FROM turnos WHERE tipo = :tipo AND DATE(fecha) = CURDATE()");
    $stmt->execute(['tipo' => $tipo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nuevo_numero = ($row['ultimo'] ?? 0) + 1;

    // Generar el código (C01 o D01)
    $prefijo = ($tipo === 'compras') ? 'C' : 'D';
    $codigo = $prefijo . str_pad($nuevo_numero, 2, '0', STR_PAD_LEFT);

    // Insertar el turno con el código
    $stmt = $conn->prepare("INSERT INTO turnos (numero, codigo, tipo, estado) VALUES (:numero, :codigo, :tipo, 'espera')");
    $stmt->execute([
        'numero' => $nuevo_numero,
        'codigo' => $codigo,
        'tipo' => $tipo
    ]);

    $mensaje = "Tu número de turno para <strong>" . ucfirst($tipo) . "</strong> es: <h1 class='display-3 mt-3 text-primary'>$codigo</h1>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kiosco de Turnos — Repuestos España</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding-top: 3rem;
        }
        .btn-turno {
            font-size: 2rem;
            padding: 2rem 4rem;
            margin: 1rem;
            border-radius: 1rem;
        }
        .compras { background-color: #00b4d8; border: none; }
        .despacho { background-color: #ffd60a; border: none; }
        .compras:hover { background-color: #0096c7; }
        .despacho:hover { background-color: #ffc300; }
        .mensaje {
            margin-top: 2rem;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <h1 class="mb-4">🧾 Kiosco de Turnos — Repuestos España</h1>
    <form method="POST">
        <button type="submit" name="tipo" value="compras" class="btn btn-primary btn-turno compras">
            🛒 Compras
        </button>
        <button type="submit" name="tipo" value="despacho" class="btn btn-warning btn-turno despacho">
            📦 Despacho
        </button>
    </form>

    <?php if ($mensaje): ?>
        <div class="alert alert-success mensaje" role="alert">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>
</body>
</html>
