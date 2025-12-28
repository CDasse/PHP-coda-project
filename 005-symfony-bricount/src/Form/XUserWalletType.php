<?php

namespace App\Form;

use App\DTO\XUserWalletDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class XUserWalletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $userChoices = [];

        foreach ($options['available_users'] as $user) {
            $userChoices[$user->getName() . "(" . $user->getEmail() . ")"] = $user->getId();
        }

        $builder
            ->add('userId', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices' => $userChoices,
                "constraints" => [
                    new NotBlank(
                        message: "Vous devez sélectionner un utilisateur à ajouter dans le portefeuille"
                    )
                ]
            ])
            ->add("role", ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    "Administrateur - Peut modifier/supprimer le portefeuille et son contenu" => "admin",
                    "Utilisateur - Peut simplement ajouter des dépenses au portefeuille" => "user"
                ],
                'constraints' => [
                    new NotBlank(
                        message: "Vous devez saisir un rôle pour l'utilisateur"
                    )
                ]
            ])
            ->add("submit", SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => XUserWalletDTO::class,
            'available_users' => [],
        ]);

        $resolver->setAllowedTypes('available_users', 'array');

    }
}
