<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SkillCategory;
use App\Form\SkillCategoryType;
use App\Repository\SkillCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/skill-categories')]
final class SkillCategoryController extends AbstractController
{
    #[Route('', name: 'admin_skill_category_index', methods: ['GET'])]
    public function index(SkillCategoryRepository $repository): Response
    {
        return $this->render('admin/skill_category/index.html.twig', [
            'categories' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_skill_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new SkillCategory();
        $form = $this->createForm(SkillCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Category "%s" created.', $category->getName()));

            return $this->redirectToRoute('admin_skill_category_index');
        }

        return $this->render('admin/skill_category/form.html.twig', [
            'form' => $form,
            'title' => 'New skill category',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_skill_category_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(SkillCategory $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SkillCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Category "%s" updated.', $category->getName()));

            return $this->redirectToRoute('admin_skill_category_index');
        }

        return $this->render('admin/skill_category/form.html.twig', [
            'form' => $form,
            'title' => \sprintf('Edit category "%s"', $category->getName()),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_skill_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(SkillCategory $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_skill_category_'.$category->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Category "%s" deleted.', $category->getName()));
        }

        return $this->redirectToRoute('admin_skill_category_index');
    }
}
