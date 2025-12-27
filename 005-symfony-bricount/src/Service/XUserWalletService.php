<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\XUserWallet;
use App\Repository\XUserWalletRepository;
use Doctrine\ORM\EntityManagerInterface;

class XUserWalletService
{

    public function __construct(
        private readonly XUserWalletRepository  $xUserWalletRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function create(Wallet $wallet, User $user, string $role): XUserWallet
    {
        $xUserWallet = $this->xUserWalletRepository->getUserAccessOnWallet($user, $wallet);

        if (true === is_null($xUserWallet)) {
            $xUserWallet = new XUserWallet();
            $xUserWallet->setTargetUser($user);
            $xUserWallet->setWallet($wallet);
            $xUserWallet->setCreatedBy($user);
            $xUserWallet->setCreatedDate(new \DateTime());
        } else {
            $xUserWallet->setUpdatedBy($user);
            $xUserWallet->setUpdatedDate(new \DateTime());
        }

        $xUserWallet->setRole($role);

        $this->em->persist($xUserWallet);
        $this->em->flush();

        return $xUserWallet;
    }

}
