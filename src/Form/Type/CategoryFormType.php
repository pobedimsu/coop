<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Form\Tree\CategoryTreeType;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => true]])
            ->add('name', null, ['attr' => ['placeholder' => 'Техническое имя на енг без пробелов и спецсимволов']])
            /*
            ->add('parent', null, [
                'label' => 'Parent category',
                'class'         => Category::class,
                'query_builder' => function (CategoryRepository $er) {
                    return $er->childrenHierarchy(null, false, [], false);
                },
                'choice_label' => function (Category $category) {
                    $prefix = '';
                    for ($i = 1; $i < $category->getLevel(); $i++) {
                        $prefix .= '⋅⋅ ';
                    }

                    return $prefix . (string) $category;
                }
            ])
            */
            ->add('parent', CategoryTreeType::class, ['label' => 'Parent category'])
            ->add('position')

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'category';
    }
}
