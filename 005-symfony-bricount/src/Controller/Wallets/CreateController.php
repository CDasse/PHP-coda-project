<?php

namespace App\Controller\Wallets;

use App\DTO\WalletDTO;
use App\Form\WalletType;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateController extends AbstractController
{
    #[Route('/wallets/create', name: 'wallets_create', methods: ['GET', 'POST'])]
    public function index(
        Request       $request,
        WalletService $walletService
    ): Response
    {
        $dto = new WalletDTO();

        $form = $this->createForm(WalletType::class, $dto);

        // traitement du formulaire par symfony, validations, etc.
        $form->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // récupération des données du formulaire sous forme de la DTO WalletDTO
            $dto = $form->getData();

            $wallet = null;
            try {
                // traitements métier pour créer le wallet via le service WalletService
                $wallet = $walletService->createWallet($dto, $this->getUser());
            } catch (\Exception $e) {
                // en cas d'erreur, ajout d'un message flash pour indiquer l'erreur
                $this->addFlash('error', 'Erreur lors de la création du portefeuille');

                // redirection vers la page de création du wallet
                return $this->redirectToRoute('wallets_create');
            }

            // ajout d'un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', 'Portefeuille créé avec succès !');

            // redirection vers le détail du wallet nouvellement créé
            return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
        }

        return $this->render('wallets/create/index.html.twig', [
            'form' => $form
        ]);
    }
}
