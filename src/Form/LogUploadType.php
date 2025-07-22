<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;


class LogUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploadFile', FileType::class, [
                'label' => 'Choose a file to upload',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'Please upload a file.',
                    ]),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'text/plain',
                            'text/x-log',
                        ],
                        'mimeTypesMessage' => 'Only .log and .txt files are allowed.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id'   => 'log_upload', // unique token ID
        ]);
    }
}
