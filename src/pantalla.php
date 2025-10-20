<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pantalla de Turnos — Repuestos España</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #111; color: #fff; text-align: center; font-family: Arial, sans-serif; }
        .turno-actual { font-size: 5rem; font-weight: bold; color: #00ff88; margin-top: 2rem; }
        .tipo { font-size: 2rem; margin-bottom: 1rem; }
        .historial { margin-top: 3rem; }
        .historial-item { font-size: 1.5rem; margin: 0.3rem; }
        .compras { color: #00b4d8; }
        .despacho { color: #ffd60a; }
    </style>
</head>
<body>
    <div class="mt-3">
        <h1 class="text-info">🖥️ Pantalla Pública de Turnos</h1>
        <h4 id="reloj" class="text-secondary"></h4>
    </div>

    <div id="contenido" class="mt-4 text-center">
        <h2 class="text-secondary">Esperando actualizaciones...</h2>
    </div>

    <script>
        // 🕐 Reloj en tiempo real
        function actualizarReloj() {
            const ahora = new Date();
            const fecha = ahora.toLocaleDateString('es-CL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const hora = ahora.toLocaleTimeString('es-CL');
            document.getElementById('reloj').textContent = `${fecha} — ${hora}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // 🔄 Conexión en tiempo real con SSE
        const contenido = document.getElementById('contenido');
        const source = new EventSource('stream_turnos.php');

        source.addEventListener('update', e => {
            const data = JSON.parse(e.data);
            let html = '';

            const atendiendo = data.filter(t => t.estado === 'atendiendo');
            const historial = data;

            if (atendiendo.length > 0) {
                atendiendo.forEach(t => {
                    html += `
                        <div class="tipo">${t.tipo.charAt(0).toUpperCase() + t.tipo.slice(1)}</div>
                        <div class="turno-actual ${t.tipo}">${t.codigo}</div>
                    `;
                });
            } else {
                html += `<h2 class="text-secondary mt-5">No hay turnos en atención</h2>`;
            }

            html += `<div class="historial"><h3 class="mb-3 text-info">Últimos turnos llamados</h3>`;
            if (historial.length > 0) {
                historial.forEach(h => {
                    html += `<div class="historial-item ${h.tipo}">${h.tipo.charAt(0).toUpperCase() + h.tipo.slice(1)} — ${h.codigo} (${h.estado})</div>`;
                });
            } else {
                html += `<p class="text-secondary">Sin historial disponible</p>`;
            }

            html += `</div>`;
            contenido.innerHTML = html;
        });

        source.onerror = () => {
            contenido.innerHTML = `<h2 class="text-danger">❌ Conexión perdida con el servidor</h2>`;
        };
    </script>
</body>

</html>
