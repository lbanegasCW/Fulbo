<?php

declare(strict_types=1);

namespace App\Repositories;

class TeamRepository extends BaseRepository
{
    public function byPot(int $potId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM equipos WHERE bombo_id = :pot_id ORDER BY nombre');
        $stmt->execute(['pot_id' => $potId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM equipos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO equipos (bombo_id, nombre, abreviatura, activo) VALUES (:bombo_id, :nombre, :abreviatura, :activo)');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE equipos SET nombre = :nombre, abreviatura = :abreviatura, activo = :activo, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'abreviatura' => $data['abreviatura'],
            'activo' => $data['activo'],
        ]);
    }

    public function randomAvailableByPotForTournament(int $potId, int $tournamentId, int $limit): array
    {
        $sql = 'SELECT e.id, e.nombre
                FROM equipos e
                WHERE e.bombo_id = :pot_id
                  AND e.activo = 1
                  AND e.id NOT IN (
                    SELECT ele.equipo_id
                    FROM elecciones_equipos ele
                    WHERE ele.torneo_id = :torneo_id
                  )
                ORDER BY RAND()
                LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'pot_id' => $potId,
            'torneo_id' => $tournamentId,
        ]);
        return $stmt->fetchAll();
    }
}
