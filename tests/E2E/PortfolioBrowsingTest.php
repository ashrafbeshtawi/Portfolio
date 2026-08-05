<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Service\ContentSeeder;
use App\Tests\Support\PurgesDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Chromium-driven end-to-end tests. The database is reset to the default
 * seeded content (committed for real — no transaction wrapping here, because
 * Panther's web server runs in a separate process), and a real browser drives
 * the public site and the control panel login.
 */
final class PortfolioBrowsingTest extends PantherTestCase
{
    use PurgesDatabase;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->purgeDatabase(static::getContainer()->get(EntityManagerInterface::class));
        static::getContainer()->get(ContentSeeder::class)->seed();
        static::ensureKernelShutdown();
    }

    public function testHomepageShowsPortfolioContent(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorTextContains('h1', 'Ashraf Beshtawi');
        // The eyebrow is uppercased via CSS, and Panther sees the rendered text
        self::assertSelectorTextContains('.hero-eyebrow', 'SENIOR BACKEND DEVELOPER');
        self::assertSelectorTextContains('#skills h2', 'Skills');
        self::assertSelectorTextContains('#projects', 'NullSpace');
        self::assertSelectorTextContains('#projects .cert-block', 'Claude Code');
        self::assertSelectorTextContains('#about', 'About Me');
        self::assertSelectorTextContains('.site-footer', 'Ashraf Beshtawi');
    }

    public function testDarkModeToggle(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        // Headless Chrome defaults to the light color scheme
        self::assertSame('light', $client->executeScript('return document.documentElement.dataset.theme'));

        $client->getCrawler()->filter('.theme-toggle')->getElement(0)->click();
        self::assertSame('dark', $client->executeScript('return document.documentElement.dataset.theme'));
        self::assertSame('dark', $client->executeScript("return localStorage.getItem('theme')"));

        // The choice survives a reload
        $client->request('GET', '/');
        self::assertSame('dark', $client->executeScript('return document.documentElement.dataset.theme'));

        // Reset so other tests start from the default
        $client->executeScript("localStorage.removeItem('theme')");
    }

    public function testAdminCanLogInThroughTheBrowser(): void
    {
        $client = static::createPantherClient();

        $client->request('GET', '/login');
        self::assertSelectorTextContains('h1', 'Control Panel');

        $client->submitForm('Sign in', [
            '_username' => 'test-admin',
            '_password' => 'test-password',
        ]);

        $client->waitFor('.stat-grid');
        self::assertSelectorTextContains('h1', 'Dashboard');
        self::assertStringContainsString('/admin', $client->getCurrentURL());
    }
}
