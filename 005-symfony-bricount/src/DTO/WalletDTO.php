<?php

namespace App\DTO;

use App\Entity\Wallet;

class WalletDTO
{
    public string $name;

    public static function fromEntity(Wallet $wallet): WalletDTO
    {
        // on crée une nouvelle instance de la DTO
        $dto = new self();

        // on remplit les propriétés de la DTO avec les données de l'entité
        $dto->name = $wallet->getLabel();

        return $dto;
    }
}
