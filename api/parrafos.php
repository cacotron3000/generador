<?php
// =============================================================================
// Endpoint: /api/parrafos.php  (biblioteca de párrafos)
// -----------------------------------------------------------------------------
// GET    /api/parrafos.php                 → lista (sin eliminadas)
// GET    /api/parrafos.php?since=<iso>     → sync: cambios ≥ since (incluye eliminadas)
// GET    /api/parrafos.php?uuid=<uuid>     → detalle
// POST   /api/parrafos.php                 → crear o upsert
// PUT    /api/parrafos.php?uuid=<uuid>     → actualizar
// DELETE /api/parrafos.php?uuid=<uuid>     → soft delete
// =============================================================================

require_once __DIR__ . '/util.php';
bootstrap();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo    = db();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['uuid'])) {
                $uuid = (string)$_GET['uuid'];
                if (!is_uuid($uuid)) json_error(400, 'uuid inválido.');
                $st = $pdo->prepare("SELECT uuid, nombre, contenido, creado_en, actualizado_en, eliminado_en FROM parrafos_biblioteca WHERE uuid = ?");
                $st->execute([$uuid]);
                $row = $st->fetch();
                if (!$row) json_error(404, 'No encontrado.');
                json_response(200, format_row($row));
            }
            if (isset($_GET['since'])) {
                $since = parse_iso_to_datetime((string)$_GET['since']);
                if ($since === null) json_error(400, 'since inválido (ISO 8601 UTC).');
                $st = $pdo->prepare("SELECT uuid, nombre, contenido, creado_en, actualizado_en, eliminado_en FROM parrafos_biblioteca WHERE actualizado_en >= ? ORDER BY actualizado_en ASC");
                $st->execute([$since]);
                json_response(200, ['parrafos' => array_map('format_row', $st->fetchAll()), 'server_time' => fmt_datetime_utc(gmdate('Y-m-d H:i:s.') . '000')]);
            }
            $rows = $pdo->query("SELECT uuid, nombre, contenido, creado_en, actualizado_en, eliminado_en FROM parrafos_biblioteca WHERE eliminado_en IS NULL ORDER BY actualizado_en DESC")->fetchAll();
            json_response(200, ['parrafos' => array_map('format_row', $rows)]);
            break;

        case 'POST':
            $data = read_json_body();
            $uuid = isset($data['uuid']) && is_string($data['uuid']) && is_uuid($data['uuid']) ? $data['uuid'] : uuid_v4();
            $nombre = trim((string)($data['nombre'] ?? ''));
            $contenido = (string)($data['contenido'] ?? '');
            if ($nombre === '') json_error(400, 'Falta "nombre".');
            if ($contenido === '') json_error(400, 'Falta "contenido".');

            $st = $pdo->prepare("\n                INSERT INTO parrafos_biblioteca (uuid, nombre, contenido)\n                VALUES (?, ?, ?)\n                ON DUPLICATE KEY UPDATE\n                  nombre = VALUES(nombre),\n                  contenido = VALUES(contenido),\n                  eliminado_en = NULL\n            ");
            $st->execute([$uuid, $nombre, $contenido]);

            $st2 = $pdo->prepare("SELECT uuid, nombre, contenido, creado_en, actualizado_en, eliminado_en FROM parrafos_biblioteca WHERE uuid = ?");
            $st2->execute([$uuid]);
            json_response(201, format_row($st2->fetch()));
            break;

        case 'PUT':
            $uuid = (string)($_GET['uuid'] ?? '');
            if (!is_uuid($uuid)) json_error(400, 'uuid inválido.');
            $data = read_json_body();
            $sets = []; $args = [];
            if (isset($data['nombre'])) {
                $nombre = trim((string)$data['nombre']);
                if ($nombre === '') json_error(400, '"nombre" no puede estar vacío.');
                $sets[] = 'nombre = ?'; $args[] = $nombre;
            }
            if (isset($data['contenido'])) {
                $contenido = (string)$data['contenido'];
                if ($contenido === '') json_error(400, '"contenido" no puede estar vacío.');
                $sets[] = 'contenido = ?'; $args[] = $contenido;
            }
            if (!$sets) json_error(400, 'Nada que actualizar.');
            $args[] = $uuid;
            $st = $pdo->prepare("UPDATE parrafos_biblioteca SET " . implode(', ', $sets) . " WHERE uuid = ?");
            $st->execute($args);
            if ($st->rowCount() === 0) {
                $chk = $pdo->prepare("SELECT 1 FROM parrafos_biblioteca WHERE uuid = ?");
                $chk->execute([$uuid]);
                if (!$chk->fetchColumn()) json_error(404, 'No encontrado.');
            }
            $st2 = $pdo->prepare("SELECT uuid, nombre, contenido, creado_en, actualizado_en, eliminado_en FROM parrafos_biblioteca WHERE uuid = ?");
            $st2->execute([$uuid]);
            json_response(200, format_row($st2->fetch()));
            break;

        case 'DELETE':
            $uuid = (string)($_GET['uuid'] ?? '');
            if (!is_uuid($uuid)) json_error(400, 'uuid inválido.');
            $st = $pdo->prepare("UPDATE parrafos_biblioteca SET eliminado_en = CURRENT_TIMESTAMP(3) WHERE uuid = ? AND eliminado_en IS NULL");
            $st->execute([$uuid]);
            if ($st->rowCount() === 0) {
                $chk = $pdo->prepare("SELECT eliminado_en FROM parrafos_biblioteca WHERE uuid = ?");
                $chk->execute([$uuid]);
                if (!$chk->fetch()) json_error(404, 'No encontrado.');
            }
            json_response(200, ['uuid' => $uuid, 'eliminado' => true]);
            break;

        default:
            header('Allow: GET, POST, PUT, DELETE, OPTIONS');
            json_error(405, 'Método no permitido.');
    }
} catch (PDOException $e) {
    json_error(500, 'Error de base de datos', $e->getMessage());
} catch (Throwable $e) {
    json_error(500, 'Error interno', $e->getMessage());
}

function format_row(array $row): array
{
    return [
        'uuid' => $row['uuid'],
        'nombre' => $row['nombre'] ?? '',
        'contenido' => $row['contenido'] ?? '',
        'creadoEn' => fmt_datetime_utc($row['creado_en']),
        'actualizadoEn' => fmt_datetime_utc($row['actualizado_en']),
        'eliminadoEn' => fmt_datetime_utc($row['eliminado_en']),
    ];
}
