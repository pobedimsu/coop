<?php
namespace SmartCore\Bundle\TexterBundle\Form\Type;

use SmartCore\Bundle\TexterBundle\Entity\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TexterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', null, ['label' => '123'])
            //->add('name')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Text::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'smart_texter';
    }
}
