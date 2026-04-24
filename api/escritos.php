<?php
// =============================================================================
// Endpoint: /api/escritos.php  (biblioteca de plantillas de escrito)
// -----------------------------------------------------------------------------
// GET    /api/escritos.php                 → lista (sin eliminadas)
// GET    /api/escritos.php?since=<iso>     → sync: cambios ≥ since (incluye eliminadas)
// GET    /api/escritos.php?uuid=<uuid>     → detalle
// POST   /api/escritos.php                 → crear o upsert (por uuid si viene)
// PUT    /api/escritos.php?uuid=<uuid>     → actualizar
// DELETE /api/escritos.php?uuid=<uuid>     → soft delete
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
                $st = $pdo->prepare("SELECT uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados, creado_en, actualizado_en, eliminado_en FROM escritos_biblioteca WHERE uuid = ?");
                $st->execute([$uuid]);
                $row = $st->fetch();
                if (!$row) json_error(404, 'No encontrada.');
                json_response(200, format_row($row));
            }
            if (isset($_GET['since'])) {
                $since = parse_iso_to_datetime((string)$_GET['since']);
                if ($since === null) json_error(400, 'since inválido (ISO 8601 UTC).');
                $st = $pdo->prepare("SELECT uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados, creado_en, actualizado_en, eliminado_en FROM escritos_biblioteca WHERE actualizado_en >= ? ORDER BY actualizado_en ASC");
                $st->execute([$since]);
                $rows = array_map('format_row', $st->fetchAll());
                json_response(200, ['escritos' => $rows, 'server_time' => fmt_datetime_utc(gmdate('Y-m-d H:i:s.') . '000')]);
            }
            $rows = $pdo->query("SELECT uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados, creado_en, actualizado_en, eliminado_en FROM escritos_biblioteca WHERE eliminado_en IS NULL ORDER BY actualizado_en DESC")->fetchAll();
            json_response(200, ['escritos' => array_map('format_row', $rows)]);
            break;

        case 'POST':
            $data = read_json_body();
            $uuid = isset($data['uuid']) && is_string($data['uuid']) && is_uuid($data['uuid']) ? $data['uuid'] : uuid_v4();
            $nombre = trim((string)($data['nombre'] ?? ''));
            $suma = (string)($data['sumaPorDefecto'] ?? '');
            $cuerpo = (string)($data['cuerpoPorDefecto'] ?? '');
            $petitorio = (string)($data['petitorioPorDefecto'] ?? '');
            $estructuraJson = encode_estructura_json($data['estructuraJson'] ?? []);
            $usaDelegados = !empty($data['usaDelegados']) ? 1 : 0;
            if ($nombre === '') json_error(400, 'Falta "nombre".');

            $st = $pdo->prepare("
                INSERT INTO escritos_biblioteca (uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                  nombre = VALUES(nombre),
                  titulo = CASE WHEN VALUES(titulo) <> '' THEN VALUES(titulo) ELSE titulo END,
                  contenido = CASE WHEN VALUES(contenido) <> '' THEN VALUES(contenido) ELSE contenido END,
                  suma = VALUES(suma),
                  cuerpo = VALUES(cuerpo),
                  petitorio = VALUES(petitorio),
                  estructura_json = VALUES(estructura_json),
                  usa_delegados = VALUES(usa_delegados),
                  eliminado_en = NULL
            ");
            $st->execute([$uuid, $nombre, $nombre, $cuerpo, $suma, $cuerpo, $petitorio, $estructuraJson, $usaDelegados]);

            $st2 = $pdo->prepare("SELECT uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados, creado_en, actualizado_en, eliminado_en FROM escritos_biblioteca WHERE uuid = ?");
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
                $sets[] = 'titulo = CASE WHEN ? <> "" THEN ? ELSE titulo END'; $args[] = $nombre; $args[] = $nombre;
            }
            if (isset($data['sumaPorDefecto'])) { $sets[] = 'suma = ?'; $args[] = (string)$data['sumaPorDefecto']; }
            if (isset($data['cuerpoPorDefecto'])) { $sets[] = 'cuerpo = ?'; $args[] = (string)$data['cuerpoPorDefecto']; }
            if (isset($data['cuerpoPorDefecto'])) { $sets[] = 'contenido = CASE WHEN ? <> "" THEN ? ELSE contenido END'; $args[] = (string)$data['cuerpoPorDefecto']; $args[] = (string)$data['cuerpoPorDefecto']; }
            if (isset($data['petitorioPorDefecto'])) { $sets[] = 'petitorio = ?'; $args[] = (string)$data['petitorioPorDefecto']; }
            if (array_key_exists('estructuraJson', $data)) { $sets[] = 'estructura_json = ?'; $args[] = encode_estructura_json($data['estructuraJson']); }
            if (isset($data['usaDelegados'])) { $sets[] = 'usa_delegados = ?'; $args[] = !empty($data['usaDelegados']) ? 1 : 0; }
            if (!$sets) json_error(400, 'Nada que actualizar.');
            $args[] = $uuid;
            $st = $pdo->prepare("UPDATE escritos_biblioteca SET " . implode(', ', $sets) . " WHERE uuid = ?");
            $st->execute($args);
            if ($st->rowCount() === 0) {
                $chk = $pdo->prepare("SELECT 1 FROM escritos_biblioteca WHERE uuid = ?");
                $chk->execute([$uuid]);
                if (!$chk->fetchColumn()) json_error(404, 'No encontrada.');
            }
            $st2 = $pdo->prepare("SELECT uuid, nombre, titulo, contenido, suma, cuerpo, petitorio, estructura_json, usa_delegados, creado_en, actualizado_en, eliminado_en FROM escritos_biblioteca WHERE uuid = ?");
            $st2->execute([$uuid]);
            json_response(200, format_row($st2->fetch()));
            break;

        case 'DELETE':
            $uuid = (string)($_GET['uuid'] ?? '');
            if (!is_uuid($uuid)) json_error(400, 'uuid inválido.');
            $st = $pdo->prepare("UPDATE escritos_biblioteca SET eliminado_en = CURRENT_TIMESTAMP(3) WHERE uuid = ? AND eliminado_en IS NULL");
            $st->execute([$uuid]);
            if ($st->rowCount() === 0) {
                $chk = $pdo->prepare("SELECT eliminado_en FROM escritos_biblioteca WHERE uuid = ?");
                $chk->execute([$uuid]);
                if (!$chk->fetch()) json_error(404, 'No encontrada.');
            }
            json_response(200, ['uuid' => $uuid, 'eliminada' => true]);
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
        'nombre' => ($row['nombre'] ?? '') !== '' ? $row['nombre'] : ($row['titulo'] ?? ''),
        'sumaPorDefecto' => $row['suma'] ?? '',
        'cuerpoPorDefecto' => ($row['cuerpo'] ?? '') !== '' ? $row['cuerpo'] : ($row['contenido'] ?? ''),
        'petitorioPorDefecto' => $row['petitorio'] ?? '',
        'estructuraJson' => decode_estructura_json($row['estructura_json'] ?? null),
        'usaDelegados' => (bool)($row['usa_delegados'] ?? 0),
        'creadoEn' => fmt_datetime_utc($row['creado_en']),
        'actualizadoEn' => fmt_datetime_utc($row['actualizado_en']),
        'eliminadoEn' => fmt_datetime_utc($row['eliminado_en']),
    ];
}

function encode_estructura_json($value): string
{
    if (!is_array($value)) $value = [];
    $json = json_encode($value, JSON_UNESCAPED_UNICODE);
    if ($json === false) return '[]';
    return $json;
}

function decode_estructura_json($raw): array
{
    if ($raw === null || $raw === '') return [];
    if (is_array($raw)) return $raw;
    $arr = json_decode((string)$raw, true);
    return is_array($arr) ? $arr : [];
}
