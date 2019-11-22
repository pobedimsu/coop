<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\JointPurchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class JointPurchaseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => true]])
            ->add('description', null, ['attr' => ['rows' => 10]])
            ->add('final_date')
            /*
            ->add('shipping_type', ChoiceType::class, [
                'choices' => array_flip(JointPurchase::getShippingTypeValues()),
                'choice_translation_domain' => false,
            ])
            */
            ->add('transportation_cost_in_percent')
            ->add('telegram_chat_link')

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JointPurchase::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'joint_purchase';
    }
}
