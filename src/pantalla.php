<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pantalla de Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Fondo blanco con texto principal en negro */
        body { background: #ffffff; color: #000000; text-align: center; font-family: Arial, sans-serif; }
        /* Turno grande: rojo fuerte para destacar */
        .turno-actual { font-size: 5rem; font-weight: bold; color: #c31010; margin-top: 2rem; }
        /* Etiqueta del tipo */
        .tipo { font-size: 2rem; margin-bottom: 1rem; color: #8a0b0b; }
        .historial { margin-top: 3rem; }
        .historial-item { font-size: 1.5rem; margin: 0.3rem; color: #333; }
        /* Tipos específicos usan acentos rojos más suaves o bordes */
        .compras { color: #b71c1c; }
        .despacho { color: #d32f2f; }

        /* Encabezados y mensajes */
        .title-main { color: #c31010; }
        .muted { color: #555 !important; }
        .alert-connection { color: #a30000; font-weight: bold; }

        /* Mejora de contraste para 'turno-actual' cuando hay fondo blanco */
        .turno-actual { text-shadow: 0 1px 0 rgba(255,255,255,0.6); }
    </style>
</head>
<body>
    <div class="mt-3">
        <h1 class="title-main">Pantalla Turnos v1</h1>
        <h4 id="reloj" class="muted"></h4>
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
                html += `<h2 class="muted mt-5">No hay turnos en atención</h2>`;
            }

            html += `<div class="historial"><h3 class="mb-3 title-main">Últimos turnos llamados</h3>`;
            if (historial.length > 0) {
                historial.forEach(h => {
                    html += `<div class="historial-item ${h.tipo}">${h.tipo.charAt(0).toUpperCase() + h.tipo.slice(1)} — ${h.codigo} (${h.estado})</div>`;
                });
            } else {
                html += `<p class="muted">Sin historial disponible</p>`;
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
