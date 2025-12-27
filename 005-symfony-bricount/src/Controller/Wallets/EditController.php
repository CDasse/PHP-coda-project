<?php

namespace App\Controller\Wallets;

use App\DTO\WalletDTO;
use App\Entity\Wallet;
use App\Form\WalletType;
use App\Service\WalletService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EditController extends AbstractController
{
    #[Route('/wallets/{uid}/edit', name: 'wallets_edit', methods: ['GET', 'POST'])]
    public function index(
        Request       $request,
        #[MapEntity(mapping: ['uid' => 'uid'])]
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
            $this->addFlash("danger", "Vous n'avez pas le droit de modifier ce portefeuille");

            return $this->redirectToRoute('wallets_list');
        }

        $dto = WalletDTO::fromEntity($wallet);

        $form = $this->createForm(WalletType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            try {
                $wallet = $walletService->updateWallet($wallet, $dto, $connectedUser);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la création du portefeuille');

                return $this->redirectToRoute('wallets_edit', ['uid' => $wallet->getUid()]);
            }

            $this->addFlash('success', 'Portefeuille modifié avec succès !');

            return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
        }

        return $this->render('wallets/edit/index.html.twig', [
            'form' => $form,
            'wallet' => $wallet
        ]);
    }
}
