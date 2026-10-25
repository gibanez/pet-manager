<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Application\Login;

final class LoginCommand
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
