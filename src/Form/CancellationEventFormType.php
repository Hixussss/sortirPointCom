<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\State;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancellationEventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cancellationMotive', TextareaType::class, [
                'label' => 'Cancellation Motive',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Describe the cancellation motive...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'validation_groups' => ['cancellation'],
        ]);
    }
}
