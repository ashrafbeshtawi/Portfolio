<?php

declare(strict_types=1);

namespace App\Tests\Integration;

final class AdminSecurityTest extends AdminWebTestCase
{
    public function testAdminAreaRequiresAuthentication(): void
    {
        $client = static::createClient();

        foreach (['/admin', '/admin/profile', '/admin/skills', '/admin/projects'] as $path) {
            $client->request('GET', $path);
            self::assertResponseRedirects('http://localhost/login', message: \sprintf('"%s" must redirect to the login page', $path));
        }
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => self::ADMIN_USERNAME,
            '_password' => 'definitely-wrong',
        ]);

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorExists('.alert-error');

        // Still locked out
        $client->request('GET', '/admin');
        self::assertResponseRedirects('http://localhost/login');
    }

    public function testLoginFailsWithWrongUsername(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => 'not-the-admin',
            '_password' => self::ADMIN_PASSWORD,
        ]);

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorExists('.alert-error');
    }

    public function testLoginSucceedsWithEnvCredentials(): void
    {
        $client = static::createClient();

        $this->logIn($client);

        self::assertSelectorTextContains('h1', 'Dashboard');
        self::assertSelectorTextContains('.admin-intro', self::ADMIN_USERNAME);
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $this->logIn($client);

        $client->request('GET', '/logout');
        self::assertResponseRedirects();

        $client->request('GET', '/admin');
        self::assertResponseRedirects('http://localhost/login');
    }
}
