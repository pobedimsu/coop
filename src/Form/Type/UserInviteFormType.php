<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserInviteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options'   => ['label' => 'Password'],
                'second_options'  => ['label' => 'Password confirmation'],
                'invalid_message' => 'Passwords mismatch',
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    '[выберите]' => null,
                    'Мужской' => User::SEX_MALE,
                    'Женский' => User::SEX_FEMALE,
                ],
                'data' => null,
                'choice_translation_domain' => false,
            ])
            ->add('is_smoking', ChoiceType::class, [
                'choices' => [
                    '[выберите]' => null,
                    'Да' => 1,
                    'Нет' => 0,
                ],
                'data' => null,
                'choice_translation_domain' => false,
            ])
            ->add('is_alcohol', ChoiceType::class, [
                'choices' => [
                    '[выберите]' => null,
                    'Да' => 1,
                    'Нет' => 0,
                ],
                'choice_translation_domain' => false,
            ])
            ->add('is_meat_consumption', ChoiceType::class, [
                'choices' => [
                    '[выберите]' => null,
                    'Да' => 1,
                    'Нет' => 0,
                ],
                'data' => null,
                'choice_translation_domain' => false,
            ])
            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            //->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_invite';
    }
}
