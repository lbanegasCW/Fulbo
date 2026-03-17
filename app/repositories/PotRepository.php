<?php

declare(strict_types=1);

namespace App\Repositories;

class PotRepository extends BaseRepository
{
    public function all(): array
    {
        $sql = 'SELECT b.*, COUNT(e.id) AS total_equipos
                FROM bombos b
                LEFT JOIN equipos e ON e.bombo_id = b.id AND e.activo = 1
                GROUP BY b.id
                ORDER BY b.fecha_creacion DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function active(): array
    {
        $stmt = $this->db->prepare('SELECT id, nombre FROM bombos WHERE activo = 1 ORDER BY nombre');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM bombos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO bombos (nombre, descripcion, activo) VALUES (:nombre, :descripcion, :activo)');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE bombos SET nombre = :nombre, descripcion = :descripcion, activo = :activo, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'activo' => $data['activo'],
        ]);
    }
}
