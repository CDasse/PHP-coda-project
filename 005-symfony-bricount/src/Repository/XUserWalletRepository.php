<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\XUserWallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XUserWallet>
 */
class XUserWalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XUserWallet::class);
    }

    public function getUserAccessOnWallet(User $user, Wallet $wallet): null|XUserWallet
    {
        return
            $this
                ->createQueryBuilder('xuw')
                ->innerJoin('xuw.targetUser', 'u', 'WITH', 'u.id = :userId')
                ->innerJoin('xuw.wallet', 'w', 'WITH', 'w.isDeleted = false AND w.id = :walletId')
                ->andWhere('xuw.isDeleted = false')
                ->setParameter('userId', $user->getId())
                ->setParameter('walletId', $wallet->getId())
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findActiveMembers(Wallet $wallet): array
    {
        $results = $this
            ->createQueryBuilder('xuw')
            ->innerJoin('xuw.targetUser', 'u')
            ->addSelect('u')
            ->where('xuw.wallet = :wallet')
            ->andWhere('xuw.isDeleted = false')
            ->setParameter('wallet', $wallet)
            ->getQuery()
            ->getResult();

        return array_map(function ($xuw) {
            return $xuw->getTargetUser();
        }, $results);
    }

}
