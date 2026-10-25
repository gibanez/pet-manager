<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Domain;

interface PageRepositoryInterface
{
    public function findById(string $id): ?Page;

    public function findBySlug(string $slug): ?Page;

    /**
     * @return Page[]
     */
    public function findAll(): array;

    public function existsBySlug(string $slug, ?string $excludeId = null): bool;

    public function save(Page $page): void;

    public function delete(string $id): void;
}
