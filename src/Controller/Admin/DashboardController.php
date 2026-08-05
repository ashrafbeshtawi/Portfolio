<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\ProfileRepository;
use App\Repository\ProjectGroupRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        ProfileRepository $profileRepository,
        SkillCategoryRepository $skillCategoryRepository,
        SkillRepository $skillRepository,
        ProjectGroupRepository $projectGroupRepository,
        ProjectRepository $projectRepository,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'profile' => $profileRepository->findSingle(),
            'skillCategoryCount' => $skillCategoryRepository->count([]),
            'skillCount' => $skillRepository->count([]),
            'projectGroupCount' => $projectGroupRepository->count([]),
            'projectCount' => $projectRepository->count([]),
        ]);
    }
}
