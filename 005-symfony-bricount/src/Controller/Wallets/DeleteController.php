<?php

namespace App\Controller\Wallets;

use App\Entity\Wallet;
use App\Service\WalletService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteController extends AbstractController
{
    #[Route('/wallets/{uid}/delete', name: 'wallets_delete', methods: ['GET'])]
    public function index(
        Request       $request,
        #[MapEntity(mapping: ["uid" => "uid"])]
        Wallet        $wallet,
        WalletService $walletService
    ): Response
    {
        $connectedUser = $this->getUser();

        $xUserWallet = $walletService->getUserAccessOnWallet($connectedUser, $wallet);

        if (true === is_null($xUserWallet)) {
            $this->addFlash("danger", "Vous n'avez pas accès à ce portefeuille");

            return $this->redirectToRoute('wallets_list');
        }

        if ("admin" !== $xUserWallet->getRole()) {
            $this->addFlash("danger", "Vous n'avez pas le droit de supprimer ce portefeuille");

            return $this->redirectToRoute('wallets_list');
        }

        $wallet = $walletService->deleteWallet($wallet, $connectedUser);

        $this->addFlash('success', 'Portefeuille supprimé avec succès !');

        return $this->redirectToRoute('wallets_list');
    }
}
