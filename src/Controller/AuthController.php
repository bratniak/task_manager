<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/register', methods: ['POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $pwdHasher
    ): JsonResponse {

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

        $data = get_object_vars($body);

        if (!isset($data['email'], $data['password'])) {
            return $this->json([
                'Error' => 'Missing email or password'
            ], 400);
        }

        $user = new User();

        $user->setEmail($data['email']);

        $hashedPassword = $pwdHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles([]);

        $em->persist($user);

        try {
            $em->flush();
            return $this->json([
                'error' => 'User registered successfully',
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'User registered unsuccessfully',
            ], 500);
        }
    }
}
