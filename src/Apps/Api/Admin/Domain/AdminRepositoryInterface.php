<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Admin\Domain;

interface AdminRepositoryInterface
{
    public function findById(string $id): ?Admin;

    public function findByEmail(string $email): ?Admin;

    public function save(Admin $admin): void;
}
