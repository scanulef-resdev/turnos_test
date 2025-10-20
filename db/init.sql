CREATE TABLE IF NOT EXISTS turnos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero INT NOT NULL,
  codigo VARCHAR(10),
  tipo ENUM('compras', 'despacho') NOT NULL,
  estado ENUM('espera', 'atendiendo', 'atendido', 'ausente') DEFAULT 'espera',
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);
