<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        // Ajout des champs du formulaire d'inscription
            ->add('email', null, [
                'constraints' => [
                    new NotBlank(['message' => 'L’adresse email est requise.']),
                    new Email(['message' => 'L’adresse email n’est pas valide.']),
                    new Length(['max' => 180])
                ],
                'attr' => ['placeholder' => 'Adresse email']
            ])

            ->add('pseudo', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Le pseudo est requis.']),
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ]+$/',
                        'message' => 'Le pseudo ne doit contenir que des lettres sans chiffres ni symboles.',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 20,
                        'minMessage' => 'Le pseudo doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le pseudo ne doit pas dépasser {{ limit }} caractères.',
                    ])
                ],
                'label' => false,
                'required' => true,
                'attr' => ['placeholder' => 'Pseudo']
            ])

            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Passant' => 'passant',
                    'Veilleur' => 'veilleur',
                    'Bâtisseur' => 'bâtisseur'
                ]
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required' => true,
                'first_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Mot de passe'],
                    'constraints' => [
                        new NotBlank(['message' => 'Le mot de passe est requis.']),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/',
                            'message' => 'Au moins 8 caractères, avec une majuscule, une minuscule, un chiffre et un symbole.',
                        ])
                    ],
                ],
                'second_options' => ['label' => false, 'attr' => ['placeholder' => 'Confirmer le mot de passe']],
                'mapped' => false,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
