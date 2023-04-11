<?php

namespace App\DataFixtures;

use App\Entity\Category;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Cinéma',
            'Sport',
            'People',
            'Musique',
            'Sciences',
            'Ecologie',
            'Mode',
            'Societé',
            'Hi-Tech',
            'Politique',
            'Jeux video'
        ];

        foreach ($categories as $name) {
            $category = new Category();

            $category->setName($name);
            $category->setAlias($this->slugger->slug($name));
            $category->setCreatedAt(new DateTime());
            $category->setUpdatedAt(new DateTime());

            $manager->persist($category);
        }

        $manager->flush();
    }
}
