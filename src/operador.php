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

$tipo = $_GET['tipo'] ?? null;
if (!in_array($tipo, ['compras', 'despacho'])) {
    die("Debe indicar el tipo de sección (?tipo=compras o ?tipo=despacho)");
}

// Llamar siguiente turno
if (isset($_POST['accion']) && $_POST['accion'] === 'llamar') {
    $stmt = $conn->prepare("SELECT * FROM turnos WHERE tipo = :tipo AND estado = 'espera' ORDER BY id ASC LIMIT 1");
    $stmt->execute(['tipo' => $tipo]);
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($turno) {
        // Marcar como atendiendo
        $conn->prepare("UPDATE turnos SET estado = 'atendiendo' WHERE id = :id")->execute(['id' => $turno['id']]);
    }
}

// Marcar como atendido
if (isset($_POST['accion']) && $_POST['accion'] === 'atendido') {
    $conn->exec("UPDATE turnos SET estado = 'atendido' WHERE tipo = '$tipo' AND estado = 'atendiendo'");
}

// Marcar como ausente
if (isset($_POST['accion']) && $_POST['accion'] === 'ausente') {
    $conn->exec("UPDATE turnos SET estado = 'ausente' WHERE tipo = '$tipo' AND estado = 'atendiendo'");
}

// Obtener el turno actual en atención
$stmt = $conn->prepare("SELECT * FROM turnos WHERE tipo = :tipo AND estado = 'atendiendo' ORDER BY fecha DESC LIMIT 1");
$stmt->execute(['tipo' => $tipo]);
$turno_actual = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los próximos en espera
$stmt = $conn->prepare("SELECT * FROM turnos WHERE tipo = :tipo AND estado = 'espera' ORDER BY id ASC LIMIT 5");
$stmt->execute(['tipo' => $tipo]);
$en_espera = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Operador — <?= ucfirst($tipo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container text-center">
        <h1 class="mb-4">🧑‍💼 Panel de Operador — <?= ucfirst($tipo) ?></h1>

        <?php if ($turno_actual): ?>
            <div class="alert alert-success fs-3">
                Turno actual: <strong>N° <?= $turno_actual['numero'] ?></strong>
            </div>
            <form method="POST" class="d-flex justify-content-center gap-3 mt-3">
                <button name="accion" value="atendido" class="btn btn-primary btn-lg">✅ Atendido</button>
                <button name="accion" value="ausente" class="btn btn-warning btn-lg">🚫 Ausente</button>
            </form>
        <?php else: ?>
            <div class="alert alert-secondary fs-4">
                No hay turno en atención actualmente.
            </div>
            <form method="POST">
                <button name="accion" value="llamar" class="btn btn-success btn-lg">📢 Llamar siguiente turno</button>
            </form>
        <?php endif; ?>

        <hr class="my-5">

        <h3>🕐 Próximos turnos en espera</h3>
        <?php if ($en_espera): ?>
            <ul class="list-group mx-auto w-50 mt-3">
                <?php foreach ($en_espera as $t): ?>
                    <li class="list-group-item"><?= "N° {$t['numero']} — {$t['estado']}" ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted mt-3">No hay turnos en espera.</p>
        <?php endif; ?>
    </div>
</body>
</html>
