<?php

declare(strict_types=1);

namespace Gaby\Apps\Site\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SiteController
{
    public function home(Request $request, Response $response): Response
    {
        return $this->render($response, 'home.html');
    }

    public function about(Request $request, Response $response): Response
    {
        return $this->render($response, 'about.html');
    }

    public function contact(Request $request, Response $response): Response
    {
        return $this->render($response, 'contact.html');
    }

    private function render(Response $response, string $view): Response
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view;
        $html = is_file($file) ? (string) file_get_contents($file) : 'Not found.';

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
