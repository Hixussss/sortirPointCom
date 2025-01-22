<?php

namespace App\Form;

use App\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupFormType extends AbstractType
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
                'label' => $this->translator->trans('form.group_name'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
                ])
            ->add('description', TextType::class, [
                'label' => $this->translator->trans('form.group_description'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full']
                ])
            ->add('maxUsers', IntegerType::class, [
                'label' => $this->translator->trans('form.group_max_users'),
                'attr' => [
                    'class' => 'p-3 border border-gray-300 rounded-lg w-full',
                    'min' => 1,
                    'max' => 300,]
                ])
            ->add('isPrivate', CheckboxType::class, [
                'label' => $this->translator->trans('form.group_is_private'),
                'attr' => ['class' => 'p-3 border border-gray-300 rounded-lg w-full'],
                'mapped' => true,
                'required' => false,
                ])
           ->add('banner', FileType::class, [
               'label' => $this->translator->trans('form.group_picture'),
               'mapped' => false,
               'required' => false,
               'attr' => [
                   'class' => 'hidden',
                   'accept' => 'image/*']
               ]);
    }
}