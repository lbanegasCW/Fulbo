<?php

declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function activeForLogin(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, username
             FROM usuarios
             WHERE activo = 1 AND requiere_activacion = 0
             ORDER BY nombre ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function pendingActivation(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, username
             FROM usuarios
             WHERE activo = 1 AND requiere_activacion = 1
             ORDER BY nombre ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.username = :username
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function all(?int $active = null): array
    {
        $sql = 'SELECT u.id, u.nombre, u.username, u.rol_id, u.activo, u.requiere_activacion, r.slug AS role_slug
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id';
        $params = [];
        if ($active !== null) {
            $sql .= ' WHERE u.activo = :active';
            $params['active'] = $active;
        }
        $sql .= ' ORDER BY u.fecha_creacion DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, username, rol_id, activo, requiere_activacion)
             VALUES (:nombre, :username, :rol_id, :activo, :requiere_activacion)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'username' => $data['username'],
            'rol_id' => $data['rol_id'],
            'activo' => $data['activo'],
            'requiere_activacion' => $data['requiere_activacion'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET nombre = :nombre,
                 username = :username,
                 rol_id = :rol_id,
                 activo = :activo,
                 requiere_activacion = :requiere_activacion,
                 fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'username' => $data['username'],
            'rol_id' => $data['rol_id'],
            'activo' => $data['activo'],
            'requiere_activacion' => $data['requiere_activacion'],
        ]);
    }

    public function activatePin(int $id, string $pinHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET pin_hash = :pin_hash,
                 requiere_activacion = 0,
                 fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        return $stmt->execute(['id' => $id, 'pin_hash' => $pinHash]);
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET ultimo_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
