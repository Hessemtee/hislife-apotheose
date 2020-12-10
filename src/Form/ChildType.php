<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Family;
use App\Entity\People;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;



class ChildType extends AbstractType
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
            ->add('lastname', null, [
                'label' => 'Nom *',
                'constraints' => new NotBlank,
            ])
            ->add('firstname', null, [
                'label' => 'Prénom *',
                'constraints' => new NotBlank,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Sexe *',
                'choices' => [
                    'Fille' => 'Fille',
                    'Garçon' => 'Garçon',
                    'Autre' => 'Autre',
                 ],
                
            ])
            ->add('birthdate',  DateType::class, [
                // renders it as a single text box
                'label' => 'Date Anniversaire *',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
                'format' =>  'dd/MM/yyyy'
            ])
            ->add('picture', FileType::class, [
                'label' => 'Photo',
                'data_class' => null,
                'constraints' => [
                    new Image([
                        // on peut mettre une taille max ou min
                        ])
                ],
                'required' => false
            ])
            ->add('families', EntityType::class, [
                'label' => 'Famille *',
                'class' => Family::class,
                'choices' => $families,
                'multiple' => true
                ])
                            
            
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
