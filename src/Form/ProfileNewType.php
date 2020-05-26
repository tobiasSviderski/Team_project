<?php

namespace App\Form;

use App\Entity\Profile;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false
            ])
            ->add('file', FileType::class,[
                'mapped' => false,
                'label' => 'File',
                'required' => false,
//                'constraints' => [
//                    new File([
//                        'maxSize' => '1024k',
//                        'mimeTypes' => [
//                            'application/json'
//                        ],
//                        'mimeTypesMessage' => 'Please upload a valid JSON document',
//                    ])
//                ]
            ])
            ->add('forAll', CheckboxType::class, [
                'mapped' => false,
                'data' => true,
                'label' => 'available for all',
                'required' => false
            ])
            ->add('enable', CheckboxType::class, [
                'data' => true,
                'label' => 'enable to be visible'
            ])
            ->add('subscriptions', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'class' => User::class,
                'property_path' => 'username',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC')
                        ->andWhere('u.enabled = 1');
                },
                'choice_label' => 'username'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
