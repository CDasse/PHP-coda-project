<?php

namespace App\Service;

use App\Entity\Wallet;
use App\Repository\ExpenseRepository;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepository $expenseRepository
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
}
