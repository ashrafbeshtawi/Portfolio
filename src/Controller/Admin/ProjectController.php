<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/projects')]
final class ProjectController extends AbstractController
{
    #[Route('', name: 'admin_project_index', methods: ['GET'])]
    public function index(ProjectRepository $repository): Response
    {
        return $this->render('admin/project/index.html.twig', [
            'projects' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project "%s" created.', $project->getName()));

            return $this->redirectToRoute('admin_project_index');
        }

        return $this->render('admin/project/form.html.twig', [
            'form' => $form,
            'title' => 'New project',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_project_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project "%s" updated.', $project->getName()));

            return $this->redirectToRoute('admin_project_index');
        }

        return $this->render('admin/project/form.html.twig', [
            'form' => $form,
            'title' => \sprintf('Edit project "%s"', $project->getName()),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_project_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_project_'.$project->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
            $this->addFlash('success', \sprintf('Project "%s" deleted.', $project->getName()));
        }

        return $this->redirectToRoute('admin_project_index');
    }
}
