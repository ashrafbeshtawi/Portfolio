<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/skills')]
final class SkillController extends AbstractController
{
    #[Route('', name: 'admin_skill_index', methods: ['GET'])]
    public function index(SkillRepository $repository): Response
    {
        return $this->render('admin/skill/index.html.twig', [
            'skills' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_skill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($skill);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Skill "%s" created.', $skill->getName()));

            return $this->redirectToRoute('admin_skill_index');
        }

        return $this->render('admin/skill/form.html.twig', [
            'form' => $form,
            'title' => 'New skill',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_skill_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Skill $skill, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Skill "%s" updated.', $skill->getName()));

            return $this->redirectToRoute('admin_skill_index');
        }

        return $this->render('admin/skill/form.html.twig', [
            'form' => $form,
            'title' => \sprintf('Edit skill "%s"', $skill->getName()),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_skill_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Skill $skill, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_skill_'.$skill->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($skill);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Skill "%s" deleted.', $skill->getName()));
        }

        return $this->redirectToRoute('admin_skill_index');
    }
}
