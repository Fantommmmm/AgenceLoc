<?php

namespace App\Form;

use DateTime;
use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class EditCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date_heure_depart', DateTimeType::class,[
            'widget' => 'single_text',
            'attr' => [
                'min' => date_format(new DateTime('+ 1 days'), 'Y-m-d H:i')
            ]
        ])
        ->add('date_heure_fin', DateTimeType::class,[
            'widget' => 'single_text',
            'attr' => [
                'min' => date_format(new DateTime('+ 2 days'), 'Y-m-d H:i')
            ]
        ])
            ->add('prix_total');

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
