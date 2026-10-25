<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Domain;

use Ramsey\Uuid\Uuid;

final class Page
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    private $id;

    /** @var string */
    private $slug;

    /** @var string */
    private $title;

    /** @var string */
    private $body;

    /** @var string */
    private $status;

    private function __construct(string $id, string $slug, string $title, string $body, string $status)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
    }

    public static function create(
        string $slug,
        string $title,
        string $body,
        string $status = self::STATUS_ACTIVE
    ): self {
        return new self(Uuid::uuid4()->toString(), $slug, $title, $body, $status);
    }

    public static function fromPersistence(
        string $id,
        string $slug,
        string $title,
        string $body,
        string $status
    ): self {
        return new self($id, $slug, $title, $body, $status);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function updateDetails(string $slug, string $title, string $body, string $status): void
    {
        $this->slug = $slug;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status,
        ];
    }
}
