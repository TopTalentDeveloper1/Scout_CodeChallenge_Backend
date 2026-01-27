<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * User Service
 * Implements Service layer for business logic
 * Handles validation and business rules
 */
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Get paginated list of users
     */
    public function getUsers(int $page = 1, int $limit = 10): array
    {
        return $this->userRepository->findAllPaginated($page, $limit);
    }

    /**
     * Get a single user by ID
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Create a new user
     * Validates data and checks for duplicate email
     */
    public function createUser(array $data): array
    {
        // Validate required fields
        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if email already exists
        if ($this->userRepository->emailExists($data['email'])) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email already exists']
            ];
        }

        // Create new user
        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);
        $user->setRole($data['role']);
        $user->setStatus($data['status'] ?? 'pending');

        // Validate entity
        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $property = $violation->getPropertyPath();
                $errors[$property] = $violation->getMessage();
            }
            return ['success' => false, 'errors' => $errors];
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ['success' => true, 'user' => $user];
    }

    /**
     * Update an existing user
     */
    public function updateUser(int $id, array $data): array
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['id' => 'User not found']];
        }

        // Validate required fields
        $errors = $this->validateUserData($data, true);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if email already exists (excluding current user)
        if (isset($data['email']) && $this->userRepository->emailExists($data['email'], $id)) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email already exists']
            ];
        }

        // Update user fields
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }
        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }

        // Validate entity
        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $property = $violation->getPropertyPath();
                $errors[$property] = $violation->getMessage();
            }
            return ['success' => false, 'errors' => $errors];
        }

        $this->entityManager->flush();

        return ['success' => true, 'user' => $user];
    }

    /**
     * Delete a user
     */
    public function deleteUser(int $id): array
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['id' => 'User not found']];
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return ['success' => true];
    }

    /**
     * Validate user data
     * Basic validation for required fields and email format
     */
    private function validateUserData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate || isset($data['firstName'])) {
            if (empty($data['firstName'])) {
                $errors['firstName'] = 'First name is required';
            }
        }

        if (!$isUpdate || isset($data['lastName'])) {
            if (empty($data['lastName'])) {
                $errors['lastName'] = 'Last name is required';
            }
        }

        if (!$isUpdate || isset($data['email'])) {
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
        }

        if (!$isUpdate || isset($data['role'])) {
            if (empty($data['role'])) {
                $errors['role'] = 'Role is required';
            } elseif (!in_array($data['role'], ['admin', 'manager', 'user'])) {
                $errors['role'] = 'Role must be one of: admin, manager, user';
            }
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], ['active', 'inactive', 'pending'])) {
                $errors['status'] = 'Status must be one of: active, inactive, pending';
            }
        }

        return $errors;
    }
}

