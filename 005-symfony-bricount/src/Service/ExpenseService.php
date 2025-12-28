<?php

namespace App\Service;

use App\DTO\ExpenseDTO;
use App\Entity\Expense;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepository      $expenseRepository,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function findExpensesForWallet(Wallet $wallet, int $page, int $limit): array
    {
        return $this->expenseRepository->findExpensesForWallet($wallet, $page, $limit);
    }

    public function countExpensesForWallet(Wallet $wallet): int
    {
        return $this->expenseRepository->countExpensesForWallet($wallet);
    }

    public function createExpense(Wallet $wallet, ExpenseDTO $dto, User $creator): Expense
    {
        $expense = new Expense();

        $expense->setUid(Uuid::v7()->toString());
        $expense->setAmount($dto->amount);
        $expense->setDescription($dto->description);
        $expense->setWallet($wallet);
        $expense->setCreatedBy($creator);
        $expense->setCreatedDate(new \DateTime());

        $this->em->persist($expense);
        $this->em->flush();

        return $expense;
    }

    public function deleteExpense(Expense $expense, User $deletor): void
    {
        $expense->setisDeleted(true);
        $expense->setDeletedBy($deletor);
        $expense->setDeletedDate(new \DateTime());

        $this->em->persist($expense);
        $this->em->flush();
    }
}
