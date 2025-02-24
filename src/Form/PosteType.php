<?php

namespace App\Form;

use App\Entity\Poste;
use App\Entity\Category; // Importez l'entité Category
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PosteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu')
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPG, PNG, max 2MB)',
                'mapped' => false, // Ce champ n'est pas lié à une propriété de l'entité
                'required' => false, // Le fichier n'est pas obligatoire
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class, // Entité Category
                'choice_label' => 'tag', // Champ à afficher dans la liste déroulante
                'multiple' => true, // Permet de sélectionner plusieurs catégories
                'expanded' => true, // Affiche les catégories sous forme de cases à cocher
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Veuillez sélectionner au moins une catégorie.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poste::class,
        ]);
    }
}