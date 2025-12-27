<?php

namespace App\Repository;

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
}
