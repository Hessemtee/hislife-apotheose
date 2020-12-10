<?php

namespace App\Form;

use App\Entity\Child;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;



class UploadEditType extends AbstractType
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
        $children = array();

        foreach ($families as $family) {
            $child = $family->getChildren()->getValues();
            $children [$family->getName()] = $child;
        }
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom *'
            ])
            ->add('file', FileType::class, [
                'label' => 'Photo *',
                'data_class' => null,
                'required' => false,
                'mapped' => false,
            ])
            ->add('child', EntityType::class, [
                'label' => 'Enfant *',
                'class' => Child::class,
                'choices' => $children,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description ',
                'required' => false
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

