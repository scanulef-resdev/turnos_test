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

// Obtener totales generales
$totales = $conn->query("
    SELECT tipo,
           COUNT(*) AS total,
           SUM(estado='espera') AS espera,
           SUM(estado='atendiendo') AS atendiendo,
           SUM(estado='atendido') AS atendido,
           SUM(estado='ausente') AS ausente
    FROM turnos
    WHERE DATE(fecha) = CURDATE()
    GROUP BY tipo
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener el historial completo (últimos 20 turnos)
$historial = $conn->query("
    SELECT id, numero, tipo, estado, fecha
    FROM turnos
    ORDER BY fecha DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración — Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1 class="mb-4 text-center">🧮 Panel de Administración — Repuestos España</h1>

        <h3>📊 Resumen Diario (Hoy)</h3>
        <?php if ($totales): ?>
            <table class="table table-bordered table-striped mt-3 text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>En espera</th>
                        <th>Atendiendo</th>
                        <th>Atendidos</th>
                        <th>Ausentes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($totales as $t): ?>
                        <tr>
                            <td><?= ucfirst($t['tipo']) ?></td>
                            <td><?= $t['total'] ?></td>
                            <td><?= $t['espera'] ?></td>
                            <td><?= $t['atendiendo'] ?></td>
                            <td><?= $t['atendido'] ?></td>
                            <td><?= $t['ausente'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No hay datos para hoy.</p>
        <?php endif; ?>

        <hr class="my-5">

        <h3>📜 Últimos 20 turnos registrados</h3>
        <?php if ($historial): ?>
            <table class="table table-sm table-hover mt-3 text-center">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= $h['id'] ?></td>
                            <td><?= ucfirst($h['tipo']) ?></td>
                            <td><strong><?= $h['numero'] ?></strong></td>
                            <td><?= ucfirst($h['estado']) ?></td>
                            <td><?= $h['fecha'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Sin historial disponible.</p>
        <?php endif; ?>
    </div>
</body>
</html>
