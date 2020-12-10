<?php

namespace App\Form;

use App\Entity\People;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class PeopleType extends AbstractType
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $people = $this->security->getUser();

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new Email,
                    new NotBlank,
            ]])
            ->add('picture', FileType::class, [
                'label' => 'Photo',
                'data_class' => null,
                'required' => false,
                'mapped' => false,
            ])
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'label' => 'Mot de passe *',
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'invalid_message' => 'les champs doivent match comme sur tinder',
                'first_options' => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Repeter le mot de passe'),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer le mot de passe,'
                        ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir minimum {{ limit }} caractères ',
                        // max length allowed by Symfony for security reasons
                        'max' => 18,
                        ]),
                    ]
                    
                    ));
     
                
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => People::class,
        ]);
    }
}

;