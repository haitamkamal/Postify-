<?php

// src/Form/ProfileImageType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class ProfileImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('profile_image', FileType::class, [
                'label' => 'Upload a new profile image',
                'mapped' => false,  // We don't want to map this field to any entity property
                'required' => false,
                'attr' => [
                    'accept' => 'image/*'  // Accepts only image files
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
