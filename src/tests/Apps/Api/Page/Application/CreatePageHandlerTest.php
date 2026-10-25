<?php

declare(strict_types=1);

use Gaby\Apps\Api\Page\Application\CreatePage\CreatePageCommand;
use Gaby\Apps\Api\Page\Application\CreatePage\CreatePageHandler;
use Gaby\Apps\Api\Page\Domain\Exception\SlugAlreadyInUseException;
use Gaby\Tests\Apps\Api\Page\Doubles\InMemoryPageRepository;

test('crea una pagina', function (): void {
    $pages = new InMemoryPageRepository();
    $handler = new CreatePageHandler($pages);

    $page = $handler->handle(new CreatePageCommand('home', 'Inicio', 'Hola'));

    expect($page->slug())->toBe('home');
    expect($pages->findBySlug('home'))->not->toBeNull();
});

test('no permite slugs duplicados', function (): void {
    $pages = new InMemoryPageRepository();
    $handler = new CreatePageHandler($pages);
    $handler->handle(new CreatePageCommand('home', 'Inicio', 'Hola'));

    expect(function () use ($handler): void {
        $handler->handle(new CreatePageCommand('home', 'Otro', '...'));
    })->toThrow(SlugAlreadyInUseException::class);
});
