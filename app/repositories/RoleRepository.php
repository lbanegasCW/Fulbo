<?php

declare(strict_types=1);

namespace App\Repositories;

class RoleRepository extends BaseRepository
{
    public function all(): array
    {
        return $this->db->query('SELECT id, name, slug FROM roles ORDER BY id')->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }
}
