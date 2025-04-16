<?php

namespace App\Form;

use App\Entity\Members;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 180,
                    ]),
                ],
            ])
            ->add('email',null,[
                'label' => 'Adresse e-mail',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte de respecter les règles de l\'application, y compris :',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les règles pour vous inscrire.',
                    ]),
                ],
                'help' => "• Pas de messages offensants, harcelants ou haineux<br>
                        • Aucune image inappropriée, violente ou illégale<br>
                        • Respecter la vie privée des autres membres<br>
                        • N'envoyez pas de spam ou de publicités<br>
                        • Vous êtes responsable de ce que vous publiez<br>
                        • Le non-respect des règles peut entraîner un bannissement",
                'help_html' => true,
            ])

            ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'Male' => 'male',
                        'Female' => 'female',
                    ],
                    'placeholder' => 'sélectionnez votre sexe',
                    'required' => true,
                    'label' => 'Genre',
            ])
            ->add('plainPassword', PasswordType::class, [
                
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Members::class,
        ]);
    }
}
