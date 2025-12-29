<?php

namespace App\Controller\Wallets;

use App\Entity\Wallet;
use App\Service\WalletService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettleController extends AbstractController
{
    #[Route('/wallets/{uid}/settle', name: 'wallets_settle', methods: ['POST', 'GET'])]
    public function index(
        #[MapEntity (mapping: ['uid' => 'uid'])]
        Wallet        $wallet,
        WalletService $walletService
    ): Response
    {
        $walletService->markAsSettled($wallet);

        $this->addFlash('success', 'Les comptes du portefeuille ont été marqués comme réglés.');

        return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);

    }
}
