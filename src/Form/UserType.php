<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('password')
            ->add('enabled')
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'choices' => [
                    'User with downloads' => User::ROLE_DOWNLOADER,
                    'User with uploads' => User::ROLE_UPLOADER,
                    'Admin' => User::ROLE_ADMIN
                ]
            ])
        ;

        //$builder->get('roles')->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
