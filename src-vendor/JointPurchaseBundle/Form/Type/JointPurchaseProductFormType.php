<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Form\Type;

//use App\Form\Type\ImageFormType; // @todo fix it!
use Coop\JointPurchaseBundle\Entity\JointPurchaseProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class JointPurchaseProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => true]])
            ->add('description', null, ['attr' => ['rows' => 10]])
            ->add('min_quantity', null, ['attr' => ['min' => 1]])
            ->add('price', null, ['attr' => ['min' => 1]])
            ->add('image_id', ImageFormType::class, [
                'mapped' => true,
                'required' => false,
                'label' => 'Image',
                'constraints' => [
                    new File([
                        'maxSize' => '8196k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ]
            ])

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success'], 'label' => 'Save'])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JointPurchaseProduct::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'joint_purchase_product';
    }
}
