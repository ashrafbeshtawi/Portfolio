<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Service\ContentSeeder;
use App\Tests\Support\PurgesDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicPageTest extends WebTestCase
{
    use PurgesDatabase;

    public function testHomepageRendersSeededContent(): void
    {
        $client = static::createClient();
        $this->purgeDatabase(static::getContainer()->get(EntityManagerInterface::class));
        static::getContainer()->get(ContentSeeder::class)->seed();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Ashraf Beshtawi');
        self::assertSelectorTextContains('.hero-eyebrow', 'Senior Backend Developer');

        // Skills grid (certifications are shown with the projects instead)
        self::assertSelectorTextContains('#skills h2', 'Skills');
        self::assertStringContainsString('PHP', $crawler->filter('#skills')->text());
        self::assertStringNotContainsString('Certifications', $crawler->filter('#skills')->text());

        // Project cards with descriptions and links
        self::assertStringContainsString('NullSpace', $crawler->filter('#projects')->text());
        self::assertStringContainsString('virtual-memory model', $crawler->filter('#projects')->text());
        $projectUrls = $crawler->filter('#projects a.project-link')->each(
            static fn ($node) => $node->attr('href'),
        );
        self::assertContains('https://github.com/ashrafbeshtawi/NullSpace', $projectUrls);
        self::assertContains('https://github.com/ashrafbeshtawi/context-paging', $projectUrls);

        // All four certifications as described cards in the projects section
        self::assertStringContainsString('SymfonyCasts certification', $crawler->filter('#projects .cert-cards')->text());
        $certUrls = $crawler->filter('#projects .cert-cards a')->each(
            static fn ($node) => $node->attr('href'),
        );
        self::assertCount(4, $certUrls);
        self::assertContains('https://symfonycasts.com/certificates/35cd1309c158', $certUrls);
        self::assertContains('https://symfonycasts.com/certificates/2aa8bb96c155', $certUrls);
        self::assertContains('https://symfonycasts.com/certificates/0e7588acc161', $certUrls);
        self::assertContains('https://verify.skilljar.com/c/da6mfrh4vqiz', $certUrls);

        // Contact and about
        self::assertSelectorTextContains('#contact h2', "Let's Connect");
        self::assertSelectorTextContains('#about h2', 'About Me');
        self::assertStringContainsString('TU Berlin', $crawler->filter('#about')->text());
        self::assertStringContainsString('Symfony 7 certified', $crawler->filter('#about')->text());
    }

    public function testHomepageWorksWithEmptyDatabase(): void
    {
        $client = static::createClient();
        $this->purgeDatabase(static::getContainer()->get(EntityManagerInterface::class));

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.hero-tagline', 'No content yet');
    }
}
