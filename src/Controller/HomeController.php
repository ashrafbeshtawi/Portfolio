<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProfileRepository;
use App\Repository\ProjectGroupRepository;
use App\Repository\SkillCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        ProfileRepository $profileRepository,
        SkillCategoryRepository $skillCategoryRepository,
        ProjectGroupRepository $projectGroupRepository,
    ): Response {
        // Certifications are shown alongside the projects, not in the skills grid.
        $certifications = null;
        $skillCategories = [];
        foreach ($skillCategoryRepository->findAllOrdered() as $category) {
            if (null === $certifications && 0 === strcasecmp($category->getName(), 'Certifications')) {
                $certifications = $category;
            } else {
                $skillCategories[] = $category;
            }
        }

        return $this->render('home/index.html.twig', [
            'profile' => $profileRepository->findSingle(),
            'skillCategories' => $skillCategories,
            'certifications' => $certifications,
            'projectGroups' => $projectGroupRepository->findAllOrdered(),
        ]);
    }
}
