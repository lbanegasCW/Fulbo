-- Fulbo schema - MySQL 8+
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS auditoria_basica;
DROP TABLE IF EXISTS campeones_torneo;
DROP TABLE IF EXISTS partidos;
DROP TABLE IF EXISTS rondas;
DROP TABLE IF EXISTS elecciones_equipos;
DROP TABLE IF EXISTS ofertas_equipos_turno;
DROP TABLE IF EXISTS sorteo_torneo;
DROP TABLE IF EXISTS torneo_bombos;
DROP TABLE IF EXISTS torneo_jugadores;
DROP TABLE IF EXISTS torneos;
DROP TABLE IF EXISTS equipos;
DROP TABLE IF EXISTS bombos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  slug VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  rol_id TINYINT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  pin_hash VARCHAR(255) NULL,
  requiere_activacion TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_login_at DATETIME NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE bombos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE equipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bombo_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  abreviatura VARCHAR(6) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_equipos_nombre_bombo (bombo_id, nombre),
  CONSTRAINT fk_equipos_bombo FOREIGN KEY (bombo_id) REFERENCES bombos(id)
);

CREATE TABLE torneos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  anio SMALLINT UNSIGNED NOT NULL,
  rondas_iniciales TINYINT UNSIGNED NOT NULL DEFAULT 1,
  inicio_programado_at DATETIME NULL,
  estado ENUM('borrador','sorteo_pendiente','eleccion_equipos','en_juego','desempate','finalizado') NOT NULL DEFAULT 'borrador',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_torneos_anio (anio),
  INDEX idx_torneos_inicio_programado (inicio_programado_at)
);

CREATE TABLE torneo_jugadores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_torneo_jugador (torneo_id, usuario_id),
  CONSTRAINT fk_tj_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_tj_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE torneo_bombos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  bombo_id INT UNSIGNED NOT NULL,
  equipos_por_jugador TINYINT UNSIGNED NOT NULL DEFAULT 1,
  oferta_por_turno TINYINT UNSIGNED NOT NULL DEFAULT 3,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_torneo_bombo (torneo_id, bombo_id),
  CONSTRAINT fk_tb_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_tb_bombo FOREIGN KEY (bombo_id) REFERENCES bombos(id)
);

CREATE TABLE sorteo_torneo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  posicion SMALLINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_sorteo_pos (torneo_id, posicion),
  UNIQUE KEY uk_sorteo_user (torneo_id, usuario_id),
  CONSTRAINT fk_st_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_st_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE ofertas_equipos_turno (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  bombo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  numero_turno INT UNSIGNED NOT NULL,
  equipos_ofrecidos_json JSON NULL,
  estado ENUM('pendiente','seleccionado','expirado') NOT NULL DEFAULT 'pendiente',
  equipo_seleccionado_id INT UNSIGNED NULL,
  fecha_seleccion DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_turno_numero (torneo_id, numero_turno),
  CONSTRAINT fk_oet_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_oet_bombo FOREIGN KEY (bombo_id) REFERENCES bombos(id),
  CONSTRAINT fk_oet_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_oet_equipo FOREIGN KEY (equipo_seleccionado_id) REFERENCES equipos(id)
);

CREATE TABLE elecciones_equipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  bombo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  equipo_id INT UNSIGNED NOT NULL,
  oferta_turno_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_torneo_equipo (torneo_id, equipo_id),
  CONSTRAINT fk_ele_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_ele_bombo FOREIGN KEY (bombo_id) REFERENCES bombos(id),
  CONSTRAINT fk_ele_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_ele_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id),
  CONSTRAINT fk_ele_turno FOREIGN KEY (oferta_turno_id) REFERENCES ofertas_equipos_turno(id)
);

CREATE TABLE rondas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  numero SMALLINT UNSIGNED NOT NULL,
  tipo ENUM('normal','desempate') NOT NULL DEFAULT 'normal',
  estado ENUM('pendiente','en_juego','finalizada') NOT NULL DEFAULT 'pendiente',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rondas_torneo_tipo (torneo_id, tipo),
  CONSTRAINT fk_rondas_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id)
);

CREATE TABLE partidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  ronda_id INT UNSIGNED NOT NULL,
  jugador_local_id INT UNSIGNED NOT NULL,
  jugador_visitante_id INT UNSIGNED NOT NULL,
  equipo_local VARCHAR(180) NOT NULL,
  equipo_visitante VARCHAR(180) NOT NULL,
  goles_local TINYINT UNSIGNED NULL,
  goles_visitante TINYINT UNSIGNED NULL,
  estado ENUM('pendiente','jugado','validado') NOT NULL DEFAULT 'pendiente',
  fecha_carga DATETIME NULL,
  cargado_por INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_partidos_torneo (torneo_id),
  INDEX idx_partidos_estado (estado),
  CONSTRAINT fk_partidos_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_partidos_ronda FOREIGN KEY (ronda_id) REFERENCES rondas(id),
  CONSTRAINT fk_partidos_local FOREIGN KEY (jugador_local_id) REFERENCES usuarios(id),
  CONSTRAINT fk_partidos_visitante FOREIGN KEY (jugador_visitante_id) REFERENCES usuarios(id),
  CONSTRAINT fk_partidos_cargado FOREIGN KEY (cargado_por) REFERENCES usuarios(id)
);

CREATE TABLE campeones_torneo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  fecha_cierre DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_campeon_torneo (torneo_id),
  INDEX idx_campeon_usuario (usuario_id),
  CONSTRAINT fk_campeon_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id),
  CONSTRAINT fk_campeon_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

DROP VIEW IF EXISTS ranking_anual;

SET FOREIGN_KEY_CHECKS = 1;
