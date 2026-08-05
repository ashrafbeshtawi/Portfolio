<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\ProjectGroup;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Tests\Support\PurgesDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SeedCommandTest extends KernelTestCase
{
    use PurgesDatabase;

    public function testSeedPopulatesInitialContentAndIsIdempotent(): void
    {
        $kernel = self::bootKernel();
        $this->purgeDatabase(static::getContainer()->get(EntityManagerInterface::class));
        $application = new Application($kernel);
        $tester = new CommandTester($application->find('app:seed'));

        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('seeded', $tester->getDisplay());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame(1, $entityManager->getRepository(Profile::class)->count([]));
        self::assertSame(7, $entityManager->getRepository(SkillCategory::class)->count([]));
        self::assertSame(28, $entityManager->getRepository(Skill::class)->count([]));
        self::assertSame(1, $entityManager->getRepository(ProjectGroup::class)->count([]));
        self::assertSame(2, $entityManager->getRepository(Project::class)->count([]));

        $profile = $entityManager->getRepository(Profile::class)->findOneBy([]);
        self::assertSame('Ashraf Beshtawi', $profile?->getFullName());
        self::assertSame('beshtawi.ashraf@gmail.com', $profile?->getEmail());

        // Second run must not duplicate anything
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('already contains content', $tester->getDisplay());
        self::assertSame(1, $entityManager->getRepository(Profile::class)->count([]));
        self::assertSame(28, $entityManager->getRepository(Skill::class)->count([]));
    }
}
