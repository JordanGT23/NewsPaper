<?php

namespace App\Form;

use App\Entity\Commentary;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentaryFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholderr' => "laisser votre commentaire ici",
                    'class' => 'editor' # Cette classe nous permet d'activer CkEditor
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;

        # Si l'utilisateur est connecté, alors on affichera le bouton submit.
        if($this->security->getUser()){
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => "Commenter <i class='bi bi-send'></i>",
                    'label_html' => true,
                    'validate' => false,
                    'attr' => [
                        'class' => 'd-block mx-auto my-3 col-3 btn btn-warning'
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentary::class,
        ]);
    }
}
