<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => "Titre de l'article"
            ])
            ->add('subtitle', TextType::class, [
                'label' => "Sous-titre de l'article"
            ])
            ->add('content', TextType::class, [
                'label' => "Contenu"
            ])
            ->add('photo', FileType::class, [
                'label' => "Image",
                'data_class' => null,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => "Categorie de l'article",
                'choice_label' => 'name',
                
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Creér',
                'validate' => false,
                'attr' => [
                    'class' => 'd-block mx-auto my-3 col-3 bbtn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'allow_file_upload' => true,
            'photo' => null
        ]);
    }
}
