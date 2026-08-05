<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/profile')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'admin_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ProfileRepository $profileRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $profile = $profileRepository->findSingle() ?? new Profile();

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($profile);
            $entityManager->flush();
            $this->addFlash('success', 'Profile saved.');

            return $this->redirectToRoute('admin_profile_edit');
        }

        return $this->render('admin/profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
