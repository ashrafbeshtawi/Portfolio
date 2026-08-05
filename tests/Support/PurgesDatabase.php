<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Profile;
use App\Entity\Project;
use App\Entity\ProjectGroup;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Removes all portfolio content so a test can rely on a known-empty database,
 * regardless of what previous runs (e.g. the E2E suite, which commits for
 * real) left behind.
 */
trait PurgesDatabase
{
    private function purgeDatabase(EntityManagerInterface $entityManager): void
    {
        foreach ([Skill::class, SkillCategory::class, Project::class, ProjectGroup::class, Profile::class] as $entityClass) {
            $entityManager->createQuery(\sprintf('DELETE FROM %s e', $entityClass))->execute();
        }
        $entityManager->clear();
    }
}
