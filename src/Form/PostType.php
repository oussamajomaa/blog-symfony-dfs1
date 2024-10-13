<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'input input-bordered input-primary w-full  mb-3',
                    'placeholder' => 'Saisir le titre'
                ],
                'label' => false
            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'textarea textarea-primary w-full  mb-3',
                    'placeholder' => "Saisir le contenu de l'article",
                    'rows' => 10
                ],
                'label' => false
            ])
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(),  // Date et heure actuelles par défaut
                'attr' => [
                    'class' => 'input input-bordered input-primary w-full mb-3',
                    'placeholder' => 'Saisir la date et l\'heure'
                ],
                'label' => false
            ])
            // ->add('image',  TextType::class, [
            //     'attr' => [
            //         'class' => 'input input-bordered input-primary w-full  mb-3',
            //         'placeholder' => "Saisir l'url de l'image"
            //     ],
            //     'label' => false,
            //     'required' => false
            // ])

            ->add('imageFile', FileType::class, [
                'attr' => [
                    'class' => 'file-input file-input-bordered file-input-primary w-full mb-3',
                    'placeholder' => "Télécharger une image"
                ],
                'label' => false,
                'mapped' => false, // Cela signifie que ce champ ne sera pas mappé à l'entité directement
                'required' => false
            ])

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'select select-primary w-full  mb-3'
                ],
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
