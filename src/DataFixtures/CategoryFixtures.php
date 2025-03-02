<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Sciences',
            'Languages',
            'Arts',
            'Technology',
            'Business',
            'Health',
            'Personal Development',
            'Sales',  // Updated to English
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setTag($categoryName);
            $manager->persist($category);
        }

        $manager->flush();
    }
}