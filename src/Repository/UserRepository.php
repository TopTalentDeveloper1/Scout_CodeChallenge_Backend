<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * User Repository
 * Implements Repository pattern for User data access
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find all users with pagination
     * 
     * @param int $page Page number (1-based)
     * @param int $limit Number of items per page
     * @return array{users: User[], total: int, page: int, limit: int, totalPages: int}
     */
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');
        
        $total = count($qb->getQuery()->getResult());
        
        $users = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        $totalPages = ceil($total / $limit);
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ];
    }

    /**
     * Check if email already exists (excluding current user for updates)
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email);
        
        if ($excludeUserId !== null) {
            $qb->andWhere('u.id != :excludeId')
               ->setParameter('excludeId', $excludeUserId);
        }
        
        return $qb->getQuery()->getSingleScalarResult() > 0;
    }
}

