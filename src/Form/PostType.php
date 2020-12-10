<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Family;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $people = $this->security->getUser();
        $families = $people->getFamilies($people);
        
        $builder
            ->add('title', null, [
                'label' => 'Titre *',
               
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message *',
                'required' => true
            ])
            //->add('people')
            ->add('family', EntityType::class, [
                'label' => 'Famille *',
                'class' => Family::class,
                'choices' => $families,
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
