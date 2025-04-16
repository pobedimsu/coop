<?php

namespace SmartCore\Bundle\TexterBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TexterCreateFormType extends TexterFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('create', SubmitType::class, ['attr' => [ 'class' => 'btn btn-primary' ]])
            ->add('cancel', SubmitType::class, ['attr' => ['formnovalidate' => 'formnovalidate']])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'smart_texter_create';
    }
}
