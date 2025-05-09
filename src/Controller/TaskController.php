<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Medecin;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/medecin/tasks')]
class TaskController extends AbstractController
{
    #[Route('', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $medecin = $this->getUser()->getMedecin();
        $tasks = $taskRepository->findTasksForMedecin($medecin);;

        return $this->redirectToRoute('app_my_tasks');

    }

    #[Route('/create', name: 'app_task_new')]
    public function createTask(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Récupération du Medecin associé via la relation User
        $medecin = $user->getMedecin();

        if (!$medecin) {
            throw new AccessDeniedException('Seuls les médecins peuvent créer des tâches');
        }

        $task = new Task();

        // Utilisation du Medecin pour l'assignation
        $task->setCreatedBy($medecin);
        $task->setCreatedAt(new \DateTime());

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($task);
                $em->flush();

                $this->addFlash('success', 'Tâche créée avec succès !');
                return $this->redirectToRoute('app_task_index');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de la tâche');
            }
        }

        return $this->render('medecin/create-task.html.twig', [
            'form' => $form->createView(),
            'medecin' => $medecin
        ]);
    }



    #[Route('/mes-tâches', name: 'app_my_tasks')]
    public function myTasks(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        if (!$medecin) {
            throw new AccessDeniedException('Accès réservé aux médecins');
        }

        $tasks = $taskRepository->findByAssignedMedecin($medecin);

        return $this->render('medecin/my-tasks.html.twig', [
            'tasks' => $tasks
        ]);

    }

    #[Route('/followed', name: 'app_task_followed', methods: ['GET'])]
    public function followedTasks(TaskRepository $taskRepository): Response
    {
        $this->checkMedecinAccess();
        $tasks = $taskRepository->findFollowedTasks($this->getUser()->getMedecin());

        return $this->render('medecin/follow-task.html.twig', [
            'tasks' => $tasks
        ]);
    }

    private function checkMedecinAccess(): void
    {
        if (!$this->getUser()->getMedecin()) {
            throw new AccessDeniedException('Accès réservé aux médecins');
        }
    }
    #[Route('/task/{id}/complete', name: 'task_complete', methods: ['POST'])]
    public function completeTask(
        Task $task,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('complete' . $task->getId(), $token)) {
            throw new AccessDeniedException('Token CSRF invalide');
        }

    // Changer le statut
        $task->setStatus(TaskStatus::COMPLETED);
        $em->flush();

        $this->addFlash('success', 'Tâche marquée comme terminée');
        return $this->redirectToRoute('app_my_tasks');
}
}