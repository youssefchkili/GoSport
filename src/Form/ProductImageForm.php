<?php

namespace App\Form;

use App\Entity\ProductImage;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductImageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Product Image',
                'mapped' => false, // Important: Not mapped directly to entity
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP)',
                    ])
                ],
            ])
            ->add('is_primary', CheckboxType::class, [
                'label' => 'Set as primary image?',
                'mapped' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImage::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'product_image_form',
        ]);
    }
}