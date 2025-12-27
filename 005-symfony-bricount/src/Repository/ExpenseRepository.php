<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findExpensesForWallet(Wallet $wallet, int $page, int $limit): array
    {
        return
            $this
                ->createQueryBuilder('e')
                ->innerJoin('e.wallet', 'w', 'WITH', 'w.isDeleted = false AND w.id = :walletId')
                ->andWhere('e.isDeleted = false')
                ->orderBy('e.createdDate', 'DESC')
                ->setParameter('walletId', $wallet->getId())
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    public function countExpensesForWallet(Wallet $wallet): int
    {
        return $this->count([
            'wallet' => $wallet,
            'isDeleted' => false
        ]);
    }

}
