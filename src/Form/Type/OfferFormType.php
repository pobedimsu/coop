<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Offer;
use App\Form\Tree\CategoryTreeType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OfferFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => true]])
            ->add('category', CategoryTreeType::class)
            ->add('city')
            ->add('image_id', ImageFormType::class, [
                'mapped' => true,
                'required' => false,
                'label' => 'Image',
                'constraints' => [
                    new File([
                        'maxSize' => '16000k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ]
            ])
            ->add('short_description', null, [
                'attr' => ['rows' => 2, 'placeholder' => 'будет отображаться в общем списке предложений. Максимаольная длина 160 символов.'],
            ])
            ->add('description', null, [
                'attr' => ['rows' => 10, 'placeholder' => 'будет отображаться при полном просмотре предложения'],
            ])
            ->add('price')
            ->add('measure', ChoiceType::class, [
                'choices' => Offer::getMeasureFormChoices(),
                'choice_translation_domain' => false,
            ])
            ->add('quantity')
            ->add('status', ChoiceType::class, [
                'choices' => Offer::getStatusFormChoices(),
                'choice_translation_domain' => false,
            ])
            ->add('is_enabled', null, [
                'label' => 'Обьявление выключено',
                'help'  => 'Если выключить, то пропадёт из списка объявлений и не будет учавствовать в формировании баланса',
            ])

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'offer';
    }
}
