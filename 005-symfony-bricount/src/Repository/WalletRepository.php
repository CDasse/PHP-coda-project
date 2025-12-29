<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\XUserWallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findWalletsForUser(User $user): array
    {
        $qb = $this
            ->createQueryBuilder('w')
            ->innerJoin(XUserWallet::class, 'xuw', 'WITH', 'xuw.wallet = w.id AND xuw.isDeleted = false AND xuw.targetUser = :user')
            ->andWhere('w.isDeleted = false')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function calculateTotalBalance(Wallet $wallet): int
    {
        return
            $this
                ->createQueryBuilder('w')
                ->select('COALESCE(SUM(e.amount), 0)')
                ->leftJoin(Expense::class, 'e', 'WITH', 'w.id = e.wallet AND e.isDeleted = false')
                ->andWhere('w.isDeleted = false')
                ->andWhere('w.id = :walletId')
                ->setParameter('walletId', $wallet->getId())
                ->getQuery()
                ->getSingleScalarResult();
    }
}
