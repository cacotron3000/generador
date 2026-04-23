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
-- Biblioteca unificada de escritos (plantillas de escrito)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS escritos_biblioteca (
  uuid            CHAR(36)            NOT NULL,
  -- Se permite guardar desde distintas partes del formulario y reutilizar
  -- el mismo registro tanto como escrito principal como otrosí.
  nombre          VARCHAR(190)        NOT NULL DEFAULT '',
  titulo          VARCHAR(190)        NOT NULL DEFAULT '',
  contenido       MEDIUMTEXT          NOT NULL,
  suma            TEXT                NOT NULL,
  cuerpo          MEDIUMTEXT          NOT NULL,
  petitorio       TEXT                NOT NULL,
  estructura_json JSON                NULL,
  usa_delegados   TINYINT(1)          NOT NULL DEFAULT 0,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_escritos_actualizado_en (actualizado_en),
  KEY idx_escritos_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Biblioteca de otrosíes (tabla dedicada)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS otrosies_biblioteca (
  uuid            CHAR(36)            NOT NULL,
  titulo          VARCHAR(190)        NOT NULL DEFAULT '',
  nombre          VARCHAR(190)        NOT NULL DEFAULT '',
  contenido       MEDIUMTEXT          NOT NULL,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_otrosies_actualizado_en (actualizado_en),
  KEY idx_otrosies_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Biblioteca de párrafos (plantillas de párrafos reutilizables)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parrafos_biblioteca (
  uuid            CHAR(36)            NOT NULL,
  nombre          VARCHAR(190)        NOT NULL,
  contenido       MEDIUMTEXT          NOT NULL,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_parrafos_actualizado_en (actualizado_en),
  KEY idx_parrafos_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Biblioteca de módulos DOCX (módulos reutilizables para composición de escrito)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS modulos (
  uuid            CHAR(36)            NOT NULL,
  nombre          VARCHAR(190)        NOT NULL,
  contenido       MEDIUMTEXT          NOT NULL,
  creado_en       DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  actualizado_en  DATETIME(3)         NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  eliminado_en    DATETIME(3)         NULL DEFAULT NULL,
  PRIMARY KEY (uuid),
  KEY idx_modulos_actualizado_en (actualizado_en),
  KEY idx_modulos_eliminado_en (eliminado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Migración para instalaciones existentes (evita errores por columnas faltantes)
-- ----------------------------------------------------------------------------
ALTER TABLE escritos_biblioteca
  ADD COLUMN IF NOT EXISTS nombre        VARCHAR(190) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS titulo        VARCHAR(190) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS contenido     MEDIUMTEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS suma          TEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS cuerpo        MEDIUMTEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS petitorio     TEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS estructura_json JSON NULL,
  ADD COLUMN IF NOT EXISTS usa_delegados TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS creado_en     DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS actualizado_en DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS eliminado_en  DATETIME(3) NULL DEFAULT NULL;

ALTER TABLE parrafos_biblioteca
  ADD COLUMN IF NOT EXISTS nombre         VARCHAR(190) NOT NULL,
  ADD COLUMN IF NOT EXISTS contenido      MEDIUMTEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS creado_en      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS actualizado_en DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS eliminado_en   DATETIME(3) NULL DEFAULT NULL;

ALTER TABLE modulos
  ADD COLUMN IF NOT EXISTS nombre         VARCHAR(190) NOT NULL,
  ADD COLUMN IF NOT EXISTS contenido      MEDIUMTEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS creado_en      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS actualizado_en DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS eliminado_en   DATETIME(3) NULL DEFAULT NULL;

ALTER TABLE otrosies_biblioteca
  ADD COLUMN IF NOT EXISTS titulo         VARCHAR(190) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS nombre         VARCHAR(190) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS contenido      MEDIUMTEXT NOT NULL,
  ADD COLUMN IF NOT EXISTS creado_en      DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS actualizado_en DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  ADD COLUMN IF NOT EXISTS eliminado_en   DATETIME(3) NULL DEFAULT NULL;
