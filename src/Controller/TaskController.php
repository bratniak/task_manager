<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/api/task/{id}', methods: ['GET'])]
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

        return $this->json($task, 200, [], ['groups' => 'task:read']);
    }

    #[Route('/api/task', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $headers = $request->headers->all();
        $body = json_decode($request->getContent());

        if (null === $body) {
            return $this->json(['message' => 'Invalid JSON body'], 400);
        }

        $headers = array_map(function ($item) {
            return $item[0];
        }, $headers);

        if (
            !isset($headers['content-type']) ||
            $headers['content-type'] !== 'application/json'
        ) {
            return $this->json([
                'message' => "Incorrect headers",
            ], 400);
        }

        $body = get_object_vars($body);

        $task = new Task();
        if (
            isset($body['title']) &&
            is_string($body['title']) &&
            !empty($body['title'])
        ) {
            $task->setTitle($body['title']);
        } else {
            $task->setTitle('Untitled');
        }

        if (
            isset($body['description']) &&
            is_string($body['description']) &&
            !empty($body['description'])
        ) {
            $task->setDescription($body['description']);
        } else {
            $task->setDescription('empty');
        }

        if (
            isset($body['status']) &&
            is_string($body['status']) &&
            !empty($body['status'])
        ) {
            $task->setStatus($body['status']);
        } else {
            $task->setStatus('todo');
        }

        $now = new \DateTimeImmutable();
        $task->setCreatedAt($now);
        $task->setUpdatedAt($now);

        try {
            $em->persist($task);
            $em->flush();

            return $this->json([
                'message' => "Task created",
                'id' => $task->getId()
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to create task',
            ], 500);
        }
    }

    #[Route('/api/task/{id}', methods: ['DELETE'])]
    public function delete(int $id, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $em->remove($task);

        try {
            $em->flush();
            return $this->json([
                'message' => "Task deleted",
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to delete task',
            ], 500);
        }
    }
}
