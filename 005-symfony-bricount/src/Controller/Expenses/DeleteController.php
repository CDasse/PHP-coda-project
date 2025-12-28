<?php

namespace App\Controller\Expenses;

use App\Entity\Expense;
use App\Entity\Wallet;
use App\Service\ExpenseService;
use App\Service\WalletService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteController extends AbstractController
{
    #[Route('/wallets/{wallet_uid}/expenses/{expense_uid}/delete', name: 'expenses_delete', methods: ['GET'])]
    public function index(
        Request        $request,
        #[MapEntity (mapping: ['wallet_uid' => 'uid'])]
        Wallet         $wallet,
        #[MapEntity (mapping: ['expense_uid' => 'uid'])]
        Expense        $expense,
        ExpenseService $expenseService,
        WalletService  $walletService
    ): Response
    {
        $connectedUser = $this->getUser();

        $xUserWallet = $walletService->getUserAccessOnWallet($connectedUser, $wallet);

        if (true === is_null($xUserWallet)) {
            $this->addFlash("danger", "Vous n'avez pas accès à ce portefeuille");

            return $this->redirectToRoute('wallets_list');
        }

        if ("admin" !== $xUserWallet->getRole()) {
            $this->addFlash("danger", "Vous n'avez pas le droit de supprimer cette dépense");

            return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
        }

        $expense = $expenseService->deleteExpense($expense, $connectedUser);

        $this->addFlash('success', 'Dépense supprimée avec succès !');

        return $this->redirectToRoute('wallets_show', ['uid' => $wallet->getUid()]);
    }
}
