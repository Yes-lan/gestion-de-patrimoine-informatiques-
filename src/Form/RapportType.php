<?php

namespace App\Form;

use App\Entity\Rapport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ========== Champ TITRE ==========
            ->add('titre', TextType::class, [
                'label' => 'Titre du rapport',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Rapport de suivi post-greffe',
                    'maxlength' => '255',
                ],
                'help' => 'Un titre court et descriptif',
            ])

            // ========== Champ CONTENU HTML ==========
            ->add('contenuHtml', TextareaType::class, [
                'label' => 'Contenu du rapport',
                'attr' => [
                    'class' => 'form-control ckeditor-init',
                    'rows' => 15,
                    'data-type' => 'ckeditortype',
                ],
                'help' => 'Formatez votre rapport avec les outils de la barre d\'édition',
            ])

            // ========== Champ STATUT ==========
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => 'brouillon',
                    'Finalisé' => 'finalisé',
                    'Archivé' => 'archivé',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rapport::class,
        ]);
    }
}
