<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    #[Route('/api/tasks', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();

        $data = array_map(fn(Task $task) => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
        ], $tasks);

        return $this->json($data);
    }

    #[Route('/api/task', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em) : JsonResponse {

        $data = json_decode($request);

        $task = new Task();
        $task->setTitle($data['title'] ?? 'Untitled');
        $task->setDescription($data['description'] ?? 'empty');
        $task->setStatus($data['status' ?? 'todo']);

        $em->persist($task);
        $em->flush();

        //handle db
        return $this->json([
            'message' => "Task created",
            'id' => $task->getId()
        ], 201);
    }
}
