<?php

namespace App\Form;

use App\DTO\ExpenseDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
                'divisor' => 100, //permet d'enregistrer la valeur en centimes dans la base de données
                'constraints' => [
                    new NotBlank(
                        message: 'Montant obligatoire.'
                    ),
                    new Positive(
                        message: 'Le montant doit être supérieur à 0.'
                    )
                ],
                'label' => 'Montant',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(
                        message: 'La description ne peut pas être vide.'
                    ),
                    new Length(
                        max: 200,
                        maxMessage: "La description ne doit pas contenir plus de 200 caractères."
                    )
                ],
                'label' => 'Description',
                'required' => true,
                'help' => "La description ne doit pas contenir plus de 200 caractères."
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExpenseDTO::class,
        ]);
    }
}
