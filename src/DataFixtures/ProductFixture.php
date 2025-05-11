<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName($faker->word);
            $product->setSlug($faker->name);
            $product->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
            $product->setPrice(56.00);
            $product->setDiscountPercent(0.5);
            $product->setStockQuantity($faker->numberBetween(1, 100));

            $category = new Category();
            $category->setName($faker->word);
            $category->setSlug($faker->name);
            $category->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
            $manager->persist($category);

            $product->setCategoryId($category);
            $manager->persist($product);
/*
            $productImage = new ProductImage();
            $productImage->setProductId($product);
            $productImage->setImagePath("../../public/images/products/bestPlayerRightNow.jpg");
            $productImage->setIsPrimary(true);

            $manager->persist($productImage);
            */
            $manager->flush();
        }

    }
}
