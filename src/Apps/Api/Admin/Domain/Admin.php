<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Admin\Domain;

use Ramsey\Uuid\Uuid;

final class Admin
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var string */
    private $passwordHash;

    /** @var string */
    private $status;

    private function __construct(string $id, string $name, string $email, string $passwordHash, string $status)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->status = $status;
    }

    public static function create(string $name, string $email, string $password): self
    {
        return new self(
            Uuid::uuid4()->toString(),
            $name,
            $email,
            (string) password_hash($password, PASSWORD_BCRYPT),
            self::STATUS_ACTIVE
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $email,
        string $passwordHash,
        string $status
    ): self {
        return new self($id, $name, $email, $passwordHash, $status);
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

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changePassword(string $password): void
    {
        $this->passwordHash = (string) password_hash($password, PASSWORD_BCRYPT);
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
    }

    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
    }
}
