<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Infrastructure\Http;

use Gaby\Apps\Api\Auth\Application\Login\LoginCommand;
use Gaby\Apps\Api\Auth\Application\Login\LoginHandler;
use Gaby\Apps\Api\Auth\Domain\Exception\InvalidCredentialsException;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuthController
{
    use JsonResponseTrait;

    /** @var LoginHandler */
    private $handler;

    public function __construct(LoginHandler $handler)
    {
        $this->handler = $handler;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $password = isset($data['password']) ? (string) $data['password'] : '';

        if ($email === '' || $password === '') {
            return $this->json($response, ['error' => 'Email and password are required.'], 422);
        }

        try {
            $result = $this->handler->handle(new LoginCommand($email, $password));
        } catch (InvalidCredentialsException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 401);
        }

        return $this->json($response, [
            'token' => $result->token,
            'admin' => $result->admin,
        ]);
    }
}
