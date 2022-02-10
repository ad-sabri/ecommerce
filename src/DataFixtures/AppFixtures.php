<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->slugger = $slugger;
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));

        $users = [];

        for ($u = 0; $u < 6; $u++) {
            $user = new User;

            $user->setEmail($faker->freeEmail)
                ->setPassword($this->passwordHasher->hashPassword($user, "password"))
                ->setRoles([]);

            $users[] = $user;

            $manager->persist($user);
        }

        $products = [];

        for ($c = 1; $c < 4; $c++) {
            $category = new Category;

            $category->setName("Category $c")
                ->setSlug(strtolower($this->slugger->slug($category->getName())));

            $manager->persist($category);

            for ($p = 0; $p < mt_rand(10, 40); $p++) {
                $product = new Product;
                $product->setName($faker->sentence())
                    ->setPrice(mt_rand(100, 200))
                    ->setSlug(strtolower($this->slugger->slug($product->getName())))
                    ->setCategory($category)
                    ->setShortDescription($faker->paragraph(mt_rand(1, 3)))
                    ->setMainPicture($faker->imageUrl(400, 400, true));

                $products[] = $product;

                $manager->persist($product);
            }
        }

        for ($p = 0; $p < mt_rand(10, 30); $p++) {
            $purchase = new Purchase;

            $purchase->setFullName($faker->name)
                ->setAddress($faker->streetAddress)
                ->setPostalCode($faker->postcode)
                ->setCity($faker->city)
                ->setUser($faker->randomElement($users))
                ->setTotal(mt_rand(2000, 30000))
                ->setPurchasedAt($faker->dateTimeBetween('-3 months'));

            $selectedProducts = $faker->randomElements($products, mt_rand(1, 3));

            foreach ($selectedProducts as $product) {
                $purchaseItem = new PurchaseItem;

                $purchaseItem->setProduct($product)
                    ->setQuantity(mt_rand(1, 3))
                    ->setProductName($product->getName())
                    ->setProductPrice($product->getPrice())
                    ->setTotal(
                        $purchaseItem->getProductPrice() * $purchaseItem->getQuantity()
                    )
                    ->setPurchase($purchase);

                $manager->persist($purchaseItem);
            }

            if ($faker->boolean(80)) {
                $purchase->setStatus(Purchase::STATUS_PAID);
            }

            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
