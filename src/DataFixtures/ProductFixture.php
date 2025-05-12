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
            $category = new Category();
            $category->setName($faker->word);
            $category->setSlug($faker->name);
            $category->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
            $manager->persist($category);
            $manager->flush();
        }
        for ($i = 0; $i < 30; $i++) {
            $product = new Product();
            $product->setName($faker->word);
            $product->setSlug($faker->name);
            $product->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
            $product->setPrice($faker->randomFloat(2, 1, 1000));
            $product->setDiscountPercent($faker->randomFloat(2, 0, 100));
            $product->setStockQuantity($faker->numberBetween(1, 100));
            
            $categoryRepository = $manager->getRepository(Category::class);
            $categories = $categoryRepository->findAll();
            $category = $categories[$faker->numberBetween(0, count($categories) - 1)];

            $product->setCategoryId($category);
            $manager->persist($product);
            
            $productImage = new ProductImage();
            $productImage->setProduct($product);
            $productImage->setImagePath("https://media.licdn.com/dms/image/v2/D4E03AQGkfY6jVdnadg/profile-displayphoto-shrink_200_200/profile-displayphoto-shrink_200_200/0/1723487780760?e=2147483647&v=beta&t=F0f-aiJGgxc2sL7BwXtI002cFXVZS-I09guBMAFLUZc");
            $productImage->setIsPrimary(true);
            $manager->persist($productImage);

            $product->addImage($productImage);
            for($j = 0; $j < 5; $j++) {
                $productImage = new ProductImage();
                $productImage->setProduct($product);
                $productImage->setImagePath("https://i1.sndcdn.com/artworks-x8zI2HVC2pnkK7F5-4xKLyA-t1080x1080.jpg");
                $productImage->setIsPrimary(false);
                $manager->persist($productImage);

                $product->addImage($productImage);
            }

            $manager->flush();
        }
    }
}
