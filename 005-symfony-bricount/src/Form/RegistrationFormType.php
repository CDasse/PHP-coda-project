<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez entrer une adresse email.',
                    ]),
                ],
                'required' => true,
                'label' => "Adresse email",
            ])
            ->add('name', TextType::class, [
                'mapped' => false,
                'label' => "Votre nom ou pseudo",
                'required' => true,
                'help' => "Le nom d'utilisateur doit contenir entre 3 et 30 caractères.",
                'constraints' => [
                    new NotBlank([
                        'message' => "Vous devez entrer votre nom d'utilisateur",
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => "Votre nom d'utilisateur doit contenir au minimum {{ limit }} caractères",
                        'maxMessage' => "Votre nom d'utilisateur doit contenir au maximum {{ limit }} caractères",
                    ])
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "J'accepte les conditions d'utilisation",
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation.",
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => "Mot de passe",
                'required' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'help' => "Le mot de passe doit contenir au minimum 6 caractères.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
