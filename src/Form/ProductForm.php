<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\WishList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('price')
            ->add('discount_percent')
            ->add('stock_quantity')
            ->add('category_id', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category Name',
            ])
            ->add('createNewCategory', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Create new category',
            ])
            ->add('newCategory', CategoryForm::class, [
            'mapped' => false,
            'label' => false,
            'attr' => [
                'class' => 'new-category-fields',
                'style' => 'display: none;'
            ]
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
