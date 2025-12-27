<?php

namespace App\Controller\Expenses;

use App\DTO\ExpenseDTO;
use App\Entity\Wallet;
use App\Form\ExpenseType;
use App\Service\ExpenseService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateController extends AbstractController
{
    #[Route('/wallets/{uid}/expenses/create', name: 'expenses_create', methods: ['POST', 'GET'])]
    public function index(
        Request        $request,
        #[MapEntity (mapping: ['uid' => 'uid'])]
        Wallet         $wallet,
        ExpenseService $expenseService
    ): Response
    {
        $dto = new ExpenseDTO();

        $form = $this->createForm(ExpenseType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $expense = null;

            try {
                $expense = $expenseService->createExpense($wallet, $dto, $this->getUser());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());

                // redirection vers la page de création du wallet
                return $this->redirectToRoute('expenses_create', ['uid' => $wallet->getUid()]);
            }

            $this->addFlash('success', 'Dépense créé avec succès !');

            // redirection vers le détail du wallet nouvellement créé
            return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
        }


        return $this->render('expenses/create/index.html.twig', [
            'form' => $form,
        ]);
    }
}
