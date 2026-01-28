<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * User Controller
 * Thin controller that delegates to UserService
 * Handles REST API endpoints for user management
 */
#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * GET /api/users
     * List all users with pagination
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));

        $result = $this->userService->getUsers($page, $limit);

        // Serialize users to array
        $usersData = [];
        foreach ($result['users'] as $user) {
            $usersData[] = $this->serializeUser($user);
        }

        return $this->json([
            'data' => $usersData,
            'pagination' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total' => $result['total'],
                'totalPages' => $result['totalPages']
            ]
        ], Response::HTTP_OK);
    }

    /**
     * GET /api/users/{id}
     * Get a single user by ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->json([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $this->serializeUser($user)
        ], Response::HTTP_OK);
    }

    /**
     * POST /api/users
     * Create a new user
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'error' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->userService->createUser($data);

        if (!$result['success']) {
            return $this->json([
                'error' => 'Validation failed',
                'errors' => $result['errors']
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => $this->serializeUser($result['user']),
            'message' => 'User created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/users/{id}
     * Update an existing user
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'error' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->userService->updateUser($id, $data);

        if (!$result['success']) {
            $statusCode = isset($result['errors']['id']) 
                ? Response::HTTP_NOT_FOUND 
                : Response::HTTP_BAD_REQUEST;
            
            return $this->json([
                'error' => 'Update failed',
                'errors' => $result['errors']
            ], $statusCode);
        }

        return $this->json([
            'data' => $this->serializeUser($result['user']),
            'message' => 'User updated successfully'
        ], Response::HTTP_OK);
    }

    /**
     * DELETE /api/users/{id}
     * Delete a user
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $result = $this->userService->deleteUser($id);

        if (!$result['success']) {
            return $this->json([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'message' => 'User deleted successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Serialize user entity to array
     */
    private function serializeUser($user): array
    {
        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'status' => $user->getStatus(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $user->getUpdatedAt()?->format('Y-m-d\TH:i:s\Z')
        ];
    }
}

