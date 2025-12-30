<?php

namespace App\Service;

use App\DTO\WalletDTO;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\XUserWallet;
use App\Repository\ExpenseRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use App\Repository\XUserWalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class WalletService
{

    public function __construct(
        private readonly WalletRepository       $walletRepository,
        private readonly XUserWalletRepository  $xUserWalletRepository,
        private readonly EntityManagerInterface $em,
        private readonly XUserWalletService     $xUserWalletService,
        private readonly UserRepository         $userRepository,
        private readonly ExpenseRepository      $expenseRepository
    )
    {
    }

    public function findWalletsForUser(User $user): array
    {
        return $this->walletRepository->findWalletsForUser($user);
    }

    public function getUserAccessOnWallet(User $user, Wallet $wallet): null|XUserWallet
    {
        $xUserWallet = null;

        try {
            $xUserWallet = $this->xUserWalletRepository->getUserAccessOnWallet($user, $wallet);
        } catch (\Exception $e) {

        }

        return $xUserWallet;
    }

    public function createWallet(WalletDTO $dto, User $user): Wallet
    {
        $wallet = new Wallet();

        $wallet->setUid(Uuid::v7()->toString());
        $wallet->setLabel($dto->name);
        $wallet->setCreatedBy($user);
        $wallet->setCreatedDate(new \DateTime());

        $this->em->persist($wallet);
        $this->em->flush();

        $this->xUserWalletService->create($wallet, $user, "admin");

        return $wallet;
    }

    public function updateWallet(Wallet $wallet, WalletDTO $dto, User $updater): Wallet
    {
        $wallet->setLabel($dto->name);
        $wallet->setUpdatedBy($updater);
        $wallet->setUpdatedDate(new \DateTime());

        $this->em->persist($wallet);
        $this->em->flush();

        return $wallet;
    }

    public function findAvailableUsersForWallet(Wallet $wallet): array
    {
        return $this->userRepository->findAvailableUsersForWallet($wallet);
    }


    public function deleteWallet(Wallet $wallet, User $deletor): void
    {
        $wallet->setisDeleted(true);
        $wallet->setDeletedBy($deletor);
        $wallet->setDeletedDate(new \DateTime());

        $this->em->persist($wallet);
        $this->em->flush();
    }

    public function updateTotalBalance(Wallet $wallet): void
    {
        $totalBalance = $this->walletRepository->calculateTotalBalance($wallet);

        $wallet->setTotalAmount($totalBalance);

        $this->em->persist($wallet);
        $this->em->flush();
    }

    public function markAsSettled(Wallet $wallet): void
    {
        $wallet->setLastSettlementDate(new \DateTime());

        $this->em->persist($wallet);
        $this->em->flush();
    }

    public function getUserBalances(Wallet $wallet): array
    {
        $totalAmount = $this->expenseRepository->calculateTotalBalanceSinceLastSettlement($wallet);
        $expenses = $this->expenseRepository->findExpensesSinceLastSettlement($wallet);

        $allMembers = $this->xUserWalletRepository->findActiveMembers($wallet);
        $userCount = count($allMembers);

        if ($userCount === 0 || $totalAmount <= 0) {
            return [];
        }

        $fairShare = $totalAmount / $userCount;

        $balances = [];
        foreach ($allMembers as $user) {
            $balances[$user->getName()] = 0;
        }

        foreach ($expenses as $expense) {
            $name = $expense->getCreatedBy()->getName();
            $balances[$name] += $expense->getAmount();
        }

        foreach ($balances as $name => $amount) {
            $balances[$name] = round(($balances[$name] - $fairShare), 2);
        }

        $creditors = [];
        $debtors = [];

        foreach ($balances as $name => $balance) {
            if ($balance > 0) {
                $creditors[$name] = $balance;
            } elseif ($balance < 0) {
                $debtors[$name] = abs($balance);
            }
        }

        $transfers = [];

        while (!empty($creditors) && !empty($debtors)) {
            $creditorName = array_key_first($creditors);
            $debtorName = array_key_first($debtors);

            $amount = min($creditors[$creditorName], $debtors[$debtorName]);
            $transfers[$debtorName][$creditorName] = $amount;

            $creditors[$creditorName] -= $amount;
            $debtors[$debtorName] -= $amount;

            if ($creditors[$creditorName] == 0) {
                unset($creditors[$creditorName]);
            }
            if ($debtors[$debtorName] == 0) {
                unset($debtors[$debtorName]);
            }
        }

        return $transfers;
    }

}
