<?php

namespace App\Form;

use App\Entity\Family;
use App\Entity\Picture;
use App\Entity\People;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PictureType extends AbstractType
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
                'constraints' => new NotBlank,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('file', FileType::class, [
                'label' => 'Photo *',
                'data_class' => null,
                'constraints' => [
                    new Image([
                        // on peut mettre une taille max ou min
                        ])
                ],
            ])
            ->add('family', EntityType::class, [
                'label' => 'Famille *',
                'class' => Family::class,
                'choices' => $families,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
