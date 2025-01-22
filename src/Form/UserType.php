<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Site;

class UserType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('form.first_name'),
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('form.last_name'),
                'required' => true,
            ])
            ->add('username', TextType::class, [
                'label' => $this->translator->trans('form.username'),
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('form.email'),
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => $this->translator->trans('form.phone'),
                'required' => false,
            ])
            ->add('site', ChoiceType::class, [
                'label' => $this->translator->trans('form.site'),
                'choices' => $options['sites'],
                'choice_label' => fn(Site $site) => $site->getName(),
                'placeholder' => $this->translator->trans('form.select_site'),
                'required' => false,
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
            ])
            ->add('profilePicture', FileType::class, [
                'label' => $this->translator->trans('form.profile_picture'),
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'hidden', // Champ caché pour être manipulé par JS
                    'accept' => 'image/*', // Restreindre aux images
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'sites' => [],
        ]);
    }
}
