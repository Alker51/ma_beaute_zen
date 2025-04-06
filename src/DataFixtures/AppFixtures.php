<?php

namespace App\DataFixtures;

use App\Entity\Gender;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $genders = ['Homme', 'Femme', 'Autre / Ne ce prononce pas'];
        $genderEntities = [];

        foreach ($genders as $gender) {
            $genre = new Gender();
            $genre->setName(ucfirst($gender));
            $manager->persist($genre);

            $genderEntities[$gender] = $genre;
        }

        $manager->flush();

        $admin = new User();
        $admin->setEmail('admin@example.com'); // Email de l'administrateur
        $admin->setFirstName('ADMIN');
        $admin->setLastName('ISTRATOR');
        $admin->setPhone('0123456789');
        $admin->setAdress('123 Rue des Admins');
        $admin->setCity('AdminCity');
        $admin->setZipcode('12345');
        $admin->setBirthDate(new \DateTime('1980-01-01')); // Date de naissance
        $admin->setGender($genderEntities['Homme']); // Genre associé

        // Définir le rôle d'admin
        $admin->setRoles(['ROLE_ADMIN']);

        // Hachage et définition du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $manager->flush();
    }
}
