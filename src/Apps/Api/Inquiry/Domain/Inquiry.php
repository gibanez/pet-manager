<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Domain;

use Ramsey\Uuid\Uuid;

final class Inquiry
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var string */
    private $message;

    /** @var string */
    private $createdAt;

    private function __construct(string $id, string $name, string $email, string $message, string $createdAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->createdAt = $createdAt;
    }

    public static function create(string $name, string $email, string $message, string $createdAt): self
    {
        return new self(Uuid::uuid4()->toString(), $name, $email, $message, $createdAt);
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $email,
        string $message,
        string $createdAt
    ): self {
        return new self($id, $name, $email, $message, $createdAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'createdAt' => $this->createdAt,
        ];
    }
}
