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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class AdminCrudTest extends AdminWebTestCase
{
    use PurgesDatabase;

    public function testProfileCanBeCreatedAndEdited(): void
    {
        $client = $this->createLoggedInClientWithEmptyDatabase();

        $client->request('GET', '/admin/profile');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save profile', [
            'profile[fullName]' => 'Jane Doe',
            'profile[headline]' => 'Backend Engineer',
            'profile[tagline]' => 'I build things.',
            'profile[email]' => 'jane@example.com',
            'profile[contactHeading]' => 'Say hi',
        ]);
        self::assertResponseRedirects('/admin/profile');

        $profile = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Profile::class)->findOneBy(['fullName' => 'Jane Doe']);
        self::assertNotNull($profile);
        self::assertSame('jane@example.com', $profile->getEmail());

        // The public site reflects the change immediately
        $client->request('GET', '/');
        self::assertSelectorTextContains('h1', 'Jane Doe');

        // Editing updates the same singleton row instead of creating a second one
        $client->request('GET', '/admin/profile');
        $client->submitForm('Save profile', [
            'profile[fullName]' => 'Jane D. Doe',
        ]);
        self::assertResponseRedirects('/admin/profile');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame(1, $entityManager->getRepository(Profile::class)->count([]));
    }

    public function testSkillCategoryCrud(): void
    {
        $client = $this->createLoggedInClientWithEmptyDatabase();

        // Create
        $client->request('GET', '/admin/skill-categories/new');
        $client->submitForm('Save', [
            'skill_category[name]' => 'Languages',
            'skill_category[sortOrder]' => '5',
        ]);
        self::assertResponseRedirects('/admin/skill-categories');
        $crawler = $client->followRedirect();
        self::assertStringContainsString('Languages', $crawler->filter('.admin-table')->text());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $category = $entityManager->getRepository(SkillCategory::class)->findOneBy(['name' => 'Languages']);
        self::assertNotNull($category);

        // Edit
        $client->request('GET', \sprintf('/admin/skill-categories/%d/edit', $category->getId()));
        $client->submitForm('Save', [
            'skill_category[name]' => 'Spoken Languages',
        ]);
        self::assertResponseRedirects('/admin/skill-categories');
        $entityManager->clear();
        self::assertSame('Spoken Languages', $entityManager->find(SkillCategory::class, $category->getId())?->getName());

        // Delete
        $crawler = $client->request('GET', '/admin/skill-categories');
        $client->submit($crawler->filter('form[action$="/delete"]')->first()->form());
        self::assertResponseRedirects('/admin/skill-categories');
        $entityManager->clear();
        self::assertNull($entityManager->find(SkillCategory::class, $category->getId()));
    }

    public function testSkillCanBeCreatedInCategory(): void
    {
        $client = $this->createLoggedInClientWithEmptyDatabase();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $category = new SkillCategory()->setName('Toolkit');
        $entityManager->persist($category);
        $entityManager->flush();

        $client->request('GET', '/admin/skills/new');
        $client->submitForm('Save', [
            'skill[name]' => 'Symfony',
            'skill[category]' => (string) $category->getId(),
            'skill[url]' => 'https://symfony.com',
            'skill[sortOrder]' => '1',
        ]);
        self::assertResponseRedirects('/admin/skills');

        $skill = $entityManager->getRepository(Skill::class)->findOneBy(['name' => 'Symfony']);
        self::assertNotNull($skill);
        self::assertSame('Toolkit', $skill->getCategory()?->getName());

        // Appears on the public page as a linked chip
        $crawler = $client->request('GET', '/');
        $chip = $crawler->filter('#skills a.chip-link')->first();
        self::assertSame('https://symfony.com', $chip->attr('href'));
    }

    public function testProjectGroupAndProjectCanBeCreated(): void
    {
        $client = $this->createLoggedInClientWithEmptyDatabase();

        $client->request('GET', '/admin/project-groups/new');
        $client->submitForm('Save', [
            'project_group[title]' => 'Open Source',
            'project_group[sortOrder]' => '1',
        ]);
        self::assertResponseRedirects('/admin/project-groups');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $group = $entityManager->getRepository(ProjectGroup::class)->findOneBy(['title' => 'Open Source']);
        self::assertNotNull($group);

        $client->request('GET', '/admin/projects/new');
        $client->submitForm('Save', [
            'project[name]' => 'Demo Project',
            'project[projectGroup]' => (string) $group->getId(),
            'project[url]' => 'https://github.com/example/demo',
            'project[description]' => 'A demo project used in tests.',
            'project[sortOrder]' => '1',
        ]);
        self::assertResponseRedirects('/admin/projects');

        $project = $entityManager->getRepository(Project::class)->findOneBy(['name' => 'Demo Project']);
        self::assertNotNull($project);
        self::assertSame('Open Source', $project->getProjectGroup()?->getTitle());
        self::assertSame('A demo project used in tests.', $project->getDescription());

        // Deleting the group cascades to its projects
        $crawler = $client->request('GET', '/admin/project-groups');
        $client->submit($crawler->filter('form[action$="/delete"]')->first()->form());
        self::assertResponseRedirects('/admin/project-groups');
        $entityManager->clear();
        self::assertNull($entityManager->find(Project::class, $project->getId()));
    }

    public function testValidationRejectsBlankName(): void
    {
        $client = $this->createLoggedInClientWithEmptyDatabase();

        $client->request('GET', '/admin/skill-categories/new');
        $client->submitForm('Save', [
            'skill_category[name]' => '',
        ]);

        // Invalid form re-renders instead of redirecting
        self::assertResponseStatusCodeSame(422);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame(0, $entityManager->getRepository(SkillCategory::class)->count([]));
    }

    /**
     * Content tests assert on counts and row positions, so they start from a
     * clean slate (the E2E suite commits seed data to the same test database).
     */
    private function createLoggedInClientWithEmptyDatabase(): KernelBrowser
    {
        $client = static::createClient();
        $this->purgeDatabase(static::getContainer()->get(EntityManagerInterface::class));
        $this->logIn($client);

        return $client;
    }
}
