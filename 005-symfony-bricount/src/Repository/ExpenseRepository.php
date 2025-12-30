<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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

    public function findExpensesSinceLastSettlement(Wallet $wallet): array
    {
        return
            $this
                ->createQueryBuilder('e')
                ->innerJoin('e.wallet', 'w', 'WITH', 'w.isDeleted = false AND w.id = :walletId')
                ->andWhere('e.isDeleted = false AND (w.lastSettlementDate IS NULL OR w.lastSettlementDate < e.createdDate)')
                ->setParameter('walletId', $wallet->getId())
                ->getQuery()
                ->getResult();
    }

    public function calculateTotalBalanceSinceLastSettlement(Wallet $wallet): int
    {
        $sql = <<<SQL
        SELECT
            COALESCE(SUM(e.amount), 0) as amount
        FROM expense e
        INNER JOIN wallet w ON e.wallet_id = w.id
        WHERE e.is_deleted = false
          AND w.id = :walletId
          AND (w.last_settlement_date IS NULL OR e.created_date > w.last_settlement_date)
        SQL;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        $result = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('walletId', $wallet->getId())
            ->getSingleScalarResult();

        return (int)$result;
    }

}
