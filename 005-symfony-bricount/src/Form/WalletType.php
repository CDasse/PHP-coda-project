<?php

namespace App\Form;

use App\DTO\WalletDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class WalletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                "constraints" => [
                    new NotBlank(
                        message: "Le nom du portefeuille ne peut pas être vide"
                    ),
                    new Length(
                        min: 3,
                        max: 50,
                        minMessage: "Le nom du portefeuille doit au moins contenir 3 caractères de longueur",
                        maxMessage: "Le nom du portefeuille ne peut pas dépasser 50 caractères"
                    )
                ],
                "required" => true,
                "label" => "Nom du portefeuille",
                "help" => "Le nom du portefeuille doit être entre 3 et 50 caractères."
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WalletDTO::class,
        ]);
    }
}
