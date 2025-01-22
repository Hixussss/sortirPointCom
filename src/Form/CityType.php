<?php

// src/Form/CityType.php
namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CityType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('form.city_name'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('postalCode', TextType::class, [
                'label' => $this->translator->trans('form.city_postal_code'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
