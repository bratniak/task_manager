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

use function PHPUnit\Framework\isString;

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

    #[Route('/api/task{id}', methods: ['GET'])]
    public function show(int $id, TaskRepository $taskRepository): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $data = [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
        ];

        return $this->json($data);
    }

    #[Route('/api/task', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $headers = $request->headers->all();
        $body = json_decode($request->getContent());

        if (!$headers || !$body) {
            return $this->json([
                'message' => "Incorrect headers or body",
            ], 201);
        }

        $headers = array_map(function ($item) {
            return $item[0];
        }, $headers);

        if (!isset($headers['content-type']) || $headers['content-type'] !== 'application/json') {
            return $this->json([
                'message' => "Incorrect headers",
            ], 201);
        }

        $body = get_object_vars($body);

        $task = new Task();
        if (isset($body['title']) && isString($body['title'])) {
            $task->setTitle($body['title'] ?? 'Untitled');
        }

        if (isset($body['description']) && isString($body['description'])) {
            $task->setDescription($body['description'] ?? 'empty');
        }

        if (isset($body['status']) && isString($body['status'])) {
            $task->setStatus($body['status'] ?? 'todo');
        }

        try {
            $em->persist($task);
            $em->flush();

            return $this->json([
                'message' => "Task created",
                'id' => $task->getId()
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to create task',
            ], 500);
        }
    }
}
