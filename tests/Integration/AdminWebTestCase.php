<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AdminWebTestCase extends WebTestCase
{
    /**
     * Credentials are defined in .env.test (ADMIN_USERNAME / ADMIN_PASSWORD).
     */
    protected const string ADMIN_USERNAME = 'test-admin';
    protected const string ADMIN_PASSWORD = 'test-password';

    protected function logIn(KernelBrowser $client): void
    {
        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $client->submitForm('Sign in', [
            '_username' => self::ADMIN_USERNAME,
            '_password' => self::ADMIN_PASSWORD,
        ]);
        self::assertResponseRedirects('/admin');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
