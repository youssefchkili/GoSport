<?php

namespace App\DataFixtures;

use App\Entity\Adress;
use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Ramsey\Uuid\Uuid;

class ProductFixture extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
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

        // Adresse
        $adress = new Adress();
        $adress->setStreet($faker->streetAddress());
        $adress->setCity($faker->city());
        $adress->setState($faker->state());
        $adress->setCountry($faker->country());
        $adress->setPostalCode((int) $faker->postcode());
        $adress->setIsDefault(true);
        $adress->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($adress);

        // Utilisateur
        $user = new User();
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setEmail($faker->unique()->safeEmail());
        $user->setFirstName($faker->firstName());
        $user->setLastName($faker->lastName());
        $user->setPhone($faker->phoneNumber());
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setAdress($adress);

        // Mot de passe haché
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);

        $manager->persist($user);


        $cart = new Cart();
        $cart->setUser($user); // Attention : méthode = setUserId() dans ta classe
        $manager->persist($cart);



        for ($i = 0; $i < 10; $i++) {
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
            echo "Product ID: " . $product->getId() . " - Name: " . $product->getName() . PHP_EOL;
        }
        echo "cart ID: " . $cart->getId() . PHP_EOL;

    }
}
