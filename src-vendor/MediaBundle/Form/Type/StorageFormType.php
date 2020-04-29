<?php

namespace SmartCore\Bundle\MediaBundle\Form\Type;

use SmartCore\Bundle\MediaBundle\Entity\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StorageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('relative_path')
            ->add('provider')
        ;

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storage::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_media_storage';
    }
}
