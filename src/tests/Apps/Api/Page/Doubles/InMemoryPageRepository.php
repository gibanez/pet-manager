<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Page\Doubles;

use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class InMemoryPageRepository implements PageRepositoryInterface
{
    /** @var array<string, Page> */
    private $pages = [];

    public function findById(string $id): ?Page
    {
        return $this->pages[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Page
    {
        foreach ($this->pages as $page) {
            if ($page->slug() === $slug) {
                return $page;
            }
        }

        return null;
    }

    /**
     * @return Page[]
     */
    public function findAll(): array
    {
        return array_values($this->pages);
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        foreach ($this->pages as $page) {
            if ($page->slug() === $slug && ($excludeId === null || $page->id() !== $excludeId)) {
                return true;
            }
        }

        return false;
    }

    public function save(Page $page): void
    {
        $this->pages[$page->id()] = $page;
    }

    public function delete(string $id): void
    {
        unset($this->pages[$id]);
    }
}
