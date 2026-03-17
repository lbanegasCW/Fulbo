SET NAMES utf8mb4;

INSERT INTO roles (id, name, slug) VALUES
(1, 'Administrador', 'admin'),
(2, 'Jugador', 'jugador');

INSERT INTO usuarios (id, nombre, username, rol_id, activo, pin_hash, requiere_activacion) VALUES
(1, 'Admin Fulbo', 'admin', 1, 1, '$2y$10$.u9RSOL1plOApWljpEr3CeP8ibF0O3MhiBmRhBV9dD/bv2E3wJzV6', 0),
(2, 'Nico', 'nico', 2, 1, '$2y$10$.u9RSOL1plOApWljpEr3CeP8ibF0O3MhiBmRhBV9dD/bv2E3wJzV6', 0),
(3, 'Maxi', 'maxi', 2, 1, '$2y$10$.u9RSOL1plOApWljpEr3CeP8ibF0O3MhiBmRhBV9dD/bv2E3wJzV6', 0),
(4, 'Luis', 'luis', 2, 1, NULL, 1);

INSERT INTO bombos (id, nombre, descripcion, activo) VALUES
(1, 'Bombo Europeo', 'Clubes top de Europa', 1),
(2, 'Bombo Argentino', 'Clubes fuertes de Argentina', 1);

INSERT INTO equipos (bombo_id, nombre, abreviatura, activo) VALUES
(1, 'Barcelona', 'BAR', 1),
(1, 'Liverpool', 'LIV', 1),
(1, 'Bayern Munich', 'BAY', 1),
(1, 'Real Madrid', 'RMA', 1),
(1, 'Inter', 'INT', 1),
(2, 'Boca Juniors', 'BOC', 1),
(2, 'River Plate', 'RIV', 1),
(2, 'San Lorenzo', 'SLO', 1),
(2, 'Racing', 'RAC', 1),
(2, 'Independiente', 'IND', 1);
