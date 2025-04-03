<?php

namespace App\DataFixtures;

use App\Entity\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $genders = ['Homme', 'Femme', 'Autre / Ne ce prononce pas'];

        foreach ($genders as $gender) {
            $genre = new Gender();
            $genre->setName(ucfirst($gender));
            $manager->persist($genre);
        }

        $manager->flush();
    }
}
