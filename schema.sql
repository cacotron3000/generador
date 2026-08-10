-- ============================================================================
-- Schema base para Generador de escritos
-- Compatible con MySQL 8+ / MariaDB 10.3+
-- ============================================================================

-- Recomendado ejecutar antes:
--   CREATE DATABASE nombre_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE nombre_bd;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ----------------------------------------------------------------------------
-- Plantillas de causa (encabezado)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS plantillas (
  uuid            CHAR(36)            NOT NULL,
  nombre          VARCHAR(160)        NOT NULL,
  encabezado_json JSON                NOT NULL,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_plantillas_actualizado_en (actualizado_en),
  KEY idx_plantillas_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Biblioteca de otrosíes
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS otrosies_biblioteca (
  uuid            CHAR(36)            NOT NULL,
  titulo          VARCHAR(190)        NOT NULL,
  contenido       MEDIUMTEXT          NOT NULL,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_otrosies_actualizado_en (actualizado_en),
  KEY idx_otrosies_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Biblioteca de plantillas de escrito (tipo de escrito personalizado)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS escritos_biblioteca (
  uuid            CHAR(36)            NOT NULL,
  nombre          VARCHAR(190)        NOT NULL,
  suma            TEXT                NOT NULL,
  cuerpo          MEDIUMTEXT          NOT NULL,
  petitorio       TEXT                NOT NULL,
  usa_delegados   TINYINT(1)          NOT NULL DEFAULT 0,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_escritos_actualizado_en (actualizado_en),
  KEY idx_escritos_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
