<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Username is required.'),
                    new Assert\Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Username must be at least {{ limit }} characters long.',
                        maxMessage: 'Username cannot be longer than {{ limit }} characters.'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Email is required.'),
                    new Assert\Email(message: 'Please enter a valid email address.'),
                ],
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'First name is required.'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Last name is required.'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Password is required.'),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Password must be at least {{ limit }} characters long.'
                    ),
                ],
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
                'label' => 'Select your site',
                'constraints' => [
                    new Assert\NotBlank(message: 'Please select a site.'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Register',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
