<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('form.event_name'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('startDate', null, [
                'label' => $this->translator->trans('form.event_start_date'),
                'widget' => 'single_text',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('duration', IntegerType::class, [
                'label' => $this->translator->trans('form.event_duration'),
                'attr' => [
                    'class' => 'p-3 border border-gray-300 rounded-lg w-full',
                    'min' => 30,
                    'max' => 600,
                ],
            ])
            ->add('registrationEndDate', null, [
                'label' => $this->translator->trans('form.event_registration_end_date'),
                'widget' => 'single_text',
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('maxRegistrations', IntegerType::class, [
                'label' => $this->translator->trans('form.event_max_registrations'),
                'attr' => [
                    'class' => 'p-3 border border-gray-300 rounded-lg w-full',
                    'min' => 1,
                    'max' => 300,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('form.event_description'),
                'required' => false,
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('location', ChoiceType::class, [
                'label' => $this->translator->trans('form.event_location'),
                'choices' => $options['locations'],
                'choice_label' => fn(Location $location) => $location->getName(),
                'placeholder' => $this->translator->trans('form.select_existing_location'),
                'required' => false,
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('newLocation', LocationFormType::class, [
                'label' => $this->translator->trans('form.new_location'),
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('takePartIn', CheckboxType::class, [
                'label' => $this->translator->trans('form.take_part_in_event'),
                'attr' => ['class' => 'ml-3 p-3 border border-gray-300 rounded-lg'],
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'locations' => [],
        ]);
    }
}
