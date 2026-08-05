<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\ProjectGroup;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Seeds the database with the default portfolio content, sourced from the CV
 * (public/cv.pdf). Idempotent: does nothing when a profile already exists.
 */
final class ContentSeeder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProfileRepository $profileRepository,
    ) {
    }

    public function isSeeded(): bool
    {
        return null !== $this->profileRepository->findSingle();
    }

    /**
     * @return bool whether anything was seeded
     */
    public function seed(): bool
    {
        if ($this->isSeeded()) {
            return false;
        }

        $this->seedProfile();
        $this->seedSkills();
        $this->seedProjects();

        $this->entityManager->flush();

        return true;
    }

    private function seedProfile(): void
    {
        $profile = new Profile()
            ->setFullName('Ashraf Beshtawi')
            ->setHeadline('Senior Backend Developer · PHP & Symfony')
            ->setTagline(
                'Five years building and operating production FinTech systems in PHP and Symfony. '
                .'Symfony 7 certified, focused on domain modelling, REST API design and reliable delivery '
                .'— increasingly at the intersection of backend and applied AI.'
            )
            ->setEmail('beshtawi.ashraf@gmail.com')
            ->setGithubUrl('https://github.com/ashrafbeshtawi')
            ->setLinkedinUrl('https://www.linkedin.com/in/ashraf-beshtawi-1308a11a8/')
            ->setLocation('Berlin, Germany')
            ->setAboutText(
                "Since 2021 I've been a backend developer at Foo Ag in Berlin, designing and maintaining "
                .'backend services and REST APIs for financial applications in Symfony and PostgreSQL — from '
                .'data-extraction pipelines that normalise heterogeneous document formats into one domain model, '
                ."to the AI layer of an internal product architected under EU data-residency constraints.\n\n"
                .'Before that I taught web development to career changers at ReDI School Berlin and tutored '
                .'undergraduates in C and Assembly at TU Berlin, where I earned my B.Sc. in Computer Science.'."\n\n"
                .'Communication is key — I work in German and English (both C1) and Arabic, my native language.'
            )
            ->setHighlights("Berlin, Germany\nSenior Backend — PHP & Symfony\nSymfony 7 certified\nArabic · German · English")
            ->setContactHeading("Let's Connect")
            ->setContactText('Open to discussing new projects, creative ideas, or opportunities. Feel free to reach out.')
            ->setPhotoPath('img/me.jpeg')
            ->setCvPath('cv.pdf')
            ->setMetaDescription('Portfolio of Ashraf Beshtawi — senior backend developer in Berlin, specializing in PHP, Symfony, PostgreSQL and applied AI.');

        $this->entityManager->persist($profile);
    }

    private function seedSkills(): void
    {
        $categories = [
            ['Backend', '⚙️', [
                ['PHP 8.x', null],
                ['Symfony 6 / 7', null],
                ['API Platform', null],
                ['Doctrine', null],
                ['REST', null],
            ]],
            ['Data', '🗄️', [
                ['PostgreSQL', null],
                ['Redis', null],
                ['Schema & query optimisation', null],
            ]],
            ['AI & Agents', '🤖', [
                ['MCP', null],
                ['Anthropic & OpenAI APIs', null],
                ['RAG', null],
                ['n8n', null],
            ]],
            ['Infrastructure', '☁️', [
                ['Docker', null],
                ['Traefik', null],
                ['GitLab CI', null],
                ['GitHub Actions', null],
                ['Linux', null],
            ]],
            ['Also', '🧰', [
                ['PHPUnit', null],
                ['TypeScript', null],
                ['Node.js', null],
                ['Next.js / Nuxt.js', null],
            ]],
            ['Languages', '🌍', [
                ['Arabic — native', null],
                ['German — C1', null],
                ['English — C1', null],
            ]],
            ['Certifications', '🎓', [
                [
                    'Symfony 7 Fundamentals',
                    'https://symfonycasts.com/certificates/35cd1309c158',
                    'SymfonyCasts certification — services, dependency injection, configuration and environments in Symfony 7.',
                ],
                [
                    'Cosmic Coding with Symfony 7',
                    'https://symfonycasts.com/certificates/2aa8bb96c155',
                    'SymfonyCasts certification — routing, controllers, Twig templating and the modern Symfony toolchain.',
                ],
                [
                    'Doctrine, Symfony 7 & the Database',
                    'https://symfonycasts.com/certificates/0e7588acc161',
                    'SymfonyCasts certification — the Doctrine ORM: entities, migrations, relations and queries.',
                ],
                [
                    'Claude Code',
                    'https://verify.skilljar.com/c/da6mfrh4vqiz',
                    'Anthropic certification — agentic software development with Claude Code.',
                ],
            ]],
        ];

        $categorySort = 0;
        foreach ($categories as [$categoryName, $icon, $skills]) {
            $category = new SkillCategory()
                ->setName($categoryName)
                ->setIcon($icon)
                ->setSortOrder($categorySort++);
            $this->entityManager->persist($category);

            $skillSort = 0;
            foreach ($skills as $skillData) {
                $skill = new Skill()
                    ->setName($skillData[0])
                    ->setUrl($skillData[1])
                    ->setDescription($skillData[2] ?? null)
                    ->setSortOrder($skillSort++);
                $category->addSkill($skill);
                $this->entityManager->persist($skill);
            }
        }
    }

    private function seedProjects(): void
    {
        $groups = [
            ['Selected Projects', null, [
                [
                    'NullSpace',
                    'https://github.com/ashrafbeshtawi/NullSpace',
                    'Self-hosted multi-service platform in production — Docker Compose, Traefik, '
                    .'PostgreSQL & Redis, GitHub Actions CI/CD, monitoring and encrypted off-site backups.',
                ],
                [
                    'Context Paging',
                    'https://github.com/ashrafbeshtawi/context-paging',
                    'Applies the OS virtual-memory model to LLM agent context, keeping long-running '
                    .'context lossless. TypeScript, provider-agnostic across seven vendors.',
                ],
            ]],
        ];

        $groupSort = 0;
        foreach ($groups as [$title, $imagePath, $projects]) {
            $group = new ProjectGroup()
                ->setTitle($title)
                ->setImagePath($imagePath)
                ->setSortOrder($groupSort++);
            $this->entityManager->persist($group);

            $projectSort = 0;
            foreach ($projects as [$name, $url, $description]) {
                $project = new Project()
                    ->setName($name)
                    ->setUrl($url)
                    ->setDescription($description)
                    ->setSortOrder($projectSort++);
                $group->addProject($project);
                $this->entityManager->persist($project);
            }
        }
    }
}
