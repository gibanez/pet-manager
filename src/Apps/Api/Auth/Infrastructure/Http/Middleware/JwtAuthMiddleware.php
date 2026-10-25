<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    /** @var Configuration */
    private $configuration;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(Configuration $configuration, ResponseFactoryInterface $responseFactory)
    {
        $this->configuration = $configuration;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeaderLine('Authorization');

        if ($authorization === '' || stripos($authorization, 'Bearer ') !== 0) {
            return $this->unauthorized('Missing bearer token.');
        }

        $tokenString = trim(substr($authorization, 7));

        try {
            $token = $this->configuration->parser()->parse($tokenString);

            $this->configuration->validator()->assert(
                $token,
                new SignedWith($this->configuration->signer(), $this->configuration->signingKey())
            );

            $this->configuration->validator()->assert($token, new ValidAt(SystemClock::fromUTC()));

            $subject = $token->claims()->get('sub');
        } catch (Throwable $error) {
            return $this->unauthorized('Invalid or expired token.');
        }

        return $handler->handle($request->withAttribute('adminId', $subject));
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(401);
        $response->getBody()->write((string) json_encode(['error' => $message]));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
