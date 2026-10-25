<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Auth\Doubles;

use Gaby\Apps\Api\Admin\Domain\Admin;
use Gaby\Apps\Api\Admin\Domain\AdminRepositoryInterface;

final class InMemoryAdminRepository implements AdminRepositoryInterface
{
    /** @var array<string, Admin> */
    private $admins = [];

    public function findById(string $id): ?Admin
    {
        return $this->admins[$id] ?? null;
    }

    public function findByEmail(string $email): ?Admin
    {
        foreach ($this->admins as $admin) {
            if ($admin->email() === $email) {
                return $admin;
            }
        }

        return null;
    }

    public function save(Admin $admin): void
    {
        $this->admins[$admin->id()] = $admin;
    }
}
