<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Location Name',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('street', TextType::class, [
                'label' => 'Street',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
