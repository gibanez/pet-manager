<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Infrastructure\Persistence;

use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;
use PDO;

final class SqlitePageRepository implements PageRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Page
    {
        $statement = $this->pdo->prepare('SELECT * FROM pages WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?Page
    {
        $statement = $this->pdo->prepare('SELECT * FROM pages WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @return Page[]
     */
    public function findAll(): array
    {
        $rows = $this->pdo->query('SELECT * FROM pages ORDER BY rowid ASC')->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM pages WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function save(Page $page): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO pages (id, slug, title, body, status)
             VALUES (:id, :slug, :title, :body, :status)
             ON CONFLICT(id) DO UPDATE SET
                slug = excluded.slug,
                title = excluded.title,
                body = excluded.body,
                status = excluded.status'
        );

        $statement->execute([
            'id' => $page->id(),
            'slug' => $page->slug(),
            'title' => $page->title(),
            'body' => $page->body(),
            'status' => $page->status(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM pages WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Page
    {
        return Page::fromPersistence(
            (string) $row['id'],
            (string) $row['slug'],
            (string) $row['title'],
            (string) $row['body'],
            (string) $row['status']
        );
    }
}
