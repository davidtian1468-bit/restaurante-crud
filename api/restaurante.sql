CREATE DATABASE IF NOT EXISTS restaurante CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE restaurante;

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  categoria VARCHAR(30) NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  precio DECIMAL(8,2) NOT NULL,
  stock INT NOT NULL
);

INSERT INTO productos (nombre, categoria, descripcion, precio, stock) VALUES
('Mixto prime (1000g)', 'Carnes', 'Corte preciso, sabor excepcional y el fuerte aroma de la leña.', 65.00, 10),
('Chorizo Fangus (400g)', 'Carnes', 'Tradición peruana, jugosidad suprema y marmoleado excepcional.', 38.00, 15),
('Provoleta la esencia Especial', 'Entradas', 'Queso provolone dorado por fuera, derretido por dentro con hierbas de la huerta.', 16.00, 20),
('Chorizos Criollos al Fuego', 'Entradas', 'Servidos con salsa criolla fresca y pan de campo caliente.', 14.00, 25),
('Parrillada de Vegetales', 'Entradas', 'Sazonados con sal de mar, romero fresco y reducción de aceto balsámico.', 12.00, 18),
('Tiramisú Artesanal', 'Postres', 'Receta italiana clásica, suave y cremosa, con un toque de café intenso.', 10.00, 12),
('Volcán de Chocolate', 'Postres', 'Centro fundido de chocolate belga, servido caliente con helado.', 11.00, 12),
('Malbec Gran Reserva', 'Licores', 'Notas de ciruela, vainilla y roble francés. Maridaje perfecto.', 75.00, 8),
('Whisky Single Malt', 'Licores', 'Añejado 12 años, notas ahumadas y un final cálido y prolongado.', 18.00, 30);
