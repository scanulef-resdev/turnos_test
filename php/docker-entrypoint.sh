#!/bin/bash
set -e

# Si el index.php no existe en el volumen, copiamos el código fuente
if [ ! -f /var/www/html/index.php ]; then
  echo "🟢 Copiando código fuente al volumen..."
  cp -r /app_src/* /var/www/html/
fi

echo "🚀 Iniciando Apache..."
exec "$@"
