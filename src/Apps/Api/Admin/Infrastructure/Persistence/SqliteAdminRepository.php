<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Admin\Infrastructure\Persistence;

use Gaby\Apps\Api\Admin\Domain\Admin;
use Gaby\Apps\Api\Admin\Domain\AdminRepositoryInterface;
use PDO;

final class SqliteAdminRepository implements AdminRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Admin
    {
        $statement = $this->pdo->prepare('SELECT * FROM admins WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function findByEmail(string $email): ?Admin
    {
        $statement = $this->pdo->prepare('SELECT * FROM admins WHERE email = :email');
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function save(Admin $admin): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admins (id, name, email, password_hash, status)
             VALUES (:id, :name, :email, :password_hash, :status)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                email = excluded.email,
                password_hash = excluded.password_hash,
                status = excluded.status'
        );

        $statement->execute([
            'id' => $admin->id(),
            'name' => $admin->name(),
            'email' => $admin->email(),
            'password_hash' => $admin->passwordHash(),
            'status' => $admin->status(),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Admin
    {
        return Admin::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            (string) $row['password_hash'],
            (string) $row['status']
        );
    }
}
