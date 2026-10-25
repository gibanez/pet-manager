<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Application\Login;

final class LoginResult
{
    /** @var string */
    public $token;

    /** @var array<string, string> */
    public $admin;

    /**
     * @param array<string, string> $admin
     */
    public function __construct(string $token, array $admin)
    {
        $this->token = $token;
        $this->admin = $admin;
    }
}
