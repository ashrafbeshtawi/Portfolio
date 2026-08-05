<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProjectGroup;
use App\Form\ProjectGroupType;
use App\Repository\ProjectGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/project-groups')]
final class ProjectGroupController extends AbstractController
{
    #[Route('', name: 'admin_project_group_index', methods: ['GET'])]
    public function index(ProjectGroupRepository $repository): Response
    {
        return $this->render('admin/project_group/index.html.twig', [
            'groups' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_project_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $group = new ProjectGroup();
        $form = $this->createForm(ProjectGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project group "%s" created.', $group->getTitle()));

            return $this->redirectToRoute('admin_project_group_index');
        }

        return $this->render('admin/project_group/form.html.twig', [
            'form' => $form,
            'title' => 'New project group',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_project_group_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(ProjectGroup $group, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project group "%s" updated.', $group->getTitle()));

            return $this->redirectToRoute('admin_project_group_index');
        }

        return $this->render('admin/project_group/form.html.twig', [
            'form' => $form,
            'title' => \sprintf('Edit group "%s"', $group->getTitle()),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_project_group_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(ProjectGroup $group, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_project_group_'.$group->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project group "%s" deleted.', $group->getTitle()));
        }

        return $this->redirectToRoute('admin_project_group_index');
    }
}
