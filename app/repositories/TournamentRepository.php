<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class TournamentRepository extends BaseRepository
{
    private const ACTIVE_STATES = ['sorteo_pendiente', 'eleccion_equipos', 'en_juego', 'desempate'];

    public function all(): array
    {
        return $this->db->query('SELECT * FROM torneos ORDER BY fecha_creacion DESC')->fetchAll();
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*
             FROM torneos t
             INNER JOIN torneo_jugadores tj ON tj.torneo_id = t.id
             WHERE tj.usuario_id = :user_id
             ORDER BY t.fecha_creacion DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function activeForUser(int $userId): ?array
    {
        $placeholders = implode(',', array_fill(0, count(self::ACTIVE_STATES), '?'));
        $sql = 'SELECT t.*
                FROM torneos t
                INNER JOIN torneo_jugadores tj ON tj.torneo_id = t.id
                WHERE tj.usuario_id = ?
                  AND t.estado IN (' . $placeholders . ')
                ORDER BY t.fecha_creacion DESC
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$userId], self::ACTIVE_STATES);
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    public function activeGlobal(): ?array
    {
        $placeholders = implode(',', array_fill(0, count(self::ACTIVE_STATES), '?'));
        $sql = 'SELECT *
                FROM torneos
                WHERE estado IN (' . $placeholders . ')
                ORDER BY fecha_creacion DESC
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(self::ACTIVE_STATES);

        return $stmt->fetch() ?: null;
    }

    public function wonByUserAndYear(int $userId, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*
             FROM campeones_torneo ct
             INNER JOIN torneos t ON t.id = ct.torneo_id
             WHERE ct.usuario_id = :usuario_id
               AND t.anio = :anio
             ORDER BY t.fecha_creacion DESC'
        );
        $stmt->execute([
            'usuario_id' => $userId,
            'anio' => $year,
        ]);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM torneos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO torneos (nombre, anio, rondas_iniciales, inicio_programado_at, estado)
             VALUES (:nombre, :anio, :rondas_iniciales, :inicio_programado_at, :estado)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function nextScheduledForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*
             FROM torneos t
             INNER JOIN torneo_jugadores tj ON tj.torneo_id = t.id
             WHERE tj.usuario_id = :user_id
               AND t.inicio_programado_at IS NOT NULL
             ORDER BY
                (t.inicio_programado_at >= CURRENT_TIMESTAMP) DESC,
                CASE WHEN t.inicio_programado_at >= CURRENT_TIMESTAMP THEN t.inicio_programado_at END ASC,
                CASE WHEN t.inicio_programado_at < CURRENT_TIMESTAMP THEN t.inicio_programado_at END DESC
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function nextScheduledGlobal(): ?array
    {
        $stmt = $this->db->query(
            'SELECT t.*
             FROM torneos t
             WHERE t.inicio_programado_at IS NOT NULL
             ORDER BY
                (t.inicio_programado_at >= CURRENT_TIMESTAMP) DESC,
                CASE WHEN t.inicio_programado_at >= CURRENT_TIMESTAMP THEN t.inicio_programado_at END ASC,
                CASE WHEN t.inicio_programado_at < CURRENT_TIMESTAMP THEN t.inicio_programado_at END DESC
             LIMIT 1'
        );

        return $stmt->fetch() ?: null;
    }

    public function recentFinishedForUser(int $userId, int $limit = 6): array
    {
        $safeLimit = max(1, min($limit, 20));
        $stmt = $this->db->prepare(
            'SELECT DISTINCT t.*
             FROM torneos t
             INNER JOIN torneo_jugadores tj ON tj.torneo_id = t.id
             WHERE tj.usuario_id = :user_id
               AND t.estado = "finalizado"
             ORDER BY COALESCE(t.inicio_programado_at, t.fecha_actualizacion, t.fecha_creacion) DESC
             LIMIT ' . $safeLimit
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function recentFinishedGlobal(int $limit = 6): array
    {
        $safeLimit = max(1, min($limit, 20));
        $stmt = $this->db->query(
            'SELECT t.*
             FROM torneos t
             WHERE t.estado = "finalizado"
             ORDER BY COALESCE(t.inicio_programado_at, t.fecha_actualizacion, t.fecha_creacion) DESC
             LIMIT ' . $safeLimit
        );

        return $stmt->fetchAll();
    }

    public function updateState(int $id, string $state): void
    {
        $stmt = $this->db->prepare('UPDATE torneos SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id, 'estado' => $state]);
    }

    public function updateBasicData(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE torneos
             SET nombre = :nombre,
                 anio = :anio,
                 rondas_iniciales = :rondas_iniciales,
                 inicio_programado_at = :inicio_programado_at,
                 fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'anio' => $data['anio'],
            'rondas_iniciales' => $data['rondas_iniciales'],
            'inicio_programado_at' => $data['inicio_programado_at'],
        ]);
    }

    public function attachPlayers(int $tournamentId, array $playerIds): void
    {
        $stmt = $this->db->prepare('INSERT INTO torneo_jugadores (torneo_id, usuario_id) VALUES (:torneo_id, :usuario_id)');
        foreach ($playerIds as $playerId) {
            $stmt->execute([
                'torneo_id' => $tournamentId,
                'usuario_id' => (int) $playerId,
            ]);
        }
    }

    public function attachPots(int $tournamentId, array $pots): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO torneo_bombos (torneo_id, bombo_id, equipos_por_jugador, oferta_por_turno)
             VALUES (:torneo_id, :bombo_id, :equipos_por_jugador, :oferta_por_turno)'
        );
        foreach ($pots as $pot) {
            $stmt->execute([
                'torneo_id' => $tournamentId,
                'bombo_id' => (int) $pot['bombo_id'],
                'equipos_por_jugador' => (int) $pot['equipos_por_jugador'],
                'oferta_por_turno' => (int) $pot['oferta_por_turno'],
            ]);
        }
    }

    public function players(int $tournamentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.username
             FROM torneo_jugadores tj
             INNER JOIN usuarios u ON u.id = tj.usuario_id
             WHERE tj.torneo_id = :torneo_id
             ORDER BY u.nombre'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetchAll();
    }

    public function pots(int $tournamentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT tb.*, b.nombre
             FROM torneo_bombos tb
             INNER JOIN bombos b ON b.id = tb.bombo_id
             WHERE tb.torneo_id = :torneo_id
             ORDER BY tb.id'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetchAll();
    }

    public function saveDrawOrder(int $tournamentId, array $orderedUserIds): void
    {
        $this->db->prepare('DELETE FROM sorteo_torneo WHERE torneo_id = :torneo_id')->execute(['torneo_id' => $tournamentId]);
        $stmt = $this->db->prepare(
            'INSERT INTO sorteo_torneo (torneo_id, usuario_id, posicion)
             VALUES (:torneo_id, :usuario_id, :posicion)'
        );
        $position = 1;
        foreach ($orderedUserIds as $userId) {
            $stmt->execute([
                'torneo_id' => $tournamentId,
                'usuario_id' => $userId,
                'posicion' => $position++,
            ]);
        }
    }

    public function drawOrder(int $tournamentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.posicion, u.id AS usuario_id, u.nombre
             FROM sorteo_torneo s
             INNER JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.torneo_id = :torneo_id
             ORDER BY s.posicion'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetchAll();
    }

    public function createTurn(int $tournamentId, int $potId, int $userId, int $turnNumber): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ofertas_equipos_turno (torneo_id, bombo_id, usuario_id, numero_turno, estado)
             VALUES (:torneo_id, :bombo_id, :usuario_id, :numero_turno, :estado)'
        );
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'bombo_id' => $potId,
            'usuario_id' => $userId,
            'numero_turno' => $turnNumber,
            'estado' => 'pendiente',
        ]);
    }

    public function currentTurn(int $tournamentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ot.*, b.nombre AS bombo_nombre, u.nombre AS jugador_nombre
             FROM ofertas_equipos_turno ot
             INNER JOIN bombos b ON b.id = ot.bombo_id
             INNER JOIN usuarios u ON u.id = ot.usuario_id
             WHERE ot.torneo_id = :torneo_id AND ot.estado = "pendiente"
             ORDER BY ot.numero_turno
             LIMIT 1'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetch() ?: null;
    }

    public function nextTurn(int $tournamentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ot.*, u.nombre AS jugador_nombre
             FROM ofertas_equipos_turno ot
             INNER JOIN usuarios u ON u.id = ot.usuario_id
             WHERE ot.torneo_id = :torneo_id AND ot.estado = "pendiente"
             ORDER BY ot.numero_turno
             LIMIT 1 OFFSET 1'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetch() ?: null;
    }

    public function updateTurnOffer(int $turnId, array $teamIds): void
    {
        $stmt = $this->db->prepare('UPDATE ofertas_equipos_turno SET equipos_ofrecidos_json = :json WHERE id = :id');
        $stmt->execute([
            'id' => $turnId,
            'json' => json_encode($teamIds),
        ]);
    }

    public function completeTurn(int $turnId, int $selectedTeamId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE ofertas_equipos_turno
             SET estado = "seleccionado", equipo_seleccionado_id = :equipo_id, fecha_seleccion = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $turnId,
            'equipo_id' => $selectedTeamId,
        ]);
    }

    public function saveSelection(int $tournamentId, int $potId, int $userId, int $teamId, int $turnId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO elecciones_equipos (torneo_id, bombo_id, usuario_id, equipo_id, oferta_turno_id)
             VALUES (:torneo_id, :bombo_id, :usuario_id, :equipo_id, :oferta_turno_id)'
        );
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'bombo_id' => $potId,
            'usuario_id' => $userId,
            'equipo_id' => $teamId,
            'oferta_turno_id' => $turnId,
        ]);
    }

    public function selectionsByTournament(int $tournamentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ele.*, u.nombre AS jugador_nombre, e.nombre AS equipo_nombre, b.nombre AS bombo_nombre
             FROM elecciones_equipos ele
             INNER JOIN usuarios u ON u.id = ele.usuario_id
             INNER JOIN equipos e ON e.id = ele.equipo_id
             INNER JOIN bombos b ON b.id = ele.bombo_id
             WHERE ele.torneo_id = :torneo_id
             ORDER BY ele.id'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetchAll();
    }

    public function allTurnsSelected(int $tournamentId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS pending FROM ofertas_equipos_turno WHERE torneo_id = :torneo_id AND estado = "pendiente"');
        $stmt->execute(['torneo_id' => $tournamentId]);
        $row = $stmt->fetch();
        return (int) $row['pending'] === 0;
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    public function createRound(int $tournamentId, int $number, string $type): int
    {
        $stmt = $this->db->prepare('INSERT INTO rondas (torneo_id, numero, tipo, estado) VALUES (:torneo_id, :numero, :tipo, :estado)');
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'numero' => $number,
            'tipo' => $type,
            'estado' => 'pendiente',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createMatch(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO partidos (torneo_id, ronda_id, jugador_local_id, jugador_visitante_id, equipo_local, equipo_visitante, estado)
             VALUES (:torneo_id, :ronda_id, :jugador_local_id, :jugador_visitante_id, :equipo_local, :equipo_visitante, :estado)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function matches(int $tournamentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, r.numero AS ronda_numero, r.tipo AS ronda_tipo,
                    ul.nombre AS local_nombre, uv.nombre AS visitante_nombre
             FROM partidos p
             INNER JOIN rondas r ON r.id = p.ronda_id
             INNER JOIN usuarios ul ON ul.id = p.jugador_local_id
             INNER JOIN usuarios uv ON uv.id = p.jugador_visitante_id
             WHERE p.torneo_id = :torneo_id
             ORDER BY r.numero, p.id'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetchAll();
    }

    public function matchById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM partidos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function nextPendingMatch(int $tournamentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.torneo_id, p.jugador_local_id, p.jugador_visitante_id, r.numero AS ronda_numero, r.tipo AS ronda_tipo
             FROM partidos p
             INNER JOIN rondas r ON r.id = p.ronda_id
             WHERE p.torneo_id = :torneo_id
               AND p.estado = "pendiente"
             ORDER BY
               CASE WHEN r.tipo = "normal" THEN 0 ELSE 1 END,
               r.numero,
               p.id
             LIMIT 1'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetch() ?: null;
    }

    public function nextPendingRound(int $tournamentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id AS ronda_id, r.numero AS ronda_numero, r.tipo AS ronda_tipo
             FROM partidos p
             INNER JOIN rondas r ON r.id = p.ronda_id
             WHERE p.torneo_id = :torneo_id
               AND p.estado = "pendiente"
             ORDER BY
               CASE WHEN r.tipo = "normal" THEN 0 ELSE 1 END,
               r.numero,
               p.id
             LIMIT 1'
        );
        $stmt->execute(['torneo_id' => $tournamentId]);
        return $stmt->fetch() ?: null;
    }

    public function updateMatchResult(int $matchId, int $homeGoals, int $awayGoals, int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE partidos
             SET goles_local = :goles_local,
                 goles_visitante = :goles_visitante,
                 estado = "jugado",
                 fecha_carga = CURRENT_TIMESTAMP,
                 cargado_por = :cargado_por
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $matchId,
            'goles_local' => $homeGoals,
            'goles_visitante' => $awayGoals,
            'cargado_por' => $userId,
        ]);
    }

    public function standings(int $tournamentId): array
    {
        $sql = 'SELECT
                    u.id AS usuario_id,
                    u.nombre,
                    SUM(CASE WHEN stats.resultado = "G" THEN 3 WHEN stats.resultado = "E" THEN 1 ELSE 0 END) AS puntos,
                    COUNT(*) AS pj,
                    SUM(stats.resultado = "G") AS pg,
                    SUM(stats.resultado = "E") AS pe,
                    SUM(stats.resultado = "P") AS pp,
                    SUM(stats.gf) AS gf,
                    SUM(stats.gc) AS gc,
                    SUM(stats.gf) - SUM(stats.gc) AS dg
                FROM (
                    SELECT p.jugador_local_id AS usuario_id,
                           CASE WHEN p.goles_local > p.goles_visitante THEN "G"
                                WHEN p.goles_local = p.goles_visitante THEN "E"
                                ELSE "P" END AS resultado,
                           p.goles_local AS gf,
                           p.goles_visitante AS gc
                    FROM partidos p
                    WHERE p.torneo_id = :torneo_id AND p.estado = "jugado"
                    UNION ALL
                    SELECT p.jugador_visitante_id AS usuario_id,
                           CASE WHEN p.goles_visitante > p.goles_local THEN "G"
                                WHEN p.goles_visitante = p.goles_local THEN "E"
                                ELSE "P" END AS resultado,
                           p.goles_visitante AS gf,
                           p.goles_local AS gc
                    FROM partidos p
                    WHERE p.torneo_id = :torneo_id2 AND p.estado = "jugado"
                ) stats
                INNER JOIN usuarios u ON u.id = stats.usuario_id
                GROUP BY u.id, u.nombre
                ORDER BY puntos DESC, nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'torneo_id2' => $tournamentId,
        ]);

        return $stmt->fetchAll();
    }

    public function unfinishedMatchesCount(int $tournamentId, string $roundType = 'normal'): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total
             FROM partidos p
             INNER JOIN rondas r ON r.id = p.ronda_id
             WHERE p.torneo_id = :torneo_id
               AND r.tipo = :tipo
               AND p.estado <> "jugado"'
        );
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'tipo' => $roundType,
        ]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function latestRoundNumber(int $tournamentId, string $type): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(numero), 0) AS max_num FROM rondas WHERE torneo_id = :torneo_id AND tipo = :tipo');
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'tipo' => $type,
        ]);
        $row = $stmt->fetch();
        return (int) ($row['max_num'] ?? 0);
    }

    public function saveChampion(int $tournamentId, int $winnerId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO campeones_torneo (torneo_id, usuario_id)
             VALUES (:torneo_id, :usuario_id)
             ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), fecha_cierre = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'torneo_id' => $tournamentId,
            'usuario_id' => $winnerId,
        ]);
    }

    public function annualRanking(?int $year = null): array
    {
        $sql = 'SELECT u.id, u.nombre,
                       COUNT(ct.id) AS campeonatos,
                       GROUP_CONCAT(CONCAT(t.nombre, "|", t.anio, "|", DATE_FORMAT(ct.fecha_cierre, "%Y-%m-%d"), "|", participants.total)
                           ORDER BY ct.fecha_cierre DESC SEPARATOR ";") AS detalle
                FROM campeones_torneo ct
                INNER JOIN torneos t ON t.id = ct.torneo_id
                INNER JOIN usuarios u ON u.id = ct.usuario_id
                INNER JOIN (
                    SELECT torneo_id, COUNT(*) AS total
                    FROM torneo_jugadores
                    GROUP BY torneo_id
                ) participants ON participants.torneo_id = t.id';
        $params = [];
        if ($year !== null) {
            $sql .= ' WHERE t.anio = :anio';
            $params['anio'] = $year;
        }
        $sql .= ' GROUP BY u.id, u.nombre ORDER BY campeonatos DESC, u.nombre ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function rankingYears(): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT t.anio
             FROM campeones_torneo ct
             INNER JOIN torneos t ON t.id = ct.torneo_id
             ORDER BY t.anio DESC'
        );
        $stmt->execute();
        return array_map(static fn (array $row): int => (int) $row['anio'], $stmt->fetchAll());
    }
}
