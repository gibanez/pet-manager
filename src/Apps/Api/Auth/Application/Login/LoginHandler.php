<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Application\Login;

use Gaby\Apps\Api\Admin\Domain\AdminRepositoryInterface;
use Gaby\Apps\Api\Auth\Domain\Exception\InvalidCredentialsException;
use Gaby\Apps\Api\Auth\Domain\TokenGeneratorInterface;

final class LoginHandler
{
    /** @var AdminRepositoryInterface */
    private $admins;

    /** @var TokenGeneratorInterface */
    private $tokens;

    public function __construct(AdminRepositoryInterface $admins, TokenGeneratorInterface $tokens)
    {
        $this->admins = $admins;
        $this->tokens = $tokens;
    }

    public function handle(LoginCommand $command): LoginResult
    {
        $admin = $this->admins->findByEmail($command->email);

        if ($admin === null || !$admin->isActive()) {
            throw InvalidCredentialsException::create();
        }

        if (!password_verify($command->password, $admin->passwordHash())) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->tokens->generate($admin->id(), $admin->email());

        return new LoginResult($token, [
            'id' => $admin->id(),
            'name' => $admin->name(),
            'email' => $admin->email(),
        ]);
    }
}
