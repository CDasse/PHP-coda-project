<?php

namespace App\Service;

use App\DTO\WalletDTO;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\XUserWallet;
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
        private readonly UserRepository         $userRepository
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

}
