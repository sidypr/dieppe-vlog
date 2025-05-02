<?php

namespace App\Form;

use App\Entity\Film;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class FilmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire'])
                ],
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Fichier vidéo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240M',
                        'mimeTypes' => [
                            'video/mp4',
                            'video/webm',
                            'video/ogg'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier vidéo valide (MP4, WebM, OGG)',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }} {{ suffix }}.'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'video/mp4,video/webm,video/ogg'
                ],
                'help' => 'Formats acceptés : MP4, WebM, OGG (max. 10Go)'
            ])
            ->add('videoUrl', UrlType::class, [
                'label' => 'URL de la vidéo (YouTube, Vimeo, etc.)',
                'required' => false,
                'constraints' => [
                    new Url(['message' => 'L\'URL n\'est pas valide'])
                ],
                'help' => 'Exemple : https://www.youtube.com/watch?v=XXXX ou https://vimeo.com/XXXX',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://www.youtube.com/watch?v=...'
                ]
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => true,
                    'Inactif' => false
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
        ]);
    }
} 