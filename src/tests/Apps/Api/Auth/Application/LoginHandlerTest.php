<?php

declare(strict_types=1);

use Gaby\Apps\Api\Admin\Domain\Admin;
use Gaby\Apps\Api\Auth\Application\Login\LoginCommand;
use Gaby\Apps\Api\Auth\Application\Login\LoginHandler;
use Gaby\Apps\Api\Auth\Domain\Exception\InvalidCredentialsException;
use Gaby\Tests\Apps\Api\Auth\Doubles\FakeTokenGenerator;
use Gaby\Tests\Apps\Api\Auth\Doubles\InMemoryAdminRepository;

test('devuelve un token con credenciales validas', function (): void {
    $admins = new InMemoryAdminRepository();
    $admin = Admin::create('Admin', 'admin@example.com', 'secret');
    $admins->save($admin);

    $handler = new LoginHandler($admins, new FakeTokenGenerator());
    $result = $handler->handle(new LoginCommand('admin@example.com', 'secret'));

    expect($result->token)->toBe('token-' . $admin->id());
    expect($result->admin['email'])->toBe('admin@example.com');
});

test('rechaza credenciales invalidas', function (): void {
    $admins = new InMemoryAdminRepository();
    $admins->save(Admin::create('Admin', 'admin@example.com', 'secret'));

    $handler = new LoginHandler($admins, new FakeTokenGenerator());

    expect(function () use ($handler): void {
        $handler->handle(new LoginCommand('admin@example.com', 'wrong'));
    })->toThrow(InvalidCredentialsException::class);
});

test('rechaza un admin inactivo', function (): void {
    $admins = new InMemoryAdminRepository();
    $admin = Admin::create('Admin', 'admin@example.com', 'secret');
    $admin->deactivate();
    $admins->save($admin);

    $handler = new LoginHandler($admins, new FakeTokenGenerator());

    expect(function () use ($handler): void {
        $handler->handle(new LoginCommand('admin@example.com', 'secret'));
    })->toThrow(InvalidCredentialsException::class);
});
