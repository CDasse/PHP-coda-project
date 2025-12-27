<?php

namespace App\Controller\Wallets;

use App\DTO\XUserWalletDTO;
use App\Entity\Wallet;
use App\Form\XUserWalletType;
use App\Repository\UserRepository;
use App\Service\WalletService;
use App\Service\XUserWalletService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddUserController extends AbstractController
{
    #[Route('/wallets/{uid}/add-user', name: 'wallets_add_user', methods: ['GET', 'POST'])]
    public function index(
        Request            $request,
        #[MapEntity(mapping: ['uid' => 'uid'])]
        Wallet             $wallet,
        WalletService      $walletService,
        XUserWalletService $xUserWalletService,
        UserRepository     $userRepository
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

        $availableUsers = $walletService->findAvailableUsersForWallet($wallet);

        $dto = new XUserWalletDTO();

        $form = $this->createForm(XUserWalletType::class, $dto, [
            'available_users' => $availableUsers,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $userToAdd = $userRepository->find($dto->userId);

            try {
                $xUserWalletService->create($wallet, $userToAdd, $dto->role);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de l\'ajout de l\'utilisateur au portefeuille : ' . $e->getMessage());

                return $this->redirectToRoute('wallets_add_user', ['uid' => $wallet->getUid()]);
            }

            $this->addFlash('success', 'Utilisateur ajouté avec succès !');

            return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
        }

        return $this->render('wallets/add_user/index.html.twig', [
            'form' => $form,
            'wallet' => $wallet
        ]);
    }
}
