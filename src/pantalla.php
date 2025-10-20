<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pantalla de Turnos — Repuestos España</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paleta: gradiente rojo de fondo, paneles blancos y acentos rojos */
        :root{
            --red-600: #d43a3a;
            --red-700: #c62828;
            --red-800: #b71c1c;
            --accent-dark: #8a0b0b;
            --muted: #555555;
            --card-bg: #ffffff;
            --active-green: #27ae60;
        }

        html,body{height:100%;}
        body {
            background: linear-gradient(135deg, var(--red-700) 0%, #ef9a9a 60%);
            color: #111;
            text-align: center;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            padding-bottom: 3rem;
        }

        /* Encabezado grande similar a la imagen */
        .page-header {
            padding: 2.5rem 1rem 1.25rem 1rem;
            text-align: center;
        }
        .page-header .title-main {
            color: #ffffff;
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0.25rem 0 0.5rem 0;
        }

        /* Panel principal blanco con sombra para el reloj/estadísticas */
        .panel {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            margin: 0 auto;
            max-width: 1100px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        }

        /* Barra de alerta crítica roja */
        .alert-critical {
            background: var(--red-800);
            color: #fff;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin: 1.75rem auto;
            max-width: 1100px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Contenedor de contenido con columnas centradas */
        #contenido { max-width: 1100px; margin: 1.5rem auto; }

        /* Turno grande: rojo fuerte para destacar */
        .turno-actual { font-size: 5rem; font-weight: 800; color: var(--red-800); margin-top: 1rem; }

        /* Etiqueta del tipo */
        .tipo { font-size: 1.6rem; margin-bottom: 0.6rem; color: var(--accent-dark); font-weight:600; }

        .historial { margin-top: 2rem; display: grid; grid-template-columns: repeat(auto-fill,minmax(320px,1fr)); gap: 1rem; }

        .historial-item {
            font-size: 1.1rem;
            margin: 0;
            color: #333;
            background: var(--card-bg);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            text-align: left;
        }

        /* Tipos específicos usan acentos rojos */
        .compras { color: var(--red-700); }
        .despacho { color: var(--red-600); }

        /* Tarjeta destacada cuando el turno está activo */
        .historial-item.atendiendo {
            border-left: 6px solid var(--active-green);
        }

        .muted { color: var(--muted) !important; }

        /* Ajustes responsivos */
        @media (max-width: 600px){
            .turno-actual { font-size: 3.2rem; }
            .page-header .title-main { font-size: 1.5rem; }
        }
    </style>
    </head>
<body>
    <header class="page-header">
        <!-- Logo/encabezado similar al diseño -->
        <img src="/logo.png" alt="Repuestos España" style="max-width:160px; display:block; margin:0.25rem auto;" onerror="this.style.display='none'">
        <div class="title-main">Sistema de Gestión de Pickeadores Repuestos España</div>
    </header>

    <main>
        <div class="panel mt-2">
            <div class="row align-items-center">
                <div class="col-md-4 text-left">
                    <div class="muted">Hora Actual</div>
                    <div id="reloj" class="h3 mt-1" style="font-weight:700;color:var(--red-800);"></div>
                    <div class="muted" id="fecha"></div>
                </div>
                <div class="col-md-8 text-right">
                    <!-- espacio para estadísticas si aplica -->
                </div>
            </div>
        </div>

        <div class="alert-critical" id="alerta-critica" style="display:none;">⚠️ ALERTA CRÍTICA: Faltan pickeadores</div>

        <div id="contenido" class="mt-4 text-center">
            <h2 class="muted">Esperando actualizaciones...</h2>
        </div>
    </main>

    <script>
        // 🕐 Reloj en tiempo real
        function actualizarReloj() {
            const ahora = new Date();
            const fecha = ahora.toLocaleDateString('es-CL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const hora = ahora.toLocaleTimeString('es-CL');
            const reloj = document.getElementById('reloj');
            const fechaEl = document.getElementById('fecha');
            if(reloj) reloj.textContent = hora;
            if(fechaEl) fechaEl.textContent = fecha;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // 🔄 Conexión en tiempo real con SSE
        const contenido = document.getElementById('contenido');
        const source = new EventSource('stream_turnos.php');

        source.addEventListener('update', e => {
            const data = JSON.parse(e.data);
            let html = '';

            // si el servidor manda un objeto con 'atendiendo' y 'historial', lo soportamos
            const atendiendo = Array.isArray(data) ? data.filter(t => t.estado === 'atendiendo') : (data.atendiendo || []);
            const historial = Array.isArray(data) ? data : (data.historial || []);

            // Mostrar alerta crítica si viene en el payload o si algún registro tiene estado 'critico'
            const alertaEl = document.getElementById('alerta-critica');
            const tieneCritico = (Array.isArray(data) && data.some(t => t.estado === 'critico')) || (!!data.alertaCritica);
            if(alertaEl) alertaEl.style.display = tieneCritico ? 'block' : 'none';

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
                    const clase = h.estado === 'atendiendo' ? 'historial-item atendiendo' : 'historial-item';
                    html += `<div class="${clase} ${h.tipo}"><strong>${h.tipo.charAt(0).toUpperCase() + h.tipo.slice(1)}</strong> — ${h.codigo} <span class="muted">(${h.estado})</span></div>`;
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
