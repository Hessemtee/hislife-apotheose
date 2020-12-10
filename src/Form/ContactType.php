<?php

namespace App\Form;

use App\Entity\Family;
use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;

class ContactType extends AbstractType
{

    private $security;

    public function __construct (Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $people = $this->security->getUser();
       $families = $people->getFamilies($people);

        $builder
            ->add('name', null, [
                'label' => 'Nom *',
                'constraints' => new NotBlank,
            ])
            ->add('job', null, [
                'label' => 'Métier *',
                'required' => false,
            ])
            ->add('address', null, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Télephone',
                'required' => false,
                'constraints' => new Assert\Length([
                    'min' => 10,
                    'max' => 10,
                    'charsetMessage' => 'Le numéro de téléphone doit contenir 10 chiffres'
            ])])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Email()
                ],
                'required' => false,
            ])
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
            'data_class' => Contact::class,
        ]);
    }
}
