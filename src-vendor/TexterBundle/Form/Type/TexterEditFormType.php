<?php

namespace SmartCore\Bundle\TexterBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TexterEditFormType extends TexterFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('save',   SubmitType::class, ['attr' => [ 'class' => 'btn btn-primary' ]])
            ->add('cancel', SubmitType::class, ['attr' => ['formnovalidate' => 'formnovalidate']])
        ;
    }

    public function getBlockPrefix()
    {
        return 'smart_texter_update';
    }
}
