<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class,[
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
            ->add('attemptToSave', SubmitType::class, [
                'label' => 'Attempt to upload a file'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Upload a file'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
